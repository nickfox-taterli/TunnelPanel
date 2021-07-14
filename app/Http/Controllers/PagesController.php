<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tunnel;

class PagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function root()
    {
        $user = Auth::user();

        if($user == null){
            return view('pages.root');
        }else{
            $tunnels = Tunnel::where('bind', '=', $user->id)->get();
            $tunnels = Tunnel::all();
            return view('users.show', compact('user','tunnels'));
        }

    }
}