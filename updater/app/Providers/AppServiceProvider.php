<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\User;
use App\Models\Social;
use App\Models\Language;
use App\Models\User\SEO;
use App\Models\User\BasicSetting;
use Illuminate\Support\Facades\DB;
use App\Models\User\UserPermission;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\User\Language as UserLanguage;
use App\Http\Helpers\UserPermissionHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function changePreferences($userId)
    {
        $currentPackage = UserPermissionHelper::currentPackagePermission($userId);

        $preference = UserPermission::where([
            ['user_id', $userId]
        ])->first();

        if (empty($preference)) {
            return;
        }

        // if current package does not match with 'package_id' of 'user_permissions' table, then change 'package_id' in 'user_permissions'
        if (!empty($currentPackage) && ($currentPackage->id != $preference->package_id)) {
            $preference->package_id = $currentPackage->id;

            $features = !empty($currentPackage->features) ? json_decode($currentPackage->features, true) : [];
            $features[] = "Contact";
            $features[] = "Footer Mail";
            $features[] = "Profile Listing";
            $preference->permissions = json_encode($features);
            $preference->package_id = $currentPackage->id;
            $preference->save();
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        // Paginator builds its `path`/`first_page_url` from Request::url(), which
        // reads the raw SCRIPT_NAME/REQUEST_URI and therefore loses the
        // sub-directory prefix. Resolve it from the URL generator instead so
        // paginated JSON (the /api/v1 endpoints) returns usable page links.
        Paginator::currentPathResolver(function () {
            return rtrim(url('/'), '/') . '/' . ltrim(request()->path(), '/');
        });

        // On a sub-directory install (http://localhost/proresume/) Apache serves
        // the app from inside the document root, so Request::root() resolves to
        // the host without the project folder and every asset()/route() URL
        // loses its prefix. Point the generator at APP_URL when - and only when -
        // the app really is mounted under that prefix, so root installs and
        // `artisan serve` keep deriving URLs from the incoming request.
        if (!$this->app->runningInConsole()) {
            $configuredPath = rtrim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');
            $scriptPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

            if ($configuredPath !== '' && $configuredPath === $scriptPath) {
                URL::forceRootUrl(config('app.url'));
            }
        }

        // baselrabia/myfatoorah-with-laravel registers three demo callbacks
        // (/payment/index, /payment/success_callback, /payment/fail_callback)
        // pointing at a controller it expects to be *published* into
        // app/Http/Controllers. This project ships its own implementation in
        // App\Http\Controllers\Payment\MyFatoorahController, so those routes
        // resolved to a class that does not exist: they 500'd on the front end
        // and crashed `artisan route:list` with a ReflectionException.
        //
        // The package registers its routes from its own service provider, which
        // boots after the app providers - and Laravel keys routes by
        // method+URI, so the later registration wins. Overriding them has to
        // happen from an `app->booted()` callback (fired once every provider has
        // booted) or the package would just overwrite us again. This one has to
        // run on the CLI too, hence it sits above the console guard below.
        $this->app->booted(function () {
            RouteFacade::get('/payment/index', function () {
                session()->flash('warning', __('Please start your payment from the checkout page.'));

                return redirect()->route('front.index');
            })->name('MyFatoorah.index');

            RouteFacade::get('/payment/success_callback', function () {
                return redirect()->route('front.index');
            })->name('MyFatoorah.success');

            RouteFacade::get('/payment/fail_callback', function () {
                session()->flash('warning', __('cancel_payment'));

                return redirect()->route('front.index');
            })->name('MyFatoorah.fail');
        });

        // The installer redirect is an HTTP concern only. On the CLI, `header()`
        // is a no-op and `exit` kills every artisan command with a status of 0 and
        // no output, so bail out before the guard can fire.
        if ($this->app->runningInConsole()) {
            return;
        }

        $socials = Social::orderBy('serial_number', 'ASC')->get();
        $langs = Language::all();

        View::composer('*', function ($view) {
            if (session()->has('lang')) {
                $currentLang = Language::where('code', session()->get('lang'))->first();
            } else {
                $currentLang = Language::where('is_default', 1)->first();
            }
            if ($currentLang == null) {
                $currentLang = Language::where('is_default', 1)->first();
            }

            if ($currentLang == null) {
                return;
            }

            $bs = $currentLang->basic_setting;
            $be = $currentLang->basic_extended;

            if (Menu::where('language_id', $currentLang->id)->count() > 0) {
                $menus = Menu::where('language_id', $currentLang->id)->first()->menus;
            } else {
                $menus = json_encode([]);
            }
            if ($currentLang->rtl == 1) {
                $rtl = 1;
            } else {
                $rtl = 0;
            }
            $view->with('bs', $bs);
            $view->with('be', $be);
            $view->with('currentLang', $currentLang);
            $view->with('menus', $menus);
            $view->with('rtl', $rtl);
        });

        View::composer('admin.*', function ($view) {
            if (session()->has('admin_lang')) {
                $langParts = explode('_', session()->get('admin_lang'));
                $currentLang = Language::where('code', $langParts[1] ?? null)->first();
            } else {
                $currentLang = Language::where('is_default', 1)->first();
            }
            if ($currentLang == null) {
                $currentLang = Language::where('is_default', 1)->first();
            }

            if ($currentLang == null) {
                return;
            }

            $bs = $currentLang->basic_setting;
            $be = $currentLang->basic_extended;

            if (Menu::where('language_id', $currentLang->id)->count() > 0) {
                $menus = Menu::where('language_id', $currentLang->id)->first()->menus;
            } else {
                $menus = json_encode([]);
            }
            if ($currentLang->rtl == 1) {
                $rtl = 1;
            } else {
                $rtl = 0;
            }
            $view->with('bs', $bs);
            $view->with('be', $be);
            $view->with('currentLang', $currentLang);
            $view->with('menus', $menus);
            $view->with('rtl', $rtl);
        });

        View::composer(['front.*', 'pdf.membership', 'pdf.user_appointment'], function ($view) {
            if (session()->has('lang')) {
                $currentLang = Language::where('code', session()->get('lang'))->first();
            } else {
                $currentLang = Language::where('is_default', 1)->first();
            }
            if ($currentLang == null) {
                $currentLang = Language::where('is_default', 1)->first();
            }

            if ($currentLang == null) {
                return;
            }

            $bs = $currentLang->basic_setting;
            $be = $currentLang->basic_extended;

            if (Menu::where('language_id', $currentLang->id)->count() > 0) {
                $menus = Menu::where('language_id', $currentLang->id)->first()->menus;
            } else {
                $menus = json_encode([]);
            }
            if ($currentLang->rtl == 1) {
                $rtl = 1;
            } else {
                $rtl = 0;
            }
            $view->with('bs', $bs);
            $view->with('be', $be);
            $view->with('currentLang', $currentLang);
            $view->with('menus', $menus);
            $view->with('rtl', $rtl);
        });

        View::composer(['user.*', 'pdf.membership'], function ($view) {
            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->user()->id;

                $userFeaturesCount = UserPermissionHelper::userFeaturesCount($userId);

                // change package_id in 'user_permissions'
                $this->changePreferences($userId);

                if (session()->has('userDashboardLang')) {
                    $lang = UserLanguage::where([
                        ['code', session()->get('userDashboardLang')],
                        ['user_id', $userId]
                    ])->first();
                    session()->put('currentLangCode', session()->get('userDashboardLang'));
                } else {
                    $lang = UserLanguage::where([
                        ['is_default', 1],
                        ['user_id', $userId]
                    ])->first();
                    session()->put('currentLangCode', $lang->code);
                }

                $adminLangs = Language::get();

                if (session()->has('userDashboardLang')) {
                    $userDashboardLang =  Language::where('code', session()->get('userDashboardLang'))->first();
                } else {
                    $userDashboardLang = Language::where('is_default', 1)->first();
                    session()->put('userDashboardLang', $userDashboardLang->code);
                }

                $bs = $userDashboardLang->basic_setting;
                $be = $userDashboardLang->basic_extended;

                $keywords = json_decode($userDashboardLang->user_keywords, true);

                $userBs = DB::table('user_basic_settings')->where('user_id', $userId)->first();
                $userCurrentPackage =  UserPermissionHelper::currentPackagePermission($userId);
                $view->with('userBs', $userBs);
                $view->with('keywords', $keywords);
                $view->with('userDashboardLang', $userDashboardLang);
                $view->with('adminLangs', $adminLangs);
                $view->with('userFeaturesCount', $userFeaturesCount);
                $view->with('userCurrentPackage', $userCurrentPackage);
                $view->with('userDefaultLang ', $lang);
                $view->with('bs', $bs);
                $view->with('be', $be);
            }
        });

        View::composer(['user.profile.*', 'user.profile1.*', 'user.profile-common.*', 'user-front.*', 'pdf.user_appointment'], function ($view) {
            $user = getUser();

            if (empty($user)) {
                return;
            }

            // change package_id in 'user_permissions'
            $this->changePreferences($user->id);

            if (session()->has('user_lang')) {
                $userCurrentLang = UserLanguage::where('code', session()->get('user_lang'))->where('user_id', $user->id)->first();
                if (empty($userCurrentLang)) {
                    $userCurrentLang = UserLanguage::where('is_default', 1)->where('user_id', $user->id)->first();
                    session()->put('user_lang', $userCurrentLang->code);
                }
            } else {
                $userCurrentLang = UserLanguage::where('is_default', 1)->where('user_id', $user->id)->first();
            }

            //fetch admin basic setting data according to admin default language
            $mainLanguage = Language::where('is_default', 1)->first();
            $bs = $mainLanguage->basic_setting;

            app()->setLocale($userCurrentLang->code);

            $keywords = json_decode($userCurrentLang->keywords, true);

            $userBs = BasicSetting::where('user_id', $user->id)->first();
            $social_medias = $user->social_media()->get() ?? collect([]);
            $userSeo = SEO::where('language_id', $userCurrentLang->id)->where('user_id', $user->id)->first();
            $userLangs = UserLanguage::where('user_id', $user->id)->get();

            $cuurentSub = UserPermissionHelper::userPackage($user->id);

            $preferences = UserPermission::where([
                ['user_id', $user->id],
                ['package_id', $cuurentSub->package_id]
            ])->first();
            $userPermissions = isset($preferences) ? json_decode($preferences->permissions, true) : [];

            $packagePermissions = UserPermissionHelper::packagePermission($user->id);
            $packagePermissions = json_decode($packagePermissions, true);

            $view->with('user', $user);
            $view->with('userSeo', $userSeo);
            $view->with('userBs', $userBs);
            $view->with('social_medias', $social_medias);
            $view->with('userCurrentLang', $userCurrentLang);
            $view->with('userLangs', $userLangs);
            $view->with('keywords', $keywords);
            $view->with('userPermissions', $userPermissions);
            $view->with('packagePermissions', $packagePermissions);
            $view->with('bs', $bs);
        });

        View::share('langs', $langs);
        View::share('socials', $socials);
    }
}