@extends('layouts.app')
@section('title','Chọn Facebook Page')
@section('content')
<section class="panel" style="max-width:700px">
    <h2>Chọn Page muốn kết nối</h2>
    <p class="muted">Facebook đã cấp quyền. Chỉ những Page bạn chọn dưới đây mới được lưu và dùng để đăng bài.</p>
    @if($errors->any())<div class="errors">{{$errors->first()}}</div>@endif
    <form method="post" action="{{route('connections.facebook.pages.save')}}">@csrf
        @foreach($pages as $page)
            <label class="connection" style="cursor:pointer;margin:0"><span><input type="checkbox" name="pages[]" value="{{$page['id']}}" style="width:auto;margin-right:10px"> <strong>{{$page['name']}}</strong><br><span class="muted small" style="margin-left:27px">Page ID: {{$page['id']}}</span></span></label>
        @endforeach
        <div class="actions"><button class="btn">Lưu các Page đã chọn</button><a class="btn light" href="{{route('connections.index')}}">Hủy</a></div>
    </form>
</section>
@endsection
