<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class HomeController extends Controller{
    public function index(Request $request)
    {
         if(!session()->has('utente')) {
             $ditta = DB::select('SELECT * FROM DITTA');
             $ditta = $ditta[0];
             return view('index', compact('ditta'));
         }else{
             $utente = session()->get('utente');
             $ditta = DB::select('SELECT * FROM DITTA');
             $ditta = $ditta[0];
             return view('index', compact('ditta','utente'));
         }
    }
}
