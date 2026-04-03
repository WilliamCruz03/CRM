<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
{
    $this->middleware(function ($request, $next) {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        return $next($request);
    });
}

}