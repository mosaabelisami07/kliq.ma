<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the locale from the URL prefix to the app and the URL generator.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale');

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        $request->session()->put('locale', $locale);
        $request->route()->forgetParameter('locale');

        return $next($request);
    }
}
