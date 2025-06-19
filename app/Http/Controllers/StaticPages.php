<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaticPages extends Controller
{
    public function welcome() {
        return view('static/welcome');
    }

    public function about() {
        return view('static/about');
    }
    public function privacy() {
        return view('static/privacy');
    }
    public function contact() {
        return view('static/contact-us');
    }
}
