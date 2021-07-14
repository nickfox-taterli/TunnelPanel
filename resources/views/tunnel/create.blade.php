@extends('layouts.app')
@section('title', '创建隧道')

@section('content')
<div class="offset-md-2 col-md-8">
  <div class="card ">
    <div class="card-header">
      <h5>创建隧道</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('tunnel.store') }}">
      @csrf
          <div class="form-group">
          <div class="col-md-12">
            <label for="client_ipv4">客户端公网IPv4：</label>
            
            <input id="client_ipv4" type="text" class="form-control @error('client_ipv4') is-invalid @enderror" name="client_ipv4" value="{{ old('client_ipv4') ?: $_SERVER['REMOTE_ADDR'] }}" required autocomplete="client_ipv4" autofocus>

            @error('client_ipv4')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
            </div>
          </div>

          <div class="form-group">
          <div class="col-md-12">
            <label for="remark">隧道名：</label>
            
            <input id="remark" type="text" class="form-control @error('remark') is-invalid @enderror" name="remark" value="{{ old('remark') }}" required autocomplete="remark">

@error('remark')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
@enderror
            </div>
          </div>
          <button type="submit" class="btn btn-primary">创建</button>
      </form>
    </div>
  </div>
</div>
@stop
