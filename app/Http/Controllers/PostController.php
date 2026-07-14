<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function dashboard(Request $request) { $posts = $request->user()->posts(); return view('dashboard', ['drafts' => (clone $posts)->where('status','draft')->count(), 'review' => (clone $posts)->where('status','review')->count(), 'scheduled' => (clone $posts)->where('status','scheduled')->count(), 'published' => (clone $posts)->where('status','published')->count(), 'upcoming' => (clone $posts)->where('status','scheduled')->orderBy('scheduled_at')->take(5)->get()]); }
    public function index(Request $request) { return view('posts.index', ['posts' => $request->user()->posts()->with('connection')->latest()->paginate(12)]); }
    public function create(Request $request) { return view('posts.form', ['post' => new Post, 'connections' => $request->user()->socialConnections()->where('provider','facebook')->where('is_active', true)->get()]); }
    public function store(Request $request) { $post = $request->user()->posts()->create($this->payload($request)); if ($request->input('action') === 'publish') return $this->publishAndRedirect($post); return redirect()->route('posts.edit',$post)->with('success','Bản nháp đã được tạo.'); }
    public function generateContent(Request $request)
    {
        $data = $request->validate(['title' => 'required|string|max:255', 'content_type' => 'required|in:facebook,website', 'seo_keywords' => 'nullable|string|max:500', 'cta' => 'nullable|string|max:255']);
        $key = $this->openAiKey($request);
        $response = Http::withToken($key)->timeout(90)->post('https://api.openai.com/v1/responses', [
            'model' => 'gpt-5.6-luna',
            'input' => $this->contentPrompt($request, $data),
        ])->throw()->json();
        $content = $response['output_text'] ?? collect($response['output'] ?? [])->flatMap(fn ($item) => $item['content'] ?? [])->firstWhere('type', 'output_text')['text'] ?? null;
        abort_if(blank($content), 502, 'OpenAI không trả về nội dung.');
        return response()->json(['content' => $content]);
    }
    public function generateGeminiContent(Request $request)
    {
        $data = $request->validate(['title' => 'required|string|max:255', 'content_type' => 'required|in:facebook,website', 'seo_keywords' => 'nullable|string|max:500', 'cta' => 'nullable|string|max:255']);
        $key = $this->providerKey($request, 'gemini', 'Hãy lưu Gemini API key trong mục Kết nối trước.');
        $prompt = $this->contentPrompt($request, $data);
        $response = Http::timeout(90)->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key='.urlencode($key), [
            'contents' => [['parts' => [['text' => $prompt]]]],
        ])->throw()->json();
        $content = data_get($response, 'candidates.0.content.parts.0.text');
        abort_if(blank($content), 502, 'Gemini không trả về nội dung.');
        return response()->json(['content' => $content]);
    }
    public function generateImage(Request $request)
    {
        $data = $request->validate(['title' => 'required|string|max:255', 'content' => 'nullable|string', 'image_prompt' => 'nullable|string']);
        $key = $this->openAiKey($request);
        $prompt = $data['image_prompt'] ?: "Create a premium social media image for this Vietnamese Facebook post. Topic: {$data['title']}. Context: ".Str::limit($data['content'] ?? '', 700).'. No text or watermark in the image.';
        $response = Http::withToken($key)->timeout(180)->post('https://api.openai.com/v1/images/generations', [
            'model' => 'gpt-image-2', 'prompt' => $prompt, 'size' => '1024x1024', 'quality' => 'medium', 'n' => 1,
        ])->throw()->json();
        $image = data_get($response, 'data.0.b64_json');
        abort_if(blank($image), 502, 'OpenAI không trả về hình ảnh.');
        $path = 'generated-images/'.Str::uuid().'.png';
        Storage::disk('public')->put($path, base64_decode($image));
        return response()->json(['image_url' => rtrim(config('app.url'), '/').Storage::url($path), 'prompt' => $prompt]);
    }
    public function edit(Request $request, Post $post) { $this->owns($request,$post); return view('posts.form', ['post' => $post, 'connections' => $request->user()->socialConnections()->where('provider','facebook')->where('is_active', true)->get()]); }
    public function update(Request $request, Post $post) { $this->owns($request,$post); $post->update($this->payload($request)); if ($request->input('action') === 'publish') return $this->publishAndRedirect($post); return back()->with('success','Đã lưu nội dung.'); }
    public function approve(Request $request, Post $post) { $this->owns($request,$post); $post->update(['status' => $post->scheduled_at ? 'scheduled' : 'approved']); return back()->with('success','Bài viết đã được duyệt.'); }
    public function publish(Request $request, Post $post) { $this->owns($request,$post); return $this->publishAndRedirect($post); }
    public function destroy(Request $request, Post $post) { $this->owns($request,$post); $post->delete(); return redirect()->route('posts.index')->with('success','Đã xoá bài viết.'); }
    private function payload(Request $request): array { $data = $request->validate(['title'=>'required|max:255','content'=>'required','content_type'=>'required|in:facebook,website','seo_keywords'=>'nullable|max:500','cta'=>'nullable|max:255','social_connection_id'=>'nullable|integer','image_prompt'=>'nullable','image_url'=>'nullable|string|max:2048','image_file'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:5120','publish_mode'=>'required|in:review,auto,schedule','scheduled_at'=>'nullable|date']); if ($request->hasFile('image_file')) { $path = $request->file('image_file')->store('post-images', 'public'); $data['image_url'] = rtrim(config('app.url'), '/').Storage::url($path); } unset($data['image_file']); $data['status'] = $data['publish_mode'] === 'schedule' && !empty($data['scheduled_at']) ? 'scheduled' : ($data['publish_mode'] === 'auto' ? 'approved' : 'draft'); return $data; }
    private function owns(Request $request, Post $post): void { abort_unless($post->user_id === $request->user()->id,403); }
    private function publishAndRedirect(Post $post)
    {
        $connection = $post->connection;
        if (!$connection || $connection->provider !== 'facebook') return back()->with('error', 'Hãy chọn Facebook Page trước khi gửi bài.');
        $credentials = $connection->credentials ?? []; $pageId = $credentials['page_id'] ?? null; $token = $credentials['page_access_token'] ?? null;
        if (!$pageId || !$token) return back()->with('error', 'Kết nối Facebook này thiếu Page access token. Hãy kết nối lại Page.');
        $message = trim(html_entity_decode(strip_tags(str_replace(['</p>', '</li>', '<br>'], ["\n", "\n", "\n"], $post->content))));
        try {
            $version = config('services.facebook.graph_version');
            $endpoint = "https://graph.facebook.com/{$version}/{$pageId}/".($post->image_url ? 'photos' : 'feed');
            if ($post->image_url && ($localPath = $this->localImagePath($post->image_url))) {
                $response = Http::attach('source', fopen($localPath, 'r'), basename($localPath))->timeout(90)->post($endpoint, ['caption' => $message, 'access_token' => $token])->throw()->json();
            } else {
                $data = $post->image_url ? ['url' => $post->image_url, 'caption' => $message, 'access_token' => $token] : ['message' => $message, 'access_token' => $token];
                $response = Http::asForm()->timeout(90)->post($endpoint, $data)->throw()->json();
            }
            $facebookId = $response['post_id'] ?? $response['id'] ?? null;
            $permalink = $facebookId && str_contains($facebookId, '_') ? 'https://www.facebook.com/'.str_replace('_', '/posts/', $facebookId) : null;
            $post->update(['status'=>'published','published_at'=>now(),'platform_post_id'=>$facebookId,'platform_post_url'=>$permalink,'publish_error'=>null]);
            return redirect()->route('posts.edit',$post)->with('success','Đã gửi bài lên Facebook thành công.');
        } catch (\Throwable $e) {
            report($e); $post->update(['publish_error' => Str::limit($e->getMessage(), 1000)]);
            return back()->with('error', 'Facebook chưa đăng được bài. '.$this->facebookErrorMessage($e->getMessage()));
        }
    }
    private function facebookErrorMessage(string $message): string { return str_contains($message, 'OAuthException') ? 'Access token hoặc quyền Page đã hết hạn. Hãy kết nối lại Facebook.' : 'Vui lòng thử lại hoặc kiểm tra quyền pages_manage_posts.'; }
    private function localImagePath(string $imageUrl): ?string
    {
        $prefix = rtrim(config('app.url'), '/').'/storage/';
        if (!str_starts_with($imageUrl, $prefix)) return null;
        $path = Storage::disk('public')->path(substr($imageUrl, strlen($prefix)));
        return is_file($path) ? $path : null;
    }
    private function openAiKey(Request $request): string
    {
        return $this->providerKey($request, 'openai', 'Hãy lưu OpenAI API key trong mục Kết nối trước.');
    }
    private function providerKey(Request $request, string $provider, string $message): string
    {
        $connection = $request->user()->socialConnections()->where('provider', $provider)->where('is_active', true)->latest()->first();
        abort_unless(filled(data_get($connection?->credentials, 'api_key')), 422, $message);
        return $connection->credentials['api_key'];
    }
    private function contentPrompt(Request $request, array $data): string
    {
        $user = $request->user(); $contact = array_filter(['Điện thoại: '.$user->business_phone, 'Email: '.$user->business_email, 'Địa chỉ: '.$user->business_address, 'Website: '.$user->business_website]);
        $format = $data['content_type'] === 'website' ? 'bài viết website chuẩn SEO, có H2/H3, đoạn mở đầu, meta description ngắn, từ khóa được dùng tự nhiên, CTA cuối bài. Trả về HTML sạch (p, h2, h3, ul, li, strong), không dùng markdown hay emoji.' : 'bài đăng Facebook gần gũi, có mở bài thu hút, xuống dòng dễ đọc, CTA tự nhiên và 3-5 hashtag. Dùng 5-8 emoji phù hợp, tự nhiên (ví dụ ✨, ✅, 📌, 📞, 📍), đặc biệt có emoji cho từng ý quan trọng và phần liên hệ. Trả về HTML sạch chỉ gồm p, strong, ul, li; không dùng markdown.';
        $cta = $data['cta'] ?: $user->default_cta;
        return "Viết {$format} bằng tiếng Việt. Chủ đề: {$data['title']}. Từ khóa SEO: ".($data['seo_keywords'] ?: 'tự đề xuất phù hợp').'. CTA cần dùng: '.($cta ?: 'mời khách liên hệ để tư vấn').'. Thông tin liên hệ để chèn ở cuối bài khi có dữ liệu: '.implode('; ', $contact).'. Không tạo dòng Thương hiệu trong khối liên hệ và không bịa thông tin ngoài dữ liệu đã cung cấp.';
    }
}
