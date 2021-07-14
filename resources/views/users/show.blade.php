@extends('layouts.app')

@section('title', $user->name . ' 的个人中心')

@section('content')

<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
          <h1 class="center" style="font-size:22px;">隧道列表</h1>
          <p></p>
    <div class="card ">
      <div class="card-body">
        @include('users.tunnel')
      </div>
    </div>

  </div>
</div>
@stop