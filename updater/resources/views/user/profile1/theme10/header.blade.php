<header class="header-area">
    <nav class="navbar navbar-expand-xl hover-menu">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="{{ route('front.user.detail.view', getParam()) }}" target="_self">
                <img data-src="{{ isset($userBs->logo)
                    ? asset('assets/front/img/user/' . $userBs->logo)
                    : asset('assets/front/img/profile/lgoo.png') }}"
                    class="lazy" alt="Brand Logo">
            </a>

            <!-- Mobile Menu Button -->
            <button class="menu-toggler d-block d-xl-none" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#mobilemenu-offcanvas" aria-controls="mobilemenu-offcanvas">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="collapse navbar-collapse" id="main_nav">
                <!-- Header menu -->
                <ul id="mainMenu" class="navbar-nav justify-content-center ms-auto">
                    <li class="nav-item @if (request()->routeIs('front.user.detail.view')) active @endif">
                        <a class="nav-link" href="{{ route('front.user.detail.view', getParam()) }}">
                            {{ $keywords['Home'] ?? 'Home' }}
                        </a>
                    </li>
                    @if (is_array($userPermissions) &&
                            is_array($packagePermissions) &&
                            in_array('Appointment', $userPermissions) &&
                            in_array('Appointment', $packagePermissions))
                        <li class="nav-item @if (request()->routeIs('front.user.appointment') ||
                                request()->routeIs('front.user.appointment.form') ||
                                request()->routeIs('front.user.appointment.booking')) active @endif">
                            <a class="nav-link" href="{{ route('front.user.appointment', getParam()) }}">
                                {{ $keywords['Appointment'] ?? 'Appointment' }}
                            </a>
                        </li>
                    @endif
                    @if (is_array($userPermissions) &&
                            is_array($packagePermissions) &&
                            in_array('Service', $userPermissions) &&
                            in_array('Service', $packagePermissions))
                        <li class="nav-item @if (request()->routeIs('front.user.services') || request()->routeIs('front.user.service.detail')) active @endif">
                            <a class="nav-link" href="{{ route('front.user.services', getParam()) }}">
                                {{ $keywords['Services'] ?? 'Services' }}
                            </a>
                        </li>
                    @endif

                    @if (is_array($userPermissions) &&
                            is_array($packagePermissions) &&
                            in_array('Portfolio', $userPermissions) &&
                            in_array('Portfolio', $packagePermissions))
                        <li class="nav-item @if (request()->routeIs('front.user.portfolios') || request()->routeIs('front.user.portfolio.detail')) active @endif">
                            <a class="nav-link" href="{{ route('front.user.portfolios', getParam()) }}">
                                {{ $keywords['Portfolios'] ?? 'Portfolios' }}
                            </a>
                        </li>
                    @endif

                    @if (is_array($userPermissions) &&
                            is_array($packagePermissions) &&
                            in_array('Blog', $userPermissions) &&
                            in_array('Blog', $packagePermissions))
                        <li class="nav-item @if (request()->routeIs('front.user.blogs') || request()->routeIs('front.user.blog.detail')) active @endif">
                            <a class="nav-link" href="{{ route('front.user.blogs', getParam()) }}">
                                {{ $keywords['Blog'] ?? 'Blog' }}
                            </a>
                        </li>
                    @endif
                    @if (is_array($userPermissions) && in_array('Gallery', $userPermissions))
                        <li class="nav-item @if (request()->routeIs('front.user.gallery')) active @endif">
                            <a class="nav-link" href="{{ route('front.user.gallery', getParam()) }}">
                                {{ $keywords['Gallery'] ?? 'Gallery' }}
                            </a>
                        </li>
                    @endif

                    @if (is_array($userPermissions) && in_array('Contact', $userPermissions))
                        <li class="nav-item @if (request()->routeIs('front.user.contact')) active @endif">
                            <a class="nav-link" href="{{ route('front.user.contact', getParam()) }}">
                                {{ $keywords['Contact'] ?? 'Contact' }}
                            </a>
                        </li>
                    @endif
                </ul>

                <!-- header-right -->
                <div class="header-right ms-auto">
                    @if (!empty($userLangs))
                        <div class="language">
                            <form action="{{ route('changeUserLanguage', getParam()) }}" id="userLangForm">
                                <input type="hidden" name="username" value="{{ $user->username }}">
                                <i class="fa-solid fa-globe"></i>
                                <select class="nice-select" name="code"
                                    onchange="document.getElementById('userLangForm').submit();">
                                    @foreach ($userLangs as $userLang)
                                        <option value="{{ $userLang->code }}" @selected($userLang->id == $userCurrentLang->id)>
                                            {{ $userLang->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    @endif

                    @if (is_array($userPermissions) &&
                            is_array($packagePermissions) &&
                            in_array('Appointment', $userPermissions) &&
                            in_array('Appointment', $packagePermissions))
                        <div class="dropdown user-btn">
                            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-light fa-user-circle"></i>
                                @if (!Auth::guard('customer')->check())
                                    {{ $keywords['Login'] ?? 'Login' }}
                                @else
                                    {{ $keywords['Account'] ?? 'Account' }}
                                @endif
                            </button>
                            <ul class="dropdown-menu">
                                @if (!Auth::guard('customer')->check())
                                    <li><a class="dropdown-item"
                                            href="{{ route('customer.login', getParam()) }}">{{ $keywords['Login'] ?? 'Login' }}</a>
                                    </li>
                                    <li><a class="dropdown-item"
                                            href="{{ route('customer.signup', getParam()) }}">{{ $keywords['Signup'] ?? 'Signup' }}</a>
                                    </li>
                                @else
                                    <li><a class="dropdown-item"
                                            href="{{ route('customer.dashboard', getParam()) }}">{{ $keywords['Dashboard'] ?? 'Dashboard' }}</a>
                                    </li>
                                    <li><a class="dropdown-item"
                                            href="{{ route('customer.logout', getParam()) }}">{{ $keywords['Signout'] ?? 'Sign out' }}</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </div>
            </div> <!-- navbar-collapse -->
        </div> <!-- container -->
    </nav>
</header>
