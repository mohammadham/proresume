<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;

class UserWebsiteLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // NOTE: a tenant website (/username/...) resolves its language from the
        // tenant's own `user_lang` session key and falls back to the *tenant's*
        // default language, which userDetailView() also uses to pick content and
        // the rtl flag. Do not fall back to the visitor's main-site language
        // here: that would render Persian chrome around English tenant content.
        if (session()->has('user_lang')) {

            app()->setLocale(session()->get('user_lang'));
        } else {
            $defaultLang = Language::where('is_default', 1)->first();
            if (!empty($defaultLang)) {
                app()->setLocale($defaultLang->code);
            }
        }
 
        return $next($request);
    }
}
