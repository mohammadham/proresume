@extends('user.layout')
@php
    use App\Http\Helpers\UserPermissionHelper;
    use App\Models\Language;
    use Illuminate\Support\Carbon;
    $default = Language::where('is_default', 1)->first();
    $user = Auth::guard('web')->user();
    if (!empty($user)) {
        $permissions = UserPermissionHelper::packagePermission($user->id);
        $permissions = json_decode($permissions, true);
        $currentPackage = UserPermissionHelper::userPackage($user->id);
        $preferences = \App\Models\User\UserPermission::where([
            ['user_id', auth()->id()],
            ['package_id', $currentPackage->package_id],
        ])
            ->whereBetween('updated_at', [$currentPackage->start_date, $currentPackage->expire_date])
            ->first();
        $preferences = isset($preferences) ? json_decode($preferences->permissions, true) : [];
    }
@endphp
@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Preference') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('user-dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('User') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Preference') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Preference') }}</div>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-lg-6 text-center">
                            <form id="permissionsForm" class="" action="{{ route('user.preference.update') }}"
                                method="post">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <div class="selectgroup selectgroup-pills mt-2">
                                        @if (is_array($permissions))
                                            @if (in_array('Follow/Unfollow', $permissions))
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="permissions[]" value="Follow/Unfollow"
                                                        class="selectgroup-input"
                                                        @if (is_array($preferences) && in_array('Follow/Unfollow', $preferences)) checked @endif>
                                                    <span class="selectgroup-button">{{ __('Follow/Unfollow') }}</span>
                                                </label>
                                            @endif
                                             @if ($userBs->theme == 9)
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="permissions[]" value="Work Process"
                                                        class="selectgroup-input"
                                                        @if (is_array($preferences) && in_array('Work Process', $preferences)) checked @endif>
                                                    <span class="selectgroup-button">{{ __('Work Process') }}</span>
                                                </label>
                                            @endif
                                            @if (in_array('Appointment', $permissions))
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="permissions[]" value="Appointment"
                                                        class="selectgroup-input"
                                                        @if (is_array($preferences) && in_array('Appointment', $preferences)) checked @endif>
                                                    <span class="selectgroup-button">{{ __('Appointments') }}</span>
                                                </label>
                                            @endif
                                            @if ($userBs->theme == 10)
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="permissions[]" value="Features"
                                                        class="selectgroup-input"
                                                        @if (is_array($preferences) && in_array('Features', $preferences)) checked @endif>
                                                    <span class="selectgroup-button">{{ __('Features') }}</span>
                                                </label>
                                            @endif
                                            @if (in_array('Gallery', $permissions))
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="permissions[]" value="Gallery"
                                                        class="selectgroup-input"
                                                        @if (is_array($preferences) && in_array('Gallery', $preferences)) checked @endif>
                                                    <span class="selectgroup-button">{{ __('Gallery') }}</span>
                                                </label>
                                            @endif
                                            @if (in_array('Skill', $permissions))
                                                @if ($userBs->theme != 9 && $userBs->theme != 10 && $userBs->theme != 11 && $userBs->theme != 12)
                                                    <label class="selectgroup-item">
                                                        <input type="checkbox" name="permissions[]" value="Skill"
                                                            class="selectgroup-input"
                                                            @if (is_array($preferences) && in_array('Skill', $preferences)) checked @endif>
                                                        <span class="selectgroup-button">{{ __('Skills') }}</span>
                                                    </label>
                                                @endif
                                            @endif

                                            @if (in_array('Service', $permissions))
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="permissions[]" value="Service"
                                                        class="selectgroup-input"
                                                        @if (is_array($preferences) && in_array('Service', $preferences)) checked @endif>
                                                    <span class="selectgroup-button">{{ __('Services') }}</span>
                                                </label>
                                            @endif
                                            @if (in_array('Experience', $permissions))
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="permissions[]" value="Experience"
                                                        class="selectgroup-input"
                                                        @if (is_array($preferences) && in_array('Experience', $preferences)) checked @endif>
                                                    <span class="selectgroup-button">{{ __('Experience') }}</span>
                                                </label>
                                            @endif
                                            @if (in_array('Achievements', $permissions))
                                                @if ($userBs->theme != 10)
                                                    <label class="selectgroup-item">
                                                        <input type="checkbox" name="permissions[]" value="Achievements"
                                                            class="selectgroup-input"
                                                            @if (is_array($preferences) && in_array('Achievements', $preferences)) checked @endif>
                                                        <span class="selectgroup-button">{{ __('Achievements') }}</span>
                                                    </label>
                                                @endif
                                            @endif
                                            @if (in_array('Portfolio', $permissions))
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="permissions[]" value="Portfolio"
                                                        class="selectgroup-input"
                                                        @if (is_array($preferences) && in_array('Portfolio', $preferences)) checked @endif>
                                                    <span class="selectgroup-button">{{ __('Portfolios') }}</span>
                                                </label>
                                            @endif
                                            @if (in_array('Blog', $permissions))
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="permissions[]" value="Blog"
                                                        class="selectgroup-input"
                                                        @if (is_array($preferences) && in_array('Blog', $preferences)) checked @endif>
                                                    <span class="selectgroup-button">{{ __('Blog') }}</span>
                                                </label>
                                            @endif
                                            @if (in_array('Testimonial', $permissions))
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="permissions[]" value="Testimonial"
                                                        class="selectgroup-input"
                                                        @if (is_array($preferences) && in_array('Testimonial', $preferences)) checked @endif>
                                                    <span class="selectgroup-button">{{ __('Testimonial') }}</span>
                                                </label>
                                            @endif
                                        @endif
                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Contact"
                                                class="selectgroup-input"
                                                @if (is_array($preferences) && in_array('Contact', $preferences)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Contact') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Footer Mail"
                                                class="selectgroup-input"
                                                @if (is_array($preferences) && in_array('Footer Mail', $preferences)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Footer Mail') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Profile Listing"
                                                class="selectgroup-input"
                                                @if (is_array($preferences) && in_array('Profile Listing', $preferences)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Profile Listing') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" id="permissionBtn"
                                    class="btn btn-success">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
