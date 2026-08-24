        <!-- Start Mobile-menu -->
        <div class="offcanvas mobilemenuoffcanvas offcanvas-start" data-bs-scroll="true" data-bs-backdrop="true"
            tabindex="-1" id="mobilemenu-offcanvas">
            <div class="offcanvas-header align-items-center justify-content-between px-20 pt-20">
                <a class="navbar-brand" href="{{ route('front.user.detail.view', getParam()) }}">
                    <img width="150"
                        data-src="{{ isset($userBs->logo)
                            ? asset('assets/front/img/user/' . $userBs->logo)
                            : asset('assets/front/img/profile/lgoo.png') }}"
                        class="lazyload blur-up" alt="Logo">
                </a>
                <a href="#" class="menu-close" data-bs-dismiss="offcanvas" aria-label="Close">
                    <i class="fa-light fa-xmark"></i>
                </a>
            </div>
            <div class="offcanvas-body">
                <!-- mobile-menu clone -->
                <nav id="mobileMenu" class="mobile-menu mb-40">

                </nav>
                <!-- menu-action-item-wrapper -->
                <div class="menu-action-item-wrapper">
                    @if (!empty($userLangs))
                        <div class="menu-action-item">
                            <a href="javascript:void(0)">
                                <span class="icon">
                                    <i class="fal fa-globe"></i>
                                </span>
                                @foreach ($userLangs as $userLang)
                                    @if ($userLang->id == $userCurrentLang->id)
                                        {{ $userLang->name }}
                                    @endif
                                @endforeach

                                <span class="plus-icon"><i class="fal fa-plus"></i></span>
                            </a>
                            <ul class="setting-dropdown">
                                @foreach ($userLangs as $userLang)
                                    @if ($userLang->id != $userCurrentLang->id)
                                        <li>
                                            <a class="menu-link" href="#">{{ $userLang->name }}</a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (is_array($userPermissions) &&
                            is_array($packagePermissions) &&
                            in_array('Appointment', $userPermissions) &&
                            in_array('Appointment', $packagePermissions))
                        <div class="menu-action-item">
                            <a href="javascript:void(0)">
                                <span class="icon">
                                    <i class="fa-light fa-user-circle"></i>
                                </span>
                                @if (!Auth::guard('customer')->check())
                                    {{ $keywords['Login'] ?? 'Login' }}
                                @else
                                    {{ $keywords['Account'] ?? 'Account' }}
                                @endif
                                <span class="plus-icon"><i class="fal fa-plus"></i></span>
                            </a>
                            <ul class="setting-dropdown">

                                @if (!Auth::guard('customer')->check())
                                    <li><a class="menu-link"
                                            href="{{ route('customer.login', getParam()) }}">{{ $keywords['Login'] ?? 'Login' }}</a>
                                    </li>
                                    <li><a class="menu-link"
                                            href="{{ route('customer.signup', getParam()) }}">{{ $keywords['Signup'] ?? __('Signup') }}</a>
                                    </li>
                                @else
                                    <li><a class="menu-link"
                                            href="{{ route('customer.dashboard', getParam()) }}">{{ $keywords['Dashboard'] ?? __('Dashboard') }}</a>
                                    </li>
                                    <li><a class="menu-link"
                                            href="{{ route('customer.logout', getParam()) }}">{{ $keywords['Signout'] ?? __('Sign out') }}</a>
                                    </li>
                                @endif

                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- End Mobile-menu -->
