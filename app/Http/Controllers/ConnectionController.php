<?php

namespace App\Http\Controllers;

use App\Models\SocialConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ConnectionController extends Controller
{
    public function index(Request $request) { return view('connections.index', ['connections' => $request->user()->socialConnections()->latest()->get()]); }
    public function facebookRedirect(Request $request)
    {
        if (!config('services.facebook.client_id') || !config('services.facebook.client_secret')) {
            return redirect()->route('connections.index')->with('error', 'Chưa cấu hình FACEBOOK_APP_ID và FACEBOOK_APP_SECRET trong tệp .env.');
        }
        $state = Str::random(40);
        $request->session()->put('facebook_oauth_state', $state);
        $query = http_build_query([
            'client_id' => config('services.facebook.client_id'),
            'redirect_uri' => $this->facebookCallbackUrl(),
            'state' => $state,
            'response_type' => 'code',
            'scope' => 'pages_show_list,pages_read_engagement,pages_manage_posts',
        ]);
        return redirect('https://www.facebook.com/'.config('services.facebook.graph_version').'/dialog/oauth?'.$query);
    }

    public function facebookCallback(Request $request)
    {
        if ($request->filled('error')) return redirect()->route('connections.index')->with('error', 'Facebook chưa cấp quyền: '.$request->string('error_message', 'Đã hủy thao tác.'));
        abort_unless(hash_equals((string) $request->session()->pull('facebook_oauth_state'), (string) $request->string('state')), 403, 'Phiên kết nối không hợp lệ. Hãy thử lại.');
        $version = config('services.facebook.graph_version');
        $token = Http::get("https://graph.facebook.com/{$version}/oauth/access_token", [
            'client_id' => config('services.facebook.client_id'), 'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri' => $this->facebookCallbackUrl(), 'code' => $request->string('code'),
        ])->throw()->json('access_token');
        $pages = Http::withToken($token)->get("https://graph.facebook.com/{$version}/me/accounts", ['fields' => 'id,name,access_token'])->throw()->json('data', []);
        if (empty($pages)) return redirect()->route('connections.index')->with('error', 'Không tìm thấy Facebook Page bạn có quyền quản trị.');
        $request->session()->put('facebook_pages_to_connect', $pages);
        return redirect()->route('connections.facebook.pages');
    }
    public function facebookPages(Request $request)
    {
        $pages = $request->session()->get('facebook_pages_to_connect', []);
        if (empty($pages)) return redirect()->route('connections.index')->with('error', 'Phiên chọn Page đã hết hạn. Hãy kết nối Facebook lại.');
        return view('connections.facebook-pages', compact('pages'));
    }
    public function saveFacebookPages(Request $request)
    {
        $pages = collect($request->session()->pull('facebook_pages_to_connect', []));
        $selected = $request->validate(['pages' => 'required|array|min:1', 'pages.*' => 'string']);
        $chosen = $pages->whereIn('id', $selected['pages']);
        foreach ($chosen as $page) {
            $request->user()->socialConnections()->updateOrCreate(['provider' => 'facebook', 'label' => $page['name']], ['credentials' => ['page_id' => $page['id'], 'page_access_token' => $page['access_token']], 'is_active' => true]);
        }
        return redirect()->route('connections.index')->with('success', 'Đã kết nối '.$chosen->count().' Facebook Page đã chọn.');
    }
    public function store(Request $request) { $data = $request->validate(['provider' => 'required|in:facebook,openai,gemini', 'label' => 'required|max:100', 'api_key' => 'nullable|string|max:500']); $request->user()->socialConnections()->create(['provider' => $data['provider'], 'label' => $data['label'], 'credentials' => ['api_key' => $data['api_key'] ?? null]]); return back()->with('success', 'Đã lưu kết nối. Khóa được mã hóa trong cơ sở dữ liệu.'); }
    public function updateBrandProfile(Request $request) { $data = $request->validate(['brand_name'=>'nullable|max:120','business_phone'=>'nullable|max:50','business_email'=>'nullable|email|max:255','business_address'=>'nullable|max:255','business_website'=>'nullable|url|max:255','default_cta'=>'nullable|max:255']); $request->user()->update($data); return back()->with('success','Đã lưu thông tin thương hiệu để AI dùng khi viết bài.'); }
    public function destroy(Request $request, SocialConnection $connection) { abort_unless($connection->user_id === $request->user()->id, 403); $connection->delete(); return back()->with('success', 'Đã gỡ kết nối.'); }

    private function facebookCallbackUrl(): string
    {
        return rtrim(config('app.url'), '/').'/connections/facebook/callback';
    }
}
