@extends('layouts.app')
@section('title','Kết nối dịch vụ')
@section('content')
<div class="grid">
    <section class="panel">
        <h2>Kết nối đang dùng</h2>
        @forelse($connections as $connection)
            <div class="connection"><div><div class="logo {{$connection->provider === 'facebook'?'fb':'ai'}}">{{strtoupper($connection->provider)}} · {{$connection->label}}</div><span class="muted small">Đang hoạt động · Khóa API được mã hóa</span></div><form method="post" action="{{route('connections.destroy',$connection)}}">@csrf @method('DELETE')<button class="btn red small">Gỡ</button></form></div>
        @empty <p class="muted">Bạn chưa kết nối dịch vụ nào.</p> @endforelse
        <hr style="border:0;border-top:1px solid #eaecf0;margin:22px 0">
        <h2>Thông tin thương hiệu cho AI</h2>
        <p class="muted small">AI sẽ dùng các thông tin này để tạo CTA, số điện thoại, email và địa chỉ phù hợp.</p>
        <form method="post" action="{{route('connections.brand-profile')}}">@csrf @method('PUT')
            <div class="row"><div><label>Tên thương hiệu</label><input name="brand_name" value="{{old('brand_name',auth()->user()->brand_name)}}" placeholder="VD: HT Agency"></div><div><label>Số điện thoại</label><input name="business_phone" value="{{old('business_phone',auth()->user()->business_phone)}}" placeholder="VD: 0901 234 567"></div></div>
            <div class="row"><div><label>Email liên hệ</label><input name="business_email" type="email" value="{{old('business_email',auth()->user()->business_email)}}" placeholder="hello@congty.vn"></div><div><label>Website</label><input name="business_website" type="url" value="{{old('business_website',auth()->user()->business_website)}}" placeholder="https://congty.vn"></div></div>
            <label>Địa chỉ</label><input name="business_address" value="{{old('business_address',auth()->user()->business_address)}}" placeholder="VD: Quận 1, TP.HCM">
            <label>CTA mặc định</label><input name="default_cta" value="{{old('default_cta',auth()->user()->default_cta)}}" placeholder="VD: Liên hệ ngay để được tư vấn miễn phí">
            <button class="btn">Lưu hồ sơ thương hiệu</button>
        </form>
    </section>
    <section class="panel">
        <h2>Thêm kết nối</h2>
        <p class="muted small">Facebook dùng để đăng bài; OpenAI/Gemini dùng để tạo nội dung và gợi ý hình ảnh.</p>
        <a class="btn" style="display:block;text-align:center;background:#1877f2" href="{{route('connections.facebook.redirect')}}">f&nbsp;&nbsp; Kết nối Facebook</a>
        <p class="muted small" style="text-align:center">Bạn sẽ được chuyển đến Facebook để đăng nhập và cấp quyền cho các Page.</p>
        <hr style="border:0;border-top:1px solid #eaecf0;margin:20px 0">
        <h2>Kết nối AI</h2>
        @if($errors->any())<div class="errors">{{$errors->first()}}</div>@endif
        <form method="post" action="{{route('connections.store')}}">@csrf
            <label>Loại dịch vụ</label><select name="provider"><option value="openai">OpenAI (ChatGPT)</option><option value="gemini">Google Gemini</option></select>
            <label>Tên hiển thị</label><input name="label" placeholder="VD: OpenAI công ty" required>
            <label>API key</label><input name="api_key" type="password" placeholder="Dán API key vào đây">
            <button class="btn">Lưu kết nối AI</button>
        </form>
    </section>
</div>
@endsection
