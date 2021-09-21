<?php

namespace App\Http\Controllers;

use App\Models\Tunnel;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TunnelController extends Controller
{
    private $api_url;
    private $prefix;

    public function __construct()
    {
        $this->api_url = env('ROUTE_API_URL');
        $this->prefix = env('IPV6_PREFIX');
        $this->server_ipv4 = env('SERVER_IPV4');
        $this->middleware('auth', ['except' => ['ddns_update']]);
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
            'remark' => ['required'],
        ]);

        $uuid = Str::uuid();

        $tunnel = Tunnel::create([
            'uuid' => $uuid,
            'remark' => $request->remark,
            'server_ipv4' => $this->server_ipv4,
            'client_ipv4' => $request->client_ipv4,
            'server_ipv6' => $uuid,
            'client_ipv6' => $uuid,
            'routed_ipv6' => $uuid, // 临时填充的
            'bind' => $user->id,
        ]);

        if($tunnel->id > 32767){
            die('IP exhaustion!');
        }

        $id = $tunnel->id;

        $route_prefix = str_replace('::/48',':'.dechex(($id - 1)*2 + 2).'::/64',$this->prefix);
        $client_ipv6 = str_replace('::/48',':'.dechex(($id - 1)*2 + 1).'::1',$this->prefix);
        $server_ipv6 = str_replace('::/48',':'.dechex(($id - 1)*2 + 1).'::2',$this->prefix);

        DB::table('tunnels')->where('id', $tunnel->id)->update(['server_ipv6' => $server_ipv6, 'client_ipv6' => $client_ipv6,'routed_ipv6' => $route_prefix]);

        file_get_contents($this->api_url.'/add/'.$tunnel->id.'/'.$request->client_ipv4);

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

    public function delete(Request $request)
    {
        $user = Auth::user();
        $this->authorize('update', $user);
        Tunnel::where('bind', '=', $user->id)->find($request->id)->delete();

        file_get_contents($this->api_url.'/del/'.Tunnel::where('bind', '=', $user->id)->find($request->id)->id);

        return redirect()->route('root');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $this->authorize('update', $user);

        $this->validate($request, [
            'client_ipv4' => ['required', 'unique:tunnels,client_ipv4', 'ipv4', new \App\Rules\ClearnetIP],
            'remark' => ['required'],
        ]);

        if (Tunnel::where('bind', '=', $user->id)->find($request->id) != null) {
            // 我实在是没办法了,我知道这样写不太好.
            DB::table('tunnels')->where('id', $request->id)->update(['client_ipv4' => $request->client_ipv4, 'remark' => $request->remark]);
            file_get_contents($this->api_url.'/add/'.Tunnel::where('bind', '=', $user->id)->find($request->id)->id.'/'.$request->client_ipv4);
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
                file_get_contents($this->api_url.'/add/'.$result->id.'/'.$request->ip());
            }
        }
        return $request->ip();
    }
}
