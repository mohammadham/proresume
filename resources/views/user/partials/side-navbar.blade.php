@php
    $user = Auth::guard('web')->user();

    $package = \App\Http\Helpers\UserPermissionHelper::currentPackagePermission($user->id);
    if (!empty($user)) {
        $permissions = \App\Http\Helpers\UserPermissionHelper::packagePermission($user->id);
        $permissions = json_decode($permissions, true);
    }
@endphp

@if (Session::has('userDashboardLang'))
    @php
        $default = \App\Models\User\Language::where('code', Session::get('userDashboardLang'))
            ->where('user_id', $user->id)
            ->first();
    @endphp
@else
    @php
        $default = \App\Models\User\Language::where('is_default', 1)->where('user_id', $user->id)->first();
    @endphp
@endif


<div class="sidebar sidebar-style-2" @if (request()->cookie('user-theme') == 'dark') data-background-color="dark2" @endif>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <div class="user">
                <div class="avatar-sm float-left mr-2">
                    @if (!empty(Auth::user()->photo))
                        <img src="{{ asset('assets/front/img/user/' . Auth::user()->photo) }}" alt="..."
                            class="avatar-img rounded">
                    @else
                        <img src="{{ asset('assets/admin/img/propics/blank_user.jpg') }}" alt="..."
                            class="avatar-img rounded">
                    @endif
                </div>
                <div class="info">
                    <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                        <span>
                            {{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}
                            <span class="user-level">{{ auth()->user()->username }}</span>
                            <span class="caret"></span>
                        </span>
                    </a>
                    <div class="clearfix"></div>
                    <div class="collapse in" id="collapseExample">
                        <ul class="nav">
                            @if (!is_null($package))
                                <li>
                                    <a href="{{ route('user-profile-update', ['language' => $default->code]) }}">
                                        <span class="link-collapse">{{ __('Edit Profile') }}</span>
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ route('user.changePass', ['language' => $default->code]) }}">
                                    <span class="link-collapse">{{ __('Change Password') }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('user-logout', ['language' => $default->code]) }}">
                                    <span class="link-collapse">{{ __('Logout') }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="nav nav-primary">
                <div class="row mb-2">
                    <div class="col-12">
                        <form action="">
                            <div class="form-group py-0">
                                <input name="term" type="text" class="form-control sidebar-search ltr"
                                    value="" placeholder="{{ __('Search Menu Here') }} ...">
                            </div>
                        </form>
                    </div>
                </div>
                <li class="nav-item
                @if (request()->path() == 'user/dashboard') active @endif">
                    <a href="{{ route('user-dashboard', ['language' => $default->code]) }}">
                        <i class="la flaticon-paint-palette"></i>
                        <p>{{ __('Dashboard') }}</p>
                    </a>
                </li>
                <li class="nav-item
                @if (request()->path() == 'user/profile') active @endif">
                    <a href="{{ route('user-profile', ['language' => $default->code]) }}">
                        <i class="far fa-user-circle"></i>
                        <p>{{ __('Edit Profile') }} </p>
                    </a>
                </li>
                @if (!is_null($package))
                    <li
                        class="nav-item
                    @if (request()->path() == 'user/domains') active
                    @elseif(request()->path() == 'user/subdomain') active @endif">
                        <a data-toggle="collapse" href="#domains">
                            <i class="fas fa-link"></i>
                            <p>{{ __('Domains & URLs') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->path() == 'user/domains') show
                        @elseif(request()->path() == 'user/subdomain') show @endif"
                            id="domains">
                            <ul class="nav nav-collapse">
                                @if (!empty($permissions) && in_array('Custom Domain', $permissions))
                                    <li
                                        class="
                                    @if (request()->path() == 'user/domains') active @endif">
                                        <a href="{{ route('user-domains', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('Custom Domain') }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if (!empty($permissions) && in_array('Subdomain', $permissions))
                                    <li
                                        class="
                                    @if (request()->path() == 'user/subdomain') active @endif">
                                        <a href="{{ route('user-subdomain', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('Subdomain & Path URL') }}</span>
                                        </a>
                                    </li>
                                @else
                                    <li
                                        class="
                                    @if (request()->path() == 'user/subdomain') active @endif">
                                        <a href="{{ route('user-subdomain', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('Path Based URL') }}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif
                {{-- start Register User --}}
                <li
                    class="nav-item  @if (request()->path() == 'user/register-user') active
                    @elseif (request()->routeIs('register.customer.view')) active
                    @elseif (request()->routeIs('register.customer.changePass')) active @endif">
                    <a href="{{ route('user.register-user', ['language' => $default->code]) }}">
                        <i class="fas fa-users"></i>
                        <p>{{ __('Registered User') }}</p>
                    </a>
                </li>
                {{-- End Register User --}}
                @if (!is_null($package))
                    <li
                        class="nav-item
                    @if (request()->path() == 'user/favicon') active
                    @elseif(request()->path() == 'user/theme/version') active
                    @elseif(request()->path() == 'user/logo') active
                    @elseif(request()->path() == 'user/preloader') active
                    @elseif(request()->path() == 'user/color') active
                    @elseif (request()->routeIs('user.basic_settings.general-settings')) active
                    @elseif(request()->path() == 'user/social') active
                    @elseif(request()->is('user/social/*')) active
                    @elseif(request()->routeIs('user.basic_settings.mail_templates')) active
                    @elseif(request()->routeIs('user.basic_settings.edit_mail_template')) active
                    @elseif(request()->routeIs('user.mail.information')) active
                    @elseif(request()->routeIs('user.footer_section.index')) active
                    @elseif(request()->routeIs('user.plugins')) active
                    @elseif(request()->path() == 'user/basic_settings/seo') active @endif">
                        <a data-toggle="collapse" href="#basic">
                            <i class="la flaticon-settings"></i>
                            <p>{{ __('Settings') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->path() == 'user/favicon') show
                        @elseif(request()->path() == 'user/theme/version') show
                        @elseif(request()->path() == 'user/logo') show
                        @elseif(request()->path() == 'user/preloader') show
                        @elseif (request()->routeIs('user.basic_settings.general-settings')) show
                        @elseif(request()->path() == 'user/color') show
                        @elseif(request()->path() == 'user/social') show
                        @elseif(request()->is('user/social/*')) show
                        @elseif(request()->routeIs('user.basic_settings.mail_templates')) show
                        @elseif(request()->routeIs('user.basic_settings.edit_mail_template')) show
                        @elseif(request()->routeIs('user.mail.information')) show
                        @elseif(request()->routeIs('user.plugins')) show
                        @elseif(request()->routeIs('user.footer_section.index')) show
                        @elseif(request()->path() == 'user/basic_settings/seo') show @endif"
                            id="basic">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->path() == 'user/theme/version') active @endif">
                                    <a href="{{ route('user.theme.version', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Themes') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'user/favicon') active @endif">
                                    <a href="{{ route('user.favicon', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Favicon') }}</span>
                                    </a>
                                </li>
                                @if ($userBs->theme != 5)
                                    <li class="@if (request()->path() == 'user/logo') active @endif">
                                        <a href="{{ route('user.logo', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('Logo') }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if ($userBs->theme != 8 && $userBs->theme != 7 && $userBs->theme != 6)
                                    <li class="@if (request()->path() == 'user/preloader') active @endif">
                                        <a href="{{ route('user.preloader', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('Preloader') }}</span>
                                        </a>
                                    </li>
                                @endif
                                <li class="@if (request()->path() == 'user/color') active @endif">
                                    <a href="{{ route('user.color.index', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Color Settings') }}</span>
                                    </a>
                                </li>
                                @if ($userBs->theme == 9 || $userBs->theme == 10 || $userBs->theme == 11 || $userBs->theme == 12)
                                    <li class="@if (request()->routeIs('user.footer_section.index')) active @endif">
                                        <a href="{{ route('user.footer_section.index') }}">
                                            <span class="sub-item">{{ __('Footer Section') }}</span>
                                        </a>
                                    </li>
                                @endif
                                <li
                                    class="{{ request()->routeIs('user.basic_settings.general-settings') ? 'active' : '' }}">
                                    <a
                                        href="{{ route('user.basic_settings.general-settings', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('General Settings') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="@if (request()->path() == 'user/social') active
                                @elseif(request()->is('user/social/*')) active @endif">
                                    <a href="{{ route('user.social.index', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Social Links') }}</span>
                                    </a>
                                </li>
                                @php
                                    $plugins = ['Google Analytics', 'Disqus', 'WhatsApp', 'Facebook Pixel', 'Tawk.to'];
                                @endphp

                                @if (!empty($permissions) && array_intersect($permissions, $plugins))
                                    <li class="{{ request()->routeIs('user.plugins') ? 'active' : '' }}">
                                        <a href="{{ route('user.plugins', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('Plugins') }}</span>
                                        </a>
                                    </li>
                                @endif
                                <li class="@if (request()->path() == 'user/basic_settings/seo') active @endif">
                                    <a href="{{ route('admin.basic_settings.seo', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('SEO Information') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="submenu
                                @if (request()->routeIs('user.basic_settings.mail_templates')) selected
                                @elseif (request()->routeIs('user.basic_settings.edit_mail_template')) selected
                                @elseif (request()->routeIs('user.basic_settings.edit_mail_template')) selected
                                @elseif (request()->path() == 'user/mail/information/subscriber') selected @endif">
                                    <a data-toggle="collapse" href="#emailset"
                                        aria-expanded="{{ request()->path() == 'user/mail/information/subscriber' || request()->routeIs('user.basic_settings.mail_templates') || request()->routeIs('user.basic_settings.edit_mail_template') ? 'true' : 'false' }}">
                                        <span class="sub-item">{{ __('Email Settings') }}</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse {{ request()->routeIs('user.basic_settings.mail_templates') || request()->routeIs('user.basic_settings.edit_mail_template') || request()->path() == 'user/mail/information/subscriber' ? 'show' : '' }}"
                                        id="emailset">
                                        <ul class="nav nav-collapse subnav">
                                            <li
                                                class="
                                            @if (request()->routeIs('user.basic_settings.mail_templates')) active
                                            @elseif (request()->routeIs('user.basic_settings.edit_mail_template')) active @endif">
                                                <a
                                                    href="{{ route('user.basic_settings.mail_templates', ['language' => $default->code]) }}">
                                                    <span class="sub-item">{{ __('Mail Templates') }}</span>
                                                </a>
                                            </li>
                                            <li
                                                class="
                                            @if (request()->path() == 'user/mail/information/subscriber') active @endif">
                                                <a
                                                    href="{{ route('user.mail.information', ['language' => $default->code]) }}">
                                                    <span class="sub-item">{{ __('Mail Information') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if (!is_null($package))
                    <li class="nav-item @if (request()->path() == 'user/home-page-text/edit') active @endif">
                        <a href="{{ route('user.home.page.text.edit', ['language' => $default->code]) }}">
                            <i class="fas fa-home"></i>
                            <p>{{ __('Home Sections') }}</p>
                        </a>
                    </li>
                    <li class="nav-item
                    @if (request()->path() == 'user/preference') active @endif">
                        <a href="{{ route('user.preference.index', ['language' => $default->code]) }}">
                            <i class="fas fa-toggle-on"></i>
                            <p>{{ __('Preference') }}</p>
                        </a>
                    </li>
                    @if ($userBs->theme == 9)
                        @if (!empty($permissions) && in_array('Work Process', $permissions))
                            <li class="nav-item @if (request()->routeIs('user.work.process.index')) active @endif">
                                <a href="{{ route('user.work.process.index', ['language' => $default->code]) }}">
                                    <i class="fas fa-tasks"></i>
                                    <p>{{ __('Work Process') }}</p>
                                </a>
                            </li>
                        @endif
                    @endif
                    @if ($userBs->theme == 10)
                        <li class="nav-item @if (request()->routeIs('user.features.index')) active @endif">
                            <a href="{{ route('user.features.index', ['language' => $default->code]) }}">
                                <i class="fas fa-star"></i>
                                <p>{{ __('Features') }}</p>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item @if (request()->routeIs('user.gallery.index')) active @endif">
                        <a href="{{ route('user.gallery.index', ['language' => $default->code]) }}">
                            <i class="fas fa-images"></i>
                            <p>{{ __('Gallery') }}</p>
                        </a>
                    </li>
                @endif
                @if ($userBs->theme != 3 && $userBs->theme != 9 && $userBs->theme != 10 && $userBs->theme != 11 && $userBs->theme != 12)
                    @if (!empty($permissions) && in_array('Skill', $permissions))
                        <li
                            class="nav-item
                        @if (request()->path() == 'user/skills') active
                        @elseif(request()->is('user/skill/*/edit')) active @endif">
                            <a href="{{ route('user.skill.index') . '?language=' . $default->code }}">
                                <i class="fas fa-pencil-ruler"></i>
                                <p>{{ __('Skills') }}</p>
                            </a>
                        </li>
                    @endif
                @endif
                @if (!empty($permissions) && in_array('Service', $permissions))
                    <li
                        class="nav-item  @if (request()->path() == 'user/services') active
                        @elseif (request()->is('user/service/*/edit')) active @endif">
                        <a href="{{ route('user.services.index') . '?language=' . $default->code }}">
                            <i class="fas fa-hands"></i>
                            <p>{{ __('Services') }}</p>
                        </a>
                    </li>
                @endif
                @if (!empty($permissions) && in_array('Experience', $permissions))
                    @if ($userBs->theme != 6 && $userBs->theme != 7)
                        <li
                            class="nav-item
                    @if (request()->path() == 'user/experiences') active
                    @elseif(request()->is('user/experience/*/edit')) active
                    @elseif(request()->path() == 'user/job-experiences') active
                    @elseif(request()->is('user/job-experience/*/edit')) active @endif">
                            <a data-toggle="collapse" href="#experience">
                                <i class="fas fa-user-cog"></i>
                                <p>{{ __('Experiences') }}</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse
                        @if (request()->path() == 'user/experiences') show
                        @elseif(request()->is('user/experience/*/edit')) show
                        @elseif(request()->path() == 'user/job-experiences') show
                        @elseif(request()->is('user/job-experience/*/edit')) show @endif"
                                id="experience">
                                <ul class="nav nav-collapse">
                                    <li
                                        class="
                                @if (request()->path() == 'user/job-experiences') active
                                @elseif(request()->is('user/job-experience/*/edit')) active @endif">
                                        <a
                                            href="{{ route('user.job.experiences.index') . '?language=' . $default->code }}">
                                            <span class="sub-item">{{ __('Job Experiences') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="@if (request()->path() == 'user/experiences') active
                                @elseif(request()->is('user/experience/*/edit')) active @endif">
                                        <a
                                            href="{{ route('user.experience.index') . '?language=' . $default->code }}">
                                            <span class="sub-item">{{ __('Educations') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif
                @if (!empty($permissions) && in_array('Achievements', $permissions))
                    @if ($userBs->theme != 10)
                        <li
                            class="nav-item  @if (request()->path() == 'user/achievements') active
                        @elseif(request()->is('user/achievement/*/edit')) active @endif">
                            <a href="{{ route('user.achievement.index', ['language' => $default->code]) }}">
                                <i class="fas fa-trophy"></i>
                                <p>{{ __('Achievements') }}</p>
                            </a>
                        </li>
                    @endif
                @endif
                @if (!empty($permissions) && in_array('Portfolio', $permissions))
                    <li
                        class="nav-item
                    @if (request()->path() == 'user/portfolio-categories') active
                    @elseif(request()->path() == 'user/portfolios') active
                    @elseif(request()->is('user/portfolio/*/edit')) active @endif">
                        <a data-toggle="collapse" href="#portfolio">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <p>{{ __('Portfolios') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->path() == 'user/portfolio-categories') show
                        @elseif(request()->path() == 'user/portfolios') show
                        @elseif(request()->is('user/portfolio/*/edit')) show @endif"
                            id="portfolio">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->path() == 'user/portfolio-categories') active @endif">
                                    <a
                                        href="{{ route('user.portfolio.category.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('Category') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="
                                @if (request()->path() == 'user/portfolios') active
                                @elseif(request()->is('user/portfolio/*/edit')) active @endif">
                                    <a href="{{ route('user.portfolio.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('Portfolios') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if (!empty($permissions) && in_array('Testimonial', $permissions))
                    <li
                        class="nav-item  @if (request()->path() == 'user/testimonials') active
                        @elseif(request()->is('user/testimonial/*/edit')) active @endif">
                        <a href="{{ route('user.testimonials.index') . '?language=' . $default->code }}">
                            <i class="far fa-comment"></i>
                            <p>{{ __('Testimonial') }}</p>
                        </a>
                    </li>
                @endif
                @if (!empty($permissions) && in_array('Blog', $permissions))
                    <li
                        class="nav-item
                    @if (request()->path() == 'user/blog-categories') active
                    @elseif(request()->routeIs('user.blog.index')) active
                    @elseif(request()->is('user/blog/*/edit')) active @endif">
                        <a data-toggle="collapse" href="#blog">
                            <i class="fas fa-blog"></i>
                            <p>{{ __('Blog') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->path() == 'user/blog-categories') show
                        @elseif(request()->routeIs('user.blog.index')) show
                        @elseif(request()->is('user/blog/*/edit')) show @endif"
                            id="blog">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->path() == 'user/blog-categories') active @endif">
                                    <a href="{{ route('user.blog.category.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('Category') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="
                                @if (request()->routeIs('user.blog.index')) active
                                @elseif(request()->is('user/blog/*/edit')) active @endif">
                                    <a href="{{ route('user.blog.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('Posts') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if (!empty($permissions) && in_array('Appointment', $permissions))
                    <li
                        class="nav-item
                    @if (request()->routeIs('user.appointment.setting')) active
                    @elseif(request()->routeIs('user.appointment.category')) active
                    @elseif(request()->routeIs('user.timeslot.management')) active
                    @elseif(request()->routeIs('user.appointment.timeslot')) active
                    @elseif(request()->routeIs('user.holidays')) active
                    @elseif(request()->routeIs('user.forminput')) active
                    @elseif (request()->routeIs('user.form.inputEdit')) active @endif">
                        <a data-toggle="collapse" href="#appointment">
                            <i class="fas fa-tools"></i>
                            <p>{{ __('Appointment Settings') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->routeIs('user.appointment.setting')) show
                        @elseif(request()->routeIs('user.appointment.category')) show
                        @elseif(request()->routeIs('user.timeslot.management')) show
                        @elseif(request()->routeIs('user.appointment.timeslot')) show
                        @elseif(request()->routeIs('user.holidays')) show
                        @elseif(request()->routeIs('user.forminput')) show
                        @elseif(request()->routeIs('user.form.inputEdit')) show @endif"
                            id="appointment">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->routeIs('user.appointment.setting')) active @endif">
                                    <a href="{{ route('user.appointment.setting', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Settings') }}</span>
                                    </a>
                                </li>
                                @if ($userBs->appointment_category == 1)
                                    <li
                                        class="@if (request()->routeIs('user.appointment.category')) active
                                @elseif (request()->routeIs('user.form.inputEdit')) active
                                @elseif (request()->routeIs('user.forminput')) active @endif">
                                        <a
                                            href="{{ route('user.appointment.category') . '?language=' . $default->code }}">
                                            <span class="sub-item">{{ __('Categories') }}</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="@if (request()->routeIs('user.forminput')) active @endif">
                                        <a href="{{ route('user.forminput') . '?language=' . $default->code }}">
                                            <span class="sub-item">{{ __('Form Builder') }}</span>
                                        </a>
                                    </li>
                                @endif
                                <li
                                    class="@if (request()->routeIs('user.appointment.timeslot')) active
                                 @elseif (request()->routeIs('user.timeslot.management')) active @endif">
                                    <a
                                        href="{{ route('user.appointment.timeslot', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Time Slots') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="@if (request()->routeIs('user.holidays')) active
                                 @elseif (request()->routeIs('user.holidays')) active @endif">
                                    <a href="{{ route('user.holidays', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Holidays') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li
                        class="nav-item
                    @if (request()->routeIs('user.bookedAppointment')) active
                    @elseif(request()->routeIs('user.pendingAppointment')) active
                    @elseif(request()->routeIs('user.approvedAppointment')) active
                    @elseif(request()->routeIs('user.completedAppointment')) active
                    @elseif(request()->routeIs('user.appointment.view')) active
                    @elseif(request()->routeIs('user.appointment.edit')) active
                    @elseif(request()->routeIs('user.rejectedAppointment')) active @endif">
                        <a data-toggle="collapse" href="#appointments">
                            <i class="fas fa-calendar"></i>
                            <p>{{ __('Appointments') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->routeIs('user.bookedAppointment')) show
                        @elseif(request()->routeIs('user.pendingAppointment')) show
                        @elseif(request()->routeIs('user.approvedAppointment')) show
                        @elseif(request()->routeIs('user.appointment.view')) show
                        @elseif(request()->routeIs('user.appointment.edit')) show
                        @elseif(request()->routeIs('user.completedAppointment')) show
                        @elseif(request()->routeIs('user.rejectedAppointment')) show @endif"
                            id="appointments">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->routeIs('user.bookedAppointment') ||
                                        request()->routeIs('user.appointment.view') ||
                                        request()->routeIs('user.appointment.edit')) active @endif">
                                    <a href="{{ route('user.bookedAppointment', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('ALL') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->routeIs('user.pendingAppointment')) active @endif">
                                    <a href="{{ route('user.pendingAppointment', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Pending') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->routeIs('user.approvedAppointment')) active @endif">
                                    <a
                                        href="{{ route('user.approvedAppointment', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Approved') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->routeIs('user.completedAppointment')) active @endif">
                                    <a
                                        href="{{ route('user.completedAppointment', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Completed') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->routeIs('user.rejectedAppointment')) active @endif">
                                    <a
                                        href="{{ route('user.rejectedAppointment', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Rejected') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif


                @if (!empty($permissions) && in_array('vCard', $permissions))
                    <li
                        class="nav-item
                    @if (request()->path() == 'user/vcard') active
                    @elseif(request()->path() == 'user/vcard/create') active
                    @elseif(request()->is('user/vcard/*/edit')) active
                    @elseif(request()->routeIs('user.vcard.services')) active
                    @elseif(request()->routeIs('user.vcard.projects')) active
                    @elseif(request()->routeIs('user.vcard.testimonials')) active
                    @elseif(request()->routeIs('user.vcard.about')) active
                    @elseif(request()->routeIs('user.vcard.preferences')) active
                    @elseif(request()->routeIs('user.vcard.color')) active
                    @elseif(request()->routeIs('user.vcard.keywords')) active @endif">
                        <a data-toggle="collapse" href="#vcard">
                            <i class="far fa-address-card"></i>
                            <p>{{ __('vCards Management') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->path() == 'user/vcard') show
                        @elseif(request()->path() == 'user/vcard/create') show
                        @elseif(request()->is('user/vcard/*/edit')) show
                        @elseif(request()->routeIs('user.vcard.services')) show
                        @elseif(request()->routeIs('user.vcard.projects')) show
                        @elseif(request()->routeIs('user.vcard.testimonials')) show
                        @elseif(request()->routeIs('user.vcard.about')) show
                        @elseif(request()->routeIs('user.vcard.preferences')) show
                        @elseif(request()->routeIs('user.vcard.color')) show
                        @elseif(request()->routeIs('user.vcard.keywords')) show @endif"
                            id="vcard">
                            <ul class="nav nav-collapse">
                                <li
                                    class="@if (request()->path() == 'user/vcard') active
                            @elseif(request()->is('user/vcard/*/edit')) active
                            @elseif(request()->routeIs('user.vcard.services')) active
                            @elseif(request()->routeIs('user.vcard.projects')) active
                            @elseif(request()->routeIs('user.vcard.testimonials')) active
                            @elseif(request()->routeIs('user.vcard.about')) active
                            @elseif(request()->routeIs('user.vcard.preferences')) active
                            @elseif(request()->routeIs('user.vcard.color')) active
                            @elseif(request()->routeIs('user.vcard.keywords')) active @endif">
                                    <a href="{{ route('user.vcard', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('vCards') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'user/vcard/create') active @endif">
                                    <a href="{{ route('user.vcard.create', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Add vCard') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if (!empty($permissions) && in_array('Online CV & Export', $permissions))
                    <li
                        class="nav-item
                    @if (request()->path() == 'user/cv') active
                    @elseif(request()->routeIs('user.cv.edit')) active
                    @elseif(request()->routeIs('user.cv.info')) active
                    @elseif(request()->routeIs('user.cv.section.index')) active
                    @elseif(request()->routeIs('user.cv.section.edit')) active
                    @elseif(request()->routeIs('user.cv.section.content')) active @endif">
                        <a href="{{ route('user.cv', ['language' => $default->code]) }}">
                            <i class="far fa-file"></i>
                            <p>{{ __('CV Management') }}</p>
                        </a>
                    </li>
                @endif
                @if (!empty($permissions) && in_array('QR Builder', $permissions))
                    <li
                        class="nav-item
                    @if (request()->routeIs('user.qrcode')) active
                    @elseif(request()->routeIs('user.qrcode.index')) active @endif">
                        <a data-toggle="collapse" href="#qrcode">
                            <i class="fas fa-qrcode"></i>
                            <p>{{ __('QR Codes') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->routeIs('user.qrcode')) show
                        @elseif(request()->routeIs('user.qrcode.index')) show @endif"
                            id="qrcode">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->routeIs('user.qrcode')) active @endif">
                                    <a href="{{ route('user.qrcode', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Generate QR Code') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->routeIs('user.qrcode.index')) active @endif">
                                    <a href="{{ route('user.qrcode.index', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Saved QR Codes') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if (!empty($permissions) && in_array('Follow/Unfollow', $permissions))
                    <li
                        class="nav-item
                    @if (request()->path() == 'user/follower-list') active
                    @elseif(request()->path() == 'user/following-list') active @endif">
                        <a data-toggle="collapse" href="#follow">
                            <i class="fas fa-user-friends"></i>
                            <p>{{ __('Follower/Following') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->path() == 'user/follower-list') show
                        @elseif(request()->path() == 'user/following-list') show @endif"
                            id="follow">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->path() == 'user/follower-list') active @endif">
                                    <a href="{{ route('user.follower.list', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Followers') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="
                                @if (request()->path() == 'user/following-list') active
                                @elseif(request()->is('user/following-list')) active @endif">
                                    <a href="{{ route('user.following.list', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('Following') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                <li class="nav-item
                @if (request()->path() == 'user/payment-log') active @endif">
                    <a href="{{ route('user.payment-log.index', ['language' => $default->code]) }}">
                        <i class="fas fa-list-ol"></i>
                        <p>{{ __('Payment Logs') }}</p>
                    </a>
                </li>

                @if (!is_null($package))
                    {{-- Language Management Page --}}
                    <li
                        class="nav-item
                    @if (request()->path() == 'user/languages') active
                    @elseif(request()->is('user/language/*/edit')) active
                    @elseif(request()->is('user/language/*/edit/keyword')) active @endif">
                        <a href="{{ route('user.language.index', ['language' => $default->code]) }}">
                            <i class="fas fa-language"></i>
                            <p>{{ __('Language Management') }}</p>
                        </a>
                    </li>
                @endif

                <li
                    class="nav-item
                    @if (request()->path() == 'user/package-list') active
                    @elseif(request()->is('user/package/checkout/*')) active @endif">
                    <a href="{{ route('user.plan.extend.index', ['language' => $default->code]) }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <p>{{ __('Buy Plan') }}</p>
                    </a>
                </li>
                {{-- Start Payment getway --}}
                <li
                    class="nav-item  @if (request()->path() == 'user/gateways') active   @elseif(request()->path() == 'user/offline/gateways') active @endif">
                    <a data-toggle="collapse" href="#gateways">
                        <i class="la flaticon-paypal"></i>
                        <p>{{ __('Payment Gateways') }}</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse  @if (request()->path() == 'user/gateways') show   @elseif(request()->path() == 'user/offline/gateways') show @endif"
                        id="gateways">
                        <ul class="nav nav-collapse">
                            <li class="@if (request()->path() == 'user/gateways') active @endif">
                                <a href="{{ route('user.gateway.index', ['language' => $default->code]) }}">
                                    <span class="sub-item">{{ __('Online Gateways') }}</span>
                                </a>
                            </li>
                            <li class="@if (request()->path() == 'user/offline/gateways') active @endif">
                                <a href="{{ route('user.gateway.offline') . '?language=' . $default->code }}">
                                    <span class="sub-item">{{ __('Offline Gateways') }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                {{-- End Payment getway --}}

                @if (!is_null($package))
                    @if ($userBs->theme != 10 && $userBs->theme != 11 && $userBs->theme != 12)
                        <li class="nav-item
                    @if (request()->path() == 'user/cv-upload') active @endif">
                            <a href="{{ route('user.cv.upload', ['language' => $default->code]) }}">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>{{ __('Upload CV') }}</p>
                            </a>
                        </li>
                    @endif
                @endif
                <li class="nav-item
                    @if (request()->path() == 'user/change-password') active @endif">
                    <a href="{{ route('user.changePass', ['language' => $default->code]) }}">
                        <i class="fas fa-key"></i>
                        <p>{{ __('Change Password') }}</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
