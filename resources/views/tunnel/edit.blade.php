@extends('layouts.app')
@section('title', '创建隧道')

@section('content')
<div class="offset-md-2 col-md-8">
    <div class="card ">
        <div class="card-header">
            <h5>更新隧道</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tunnel.update') }}">
                @csrf
                <div class="form-group row">
                    <div class="col-md-12">

                        <label for="client_ipv4">客户端DDNS链接：</label>
                        <input type="text" disabled class="form-control"
                            value="{{ route('tunnel.ddns',['id' => $tunnel->uuid]) }}">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">

                        <label for="client_ipv4">Debian/Ubuntu/CentOS配置参考：</label>
                        <textarea type="textarea" rows="9" cols="75" disabled class="form-control">auto taterli-ipv6
 iface taterli-ipv6 inet6 v4tunnel
  address {{ $tunnel->client_ipv6 }}
  netmask 112
  endpoint {{ $tunnel->server_ipv4 }}
  local {{ $tunnel->client_ipv4 }}
  ttl 255
  gateway {{ $tunnel->server_ipv6 }}
  mtu 1280</textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <input type="hidden" name="id" value="{{$tunnel->id}}">
                        <input type="hidden" name="uuid" value="{{$tunnel->uuid}}">
                        <label for="client_ipv4">客户端公网IPv4：</label>
                        <input id="client_ipv4" type="text"
                            class="form-control @error('client_ipv4') is-invalid @enderror" name="client_ipv4"
                            value="{{ $tunnel->client_ipv4 }}" required autocomplete="client_ipv4" autofocus>
                        @error('client_ipv4')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">更新隧道</button> <a href="#" type="submit"
                    class="btn btn-danger">隧道</a>
            </form>
        </div>
    </div>
</div>
@stop