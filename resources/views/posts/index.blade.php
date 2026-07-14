@extends('layouts.app')
@section('title','Bài viết')
@section('content')
<section class="panel"><table class="table"><thead><tr><th>Nội dung</th><th>Kênh đăng</th><th>Trạng thái</th><th>Thời gian</th><th></th></tr></thead><tbody>
@forelse($posts as $post)<tr><td><strong>{{$post->title}}</strong><br><span class="muted small">{{Str::limit(strip_tags($post->content),70)}}</span></td><td>{{$post->connection?->label ?? 'Chưa chọn'}}</td><td><span class="badge {{$post->status}}">{{['draft'=>'Nháp','review'=>'Chờ duyệt','approved'=>'Đã duyệt','scheduled'=>'Đã lên lịch','published'=>'Đã đăng'][$post->status] ?? $post->status}}</span></td><td class="small">{{$post->scheduled_at?->format('d/m H:i') ?? $post->created_at->format('d/m H:i')}}</td><td><a class="btn light small" href="{{route('posts.edit',$post)}}">Mở</a>@if($post->platform_post_url)<a class="btn light small" target="_blank" href="{{$post->platform_post_url}}">Facebook</a>@endif</td></tr>
@empty<tr><td colspan="5" class="muted">Chưa có bài viết. Hãy tạo bài đầu tiên.</td></tr>@endforelse
</tbody></table></section><div style="margin-top:16px">{{$posts->links()}}</div>
@endsection
