<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>PostFlow</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f6f7fb;color:#172033;font:15px/1.55 system-ui,sans-serif}.shell{display:flex;min-height:100vh}.side{width:242px;background:#101828;color:#cdd5df;padding:25px 16px;position:fixed;height:100vh}.brand{font-weight:800;font-size:22px;color:#fff;margin:0 12px 40px}.brand i{color:#7c6cff;font-style:normal}.nav a{display:block;color:#a9b5c6;text-decoration:none;padding:11px 12px;border-radius:9px;margin:3px 0}.nav a:hover,.nav a.active{background:#25304a;color:#fff}.main{margin-left:242px;width:calc(100% - 242px);padding:30px 44px}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:28px}.top h1{margin:0;font-size:25px}.muted{color:#697586}.btn{border:0;border-radius:8px;padding:10px 16px;font-weight:650;cursor:pointer;text-decoration:none;display:inline-block;background:#6558e8;color:#fff}.btn.light{background:#edf0f8;color:#364152}.btn.green{background:#0a9b70}.btn.red{background:#fee4e2;color:#b42318}.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}.card,.panel{background:#fff;border:1px solid #eaecf0;border-radius:12px;padding:19px}.num{font-weight:800;font-size:30px;margin:6px 0}.grid{display:grid;grid-template-columns:2fr 1fr;gap:20px}.panel h2{font-size:16px;margin:0 0 15px}.table{width:100%;border-collapse:collapse}.table td,.table th{padding:12px 7px;border-bottom:1px solid #eaecf0;text-align:left}.table th{font-size:12px;color:#697586}.badge{font-size:12px;padding:3px 9px;border-radius:20px;background:#eef2ff;color:#4f46c7}.badge.published{background:#dcfae6;color:#067647}.badge.draft{background:#f2f4f7;color:#475467}.badge.review{background:#fff7dc;color:#b54708}.badge.scheduled{background:#e8f1ff;color:#175cd3}label{font-size:13px;font-weight:700;display:block;margin:15px 0 6px}input,textarea,select{width:100%;border:1px solid #d0d5dd;border-radius:8px;padding:10px 11px;font:inherit;background:#fff}textarea{min-height:160px}.alert{margin-bottom:18px;padding:11px 14px;border-radius:8px;background:#ecfdf3;color:#067647}.errors{color:#b42318;margin:8px 0}.actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:20px}.row{display:grid;grid-template-columns:1fr 1fr;gap:16px}.login{max-width:420px;margin:10vh auto}.login .panel{padding:30px}.small{font-size:13px}.connection{display:flex;align-items:center;justify-content:space-between;padding:15px 0;border-bottom:1px solid #eaecf0}.logo{font-weight:800}.fb{color:#1877f2}.ai{color:#6558e8}
</style></head><body>
@if(auth()->check())
<div class="shell">
<aside class="side"><div class="brand">Post<i>Flow</i></div><nav class="nav"><a class="{{request()->routeIs('dashboard')?'active':''}}" href="{{route('dashboard')}}">Tổng quan</a><a class="{{request()->routeIs('posts.*')?'active':''}}" href="{{route('posts.index')}}">Bài viết</a><a class="{{request()->routeIs('connections.*')?'active':''}}" href="{{route('connections.index')}}">Kết nối</a></nav></aside>
<main class="main"><div class="top"><div><h1>@yield('title','Tổng quan')</h1><span class="muted">Xin chào, {{auth()->user()->name}}</span></div><a class="btn" href="{{route('posts.create')}}">Tạo bài viết</a></div>
@if(session('success'))
<div class="alert">{{session('success')}}</div>
@endif
@if(session('error'))
<div class="alert" style="background:#fff1f0;color:#b42318">{{session('error')}}</div>
@endif
@yield('content')
</main></div>
@else
@yield('content')
@endif
</body></html>
