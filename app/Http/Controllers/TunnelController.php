<?php

namespace App\Http\Controllers;

use App\Models\Tunnel;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        return view('tunnel.create', compact('user'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $this->authorize('update', $user);
        $this->validate($request, [
            'client_ipv4' => ['required', 'unique:tunnels,client_ipv4', 'ipv4', new \App\Rules\ClearnetIP],
        ]);

        $uuid = Str::uuid();

        $tunnel = Tunnel::create([
            'uuid' => $uuid,
            'server_ipv4' => $this->local_ip,
            'client_ipv4' => $request->client_ipv4,
            'server_ipv6' => $uuid,
            'client_ipv6' => $uuid,
            'bind' => $user->id,
        ]);

        DB::table('tunnels')->where('id', $tunnel->id)->update(['server_ipv6' => $this->link_prefix . $tunnel->id . ':2', 'client_ipv6' => $this->link_prefix . $tunnel->id . ':1']);

        $config = new \RouterOS\Config([
            'host' => $this->local_ip,
            'user' => 'admin',
            'pass' => $this->router_password,
            'port' => 8728,
        ]);
        $client = new \RouterOS\Client($config);

        // 添加隧道接口
        $query = new \RouterOS\Query('/interface/6to4/add');
        $query->equal('disabled', 'no');
        $query->equal('local-address', $this->local_ip);
        $query->equal('mtu', '1280');
        $query->equal('name', 'tunnel-' . $uuid);
        $query->equal('remote-address', $request->client_ipv4);
        $client->query($query)->read();

        // 添加网关IP
        $query = new \RouterOS\Query('/ipv6/address/add');
        $query->equal('address', $this->link_prefix . $tunnel->id . ':2');
        $query->equal('advertise', 'no');
        $query->equal('disabled', 'no');
        $query->equal('eui-64', 'no');
        $query->equal('interface', 'tunnel-' . $uuid);
        $client->query($query)->read();

        return redirect()->route('root');
    }

    public function edit(Request $request)
    {
        $user = Auth::user();
        $this->authorize('update', $user);
        $tunnel = Tunnel::where('bind', '=', $user->id)->find($request->id);
        if ($tunnel == null) {
            return redirect()->route('root');
        }
        return view('tunnel.edit', compact('user', 'tunnel'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $this->authorize('update', $user);
        $this->validate($request, [
            'client_ipv4' => ['required', 'unique:tunnels,client_ipv4', 'ipv4', new \App\Rules\ClearnetIP],
        ]);

        if (Tunnel::where('bind', '=', $user->id)->find($request->id) != null) {
            // 我实在是没办法了,我知道这样写不太好.
            DB::table('tunnels')->where('id', $request->id)->update(['client_ipv4' => $request->client_ipv4]);

            // 首先要登录路由器
            $config = new \RouterOS\Config([
                'host' => $this->local_ip,
                'user' => 'admin',
                'pass' => $this->router_password,
                'port' => 8728,
            ]);
            $client = new \RouterOS\Client($config);

            // 然后获取当前通道的ID
            $query = new \RouterOS\Query('/interface/6to4/print');
            $query->where('name', 'tunnel-' . $request->uuid);
            $route_id = $client->query($query)->read()[0]['.id'];

            // 然后修改它的远程地址
            $query = new \RouterOS\Query('/interface/6to4/set');
            $query->equal('.id', $route_id);
            $query->equal('remote-address', $request->client_ipv4);
            $client->query($query)->read();
        }
        return redirect()->route('root');
    }

    public function ddns_update(Request $request)
    {
        if (filter_var($request->ip(), \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            $result = DB::table('tunnels')->where('uuid', '=', $request->id)->orderBy('id', 'DESC')->first();
            if ($result == null) {
                return $request->ip();
            }
            if ($request->ip() != $result->client_ipv4) {
                DB::table('tunnels')->where('uuid', $request->id)->update(['client_ipv4' => $request->ip(), 'updated_at' => \Carbon\Carbon::now()]);

                // 首先要登录路由器
                $config = new \RouterOS\Config([
                    'host' => $this->local_ip,
                    'user' => 'admin',
                    'pass' => $this->router_password,
                    'port' => 8728,
                ]);
                $client = new \RouterOS\Client($config);

                // 然后获取当前通道的ID
                $query = new \RouterOS\Query('/interface/6to4/print');
                $query->where('name', 'tunnel-' . $request->id);
                $route_id = $client->query($query)->read()[0]['.id'];

                // 然后修改它的远程地址
                $query = new \RouterOS\Query('/interface/6to4/set');
                $query->equal('.id', $route_id);
                $query->equal('remote-address', $request->ip());
                $client->query($query)->read();
            }
        }
        return $request->ip();
    }
}
