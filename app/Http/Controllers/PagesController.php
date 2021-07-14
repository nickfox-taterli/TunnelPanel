<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            return view('users.show', compact('user'));
        }

    }
}