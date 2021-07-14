@extends('layouts.app')

@section('title', $user->name . ' 的个人中心')

@section('content')

<div class="row">

  <div class="col-lg-2 col-md-2 hidden-sm hidden-xs user-info">
    <div class="card ">
      <img src="{{ $user->gravatar('140') }}" alt="{{ $user->name }}" class="gravatar"/>
      <div class="card-body">
            <h5>普通用户</h5>     
      </div>
    </div>
  </div>
  <div class="col-lg-10 col-md-10 col-sm-12 col-xs-12">
    <div class="card ">
      <div class="card-body">
          <h1 class="mb-0" style="font-size:22px;">Hello {{ $user->name }},Welcome to TunnelBroker.</h1>
      </div>
    </div>
    <hr>
    <div class="card ">
      <div class="card-body">
        @include('users.tunnel')
      </div>
    </div>

  </div>
</div>
@stop