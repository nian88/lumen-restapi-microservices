<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
class BookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $payload = auth()->payload();
        return $payload['nada'];
        echo "asd";
    }

    //
}
