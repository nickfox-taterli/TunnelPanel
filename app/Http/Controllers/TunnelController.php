<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Tunnel;
use Auth;

class TunnelController extends Controller
{
    private $local_ip; // 修改为路由器IP
    private $link_prefix; // 修改为需要分配的前缀,目前只支持96.
    private $router_password; // 路由器密码
    //
    public function __construct()
    {
        $this->local_ip = env('ROUTER_IP');
        $this->link_prefix = env('ROUTER_PREFIX');
        $this->router_password = env('ROUTER_PASSWORD');
        $this->middleware('auth');
    }

    public function create()
    {
        $user = Auth::user();
        $this->authorize('update', $user);
        return view('tunnel.create',compact('user'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $this->authorize('update', $user);
        $this->validate($request, [
            'client_ipv4' => ['required', 'unique:tunnels,client_ipv4', 'ipv4',new \App\Rules\ClearnetIP],
        ]);

        $user = Tunnel::create([
            'uuid' => Str::uuid(),
            'server_ipv4' => $this->local_ip,
            'client_ipv4' => $request->client_ipv4,
            'server_ipv6' => $this->link_prefix . $user->id . ':1',
            'client_ipv6' => $this->link_prefix . $user->id . ':2',
            'bind' => $user->id,
        ]);

        return redirect()->route('root');
    }
}
