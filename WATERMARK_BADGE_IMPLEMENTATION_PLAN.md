# Watermark Badge Feature - Implementation Plan

## Overview
Add a configurable watermark/badge feature that appears in the website footer when enabled. The watermark will be displayed across all public pages but excluded from admin/user panels.

## Research Summary

### Existing Patterns in Codebase
The system uses feature-toggle patterns with status fields (0/1):
- `preloader_status` - Preloader enable/disable
- `cookie_alert_status` - Cookie banner enable/disable  
- `top_footer_section` - Footer widget area enable/disable
- `copyright_section` - Copyright area enable/disable
- `is_whatsapp` - WhatsApp button enable/disable
- `is_tawkto` - Tawk.to chat widget enable/disable

### Where Watermark Should Appear
- ✅ All public front pages (index, blog, templates, etc.)
- ❌ Admin panels (already don't include footer.blade.php)
- ❌ User panels (already don't include footer.blade.php)

### Where Configuration Should Live
- **Admin**: `admin.basic_settings` (global watermark for all users)
- **User**: `user.basic_settings` (per-user watermark)

## Implementation Plan

### Phase 1: Database Migrations
Create migrations to add watermark fields to both tables:

**File: `updater/database/migrations/2026_08_30_000003_add_watermark_to_basic_settings.php`**
```php
Schema::table('basic_settings', function (Blueprint $table) {
    $table->tinyInteger('watermark_status')->default(0)->after('copyright_section');
    $table->text('watermark_text')->nullable()->after('watermark_status');
    $table->string('watermark_url')->nullable()->after('watermark_text');
    $table->string('watermark_image')->nullable()->after('watermark_url');
});
```

**File: `updater/database/migrations/2026_08_30_000004_add_watermark_to_user_basic_settings.php`**
```php
Schema::table('user_basic_settings', function (Blueprint $table) {
    $table->tinyInteger('watermark_status')->default(0)->after('footer_section_image');
    $table->text('watermark_text')->nullable()->after('watermark_status');
    $table->string('watermark_url')->nullable()->after('watermark_text');
    $table->string('watermark_image')->nullable()->after('watermark_url');
});
```

### Phase 2: Update Models
**File: `app/Models/BasicSetting.php`**
Add to `$fillable`:
```php
'watermark_status',
'watermark_text',
'watermark_url',
'watermark_image',
```

**File: `app/Models/User/BasicSetting.php`**
Add to `$fillable`:
```php
'watermark_status',
'watermark_text',
'watermark_url',
'watermark_image',
```

### Phase 3: Create Watermark Partial View
**File: `resources/views/partials/watermark.blade.php`**
```blade
@if ($watermark_status == 1)
<div class="watermark-badge-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; opacity: 0.7; transition: opacity 0.3s;">
    @if ($watermark_image)
        <a href="{{ $watermark_url ?? '#' }}" target="_blank" rel="noopener noreferrer">
            <img src="{{ asset('assets/front/img/' . $watermark_image) }}" 
                 alt="{{ $watermark_text ?? 'Watermark' }}" 
                 style="max-width: 120px; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        </a>
    @elseif ($watermark_text)
        <a href="{{ $watermark_url ?? '#' }}" target="_blank" rel="noopener noreferrer"
           style="display: inline-block; padding: 8px 16px; background: var(--base-color, #007bff); color: white; 
                  border-radius: 50px; font-size: 13px; font-weight: 600; text-decoration: none;
                  box-shadow: 0 4px 12px rgba(0,0,0,0.15); white-space: nowrap;">
            {{ $watermark_text }}
        </a>
    @endif
</div>
<style>
.watermark-badge-container:hover {
    opacity: 1;
}
@media (max-width: 768px) {
    .watermark-badge-container {
        bottom: 80px; /* Above cookie banner if present */
        right: 15px;
    }
}
</style>
@endif
```

### Phase 4: Update Footer Partial
**File: `resources/views/front/partials/footer.blade.php`**
Add after the copyright section or as part of the footer:
```blade
@include('partials.watermark', [
    'watermark_status' => $bs->watermark_status ?? 0,
    'watermark_text' => $bs->watermark_text ?? '',
    'watermark_url' => $bs->watermark_url ?? '',
    'watermark_image' => $bs->watermark_image ?? '',
])
```

### Phase 5: Admin Controller & Views
**Controller: `app/Http/Controllers/Admin/BasicController.php`**
Add method:
```php
public function watermark()
{
    $data['abs'] = BasicSetting::firstOrFail();
    return view('admin.basic.watermark', $data);
}

public function updateWatermark(Request $request)
{
    $request->validate([
        'watermark_status' => 'required|integer',
        'watermark_text' => 'nullable|string|max:100',
        'watermark_url' => 'nullable|url|max:255',
        'watermark_image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
    ]);

    $bs = BasicSetting::first();
    
    // Handle image upload
    if ($request->hasFile('watermark_image')) {
        $img = $request->file('watermark_image');
        $filename = uniqid() . '.' . $img->getClientOriginalExtension();
        $img->move(public_path('assets/front/img/'), $filename);
        
        // Remove old image
        if ($bs->watermark_image) {
            @unlink(public_path('assets/front/img/' . $bs->watermark_image));
        }
        $bs->watermark_image = $filename;
    }
    
    $bs->watermark_status = $request->watermark_status;
    $bs->watermark_text = $request->watermark_text;
    $bs->watermark_url = $request->watermark_url;
    $bs->save();
    
    Session::flash('success', __('Updated successfully!'));
    return back();
}
```

**View: `resources/views/admin/basic/watermark.blade.php`**
```blade
@extends('admin.layout')

@section('content')
<div class="page-header">
    <h4 class="page-title">{{ __('Watermark Badge Settings') }}</h4>
    <ul class="breadcrumbs">
        <li class="nav-home"><a href="{{route('admin.dashboard')}}"><i class="flaticon-home"></i></a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">{{ __('Basic Settings') }}</a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">{{ __('Watermark Badge') }}</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <form action="{{route('admin.watermark.update')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10">
                            <div class="card-title">{{ __('Update Watermark Badge') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">
                            <div class="form-group">
                                <label>{{ __('Watermark Status') }}</label>
                                <div class="selectgroup w-100">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="watermark_status" value="1" class="selectgroup-input" {{ $abs->watermark_status == 1 ? 'checked' : '' }}>
                                        <span class="selectgroup-button">{{ __('Active') }}</span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="watermark_status" value="0" class="selectgroup-input" {{ $abs->watermark_status == 0 ? 'checked' : '' }}>
                                        <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                    </label>
                                </div>
                                @if ($errors->has('watermark_status'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_status')}}</p>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>{{ __('Watermark Text') }}</label>
                                <input type="text" class="form-control" name="watermark_text" value="{{ $abs->watermark_text }}" placeholder="{{ __('e.g. Secure Payment with Bank Mellat') }}">
                                @if ($errors->has('watermark_text'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_text')}}</p>
                                @endif
                                <small class="form-text text-muted">{{ __('Text to display when no image is provided') }}</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Watermark Link URL') }}</label>
                                <input type="url" class="form-control" name="watermark_url" value="{{ $abs->watermark_url }}" placeholder="https://example.com">
                                @if ($errors->has('watermark_url'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_url')}}</p>
                                @endif
                                <small class="form-text text-muted">{{ __('Optional link when watermark is clicked') }}</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Watermark Image') }}</label>
                                <input type="file" class="form-control" name="watermark_image" accept="image/*">
                                @if ($errors->has('watermark_image'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_image')}}</p>
                                @endif
                                @if ($abs->watermark_image)
                                    <div class="mt-2">
                                        <img src="{{ asset('assets/front/img/' . $abs->watermark_image) }}" alt="Current Watermark" style="max-width: 150px; height: auto; border-radius: 4px;">
                                        <p class="text-muted mt-1">{{ __('Current watermark image') }}</p>
                                    </div>
                                @endif
                                <small class="form-text text-muted">{{ __('Optional image (max 2MB). If provided, text will be ignored.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" id="displayNotif" class="btn btn-success">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

### Phase 6: User Controller & Views
**Controller: `app/Http/Controllers/User/BasicSettingController.php`**
Add methods:
```php
public function watermark()
{
    $data['data'] = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->first();
    return view('user.settings.watermark', $data);
}

public function updateWatermark(Request $request)
{
    $request->validate([
        'watermark_status' => 'required|integer',
        'watermark_text' => 'nullable|string|max:100',
        'watermark_url' => 'nullable|url|max:255',
        'watermark_image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
    ]);

    $bs = BasicSetting::where('user_id', Auth::id())->first();
    
    if (!$bs) {
        $bs = new BasicSetting();
        $bs->user_id = Auth::id();
    }
    
    // Handle image upload
    if ($request->hasFile('watermark_image')) {
        $img = $request->file('watermark_image');
        $filename = uniqid() . '.' . $img->getClientOriginalExtension();
        $img->move(public_path('assets/front/img/user/watermark/'), $filename);
        
        // Remove old image
        if ($bs->watermark_image) {
            @unlink(public_path('assets/front/img/user/watermark/' . $bs->watermark_image));
        }
        $bs->watermark_image = $filename;
    }
    
    $bs->watermark_status = $request->watermark_status;
    $bs->watermark_text = $request->watermark_text;
    $bs->watermark_url = $request->watermark_url;
    $bs->save();
    
    Session::flash('success', __('Updated successfully!'));
    return back();
}
```

**View: `resources/views/user/settings/watermark.blade.php`**
```blade
@extends('user.layout')

@section('content')
<div class="page-header">
    <h4 class="page-title">{{ __('Watermark Badge Settings') }}</h4>
    <ul class="breadcrumbs">
        <li class="nav-home"><a href="{{route('user-dashboard')}}"><i class="flaticon-home"></i></a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">{{ __('Settings') }}</a></li>
        <li class="separator"><i class="flaticon-right-arrow"></i></li>
        <li class="nav-item"><a href="#">{{ __('Watermark Badge') }}</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <form action="{{route('user.watermark.update')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10">
                            <div class="card-title">{{ __('Update Watermark Badge') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">
                            <div class="form-group">
                                <label>{{ __('Watermark Status') }}</label>
                                <div class="selectgroup w-100">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="watermark_status" value="1" class="selectgroup-input" {{ ($data && $data->watermark_status == 1) ? 'checked' : '' }}>
                                        <span class="selectgroup-button">{{ __('Active') }}</span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="watermark_status" value="0" class="selectgroup-input" {{ ($data && $data->watermark_status == 0) ? 'checked' : '' }}>
                                        <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                    </label>
                                </div>
                                @if ($errors->has('watermark_status'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_status')}}</p>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>{{ __('Watermark Text') }}</label>
                                <input type="text" class="form-control" name="watermark_text" value="{{ $data->watermark_text ?? '' }}" placeholder="{{ __('e.g. Secure Payment with Bank Mellat') }}">
                                @if ($errors->has('watermark_text'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_text')}}</p>
                                @endif
                                <small class="form-text text-muted">{{ __('Text to display when no image is provided') }}</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Watermark Link URL') }}</label>
                                <input type="url" class="form-control" name="watermark_url" value="{{ $data->watermark_url ?? '' }}" placeholder="https://example.com">
                                @if ($errors->has('watermark_url'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_url')}}</p>
                                @endif
                                <small class="form-text text-muted">{{ __('Optional link when watermark is clicked') }}</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Watermark Image') }}</label>
                                <input type="file" class="form-control" name="watermark_image" accept="image/*">
                                @if ($errors->has('watermark_image'))
                                    <p class="mb-0 text-danger">{{$errors->first('watermark_image')}}</p>
                                @endif
                                @if ($data && $data->watermark_image)
                                    <div class="mt-2">
                                        <img src="{{ asset('assets/front/img/user/watermark/' . $data->watermark_image) }}" alt="Current Watermark" style="max-width: 150px; height: auto; border-radius: 4px;">
                                        <p class="text-muted mt-1">{{ __('Current watermark image') }}</p>
                                    </div>
                                @endif
                                <small class="form-text text-muted">{{ __('Optional image (max 2MB). If provided, text will be ignored.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

### Phase 7: Routes
**File: `routes/web.php`** - Add routes:
```php
// Admin Watermark
Route::get('/watermark', 'Admin\BasicController@watermark')->name('admin.watermark');
Route::post('/watermark/update', 'Admin\BasicController@updateWatermark')->name('admin.watermark.update');

// User Watermark
Route::get('/watermark', 'User\BasicSettingController@watermark')->name('user.watermark');
Route::post('/watermark/update', 'User\BasicSettingController@updateWatermark')->name('user.watermark.update');
```

### Phase 8: CSS Styling
Add to `resources/views/front/partials/footer.blade.php` or layout:
```css
/* Watermark Badge Styles */
.watermark-badge-container {
    position: fixed !important;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    opacity: 0.7;
    transition: opacity 0.3s ease;
    pointer-events: auto;
}

.watermark-badge-container:hover {
    opacity: 1;
}

.watermark-badge-container a {
    display: inline-block;
    text-decoration: none;
}

.watermark-badge-container img {
    max-width: 120px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.watermark-badge-container .text-badge {
    display: inline-block;
    padding: 8px 16px;
    background: var(--base-color, #007bff);
    color: white;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    white-space: nowrap;
}

@media (max-width: 768px) {
    .watermark-badge-container {
        bottom: 80px;
        right: 15px;
    }
    .watermark-badge-container img {
        max-width: 100px;
    }
    .watermark-badge-container .text-badge {
        font-size: 12px;
        padding: 6px 12px;
    }
}

/* RTL Support */
@media (min-width: 768px) {
    [dir="rtl"] .watermark-badge-container {
        right: auto;
        left: 20px;
    }
}
```

## Security Considerations

1. **Image Validation**: Restrict to jpg, jpeg, png, svg with max 2MB
2. **URL Validation**: Validate URLs with Laravel's URL validation
3. **XSS Protection**: Use `{{ }}` escaping for text fields
4. **File Upload**: Use unique filenames with `uniqid()` to prevent conflicts
5. **Old File Cleanup**: Delete old images when replacing

## Responsive Design

- Desktop: Bottom-right corner (20px from edges)
- Mobile: Moved up to 80px from bottom (above cookie banner)
- RTL Support: Automatically switches to left side for RTL languages
- Z-index: 1000 ensures it's above footer but below modals

## Testing Checklist

- [ ] Admin can enable/disable watermark
- [ ] Admin can set watermark text
- [ ] Admin can set watermark link URL
- [ ] Admin can upload watermark image
- [ ] User can enable/disable their watermark
- [ ] User can set their own watermark text/URL/image
- [ ] Watermark appears on all public pages
- [ ] Watermark does NOT appear on admin panels
- [ ] Watermark does NOT appear on user panels
- [ ] Watermark styling doesn't break footer layout
- [ ] Responsive design works on mobile
- [ ] RTL layout works correctly
- [ ] Cookie banner doesn't overlap watermark on mobile
- [ ] Old images are cleaned up on replacement
- [ ] No console errors
- [ ] No PHP errors

## Success Criteria

1. ✅ Watermark only shows when status = 1
2. ✅ No visual changes when disabled (status = 0)
3. ✅ Doesn't break existing styling
4. ✅ Works on all public pages
5. ✅ Excluded from admin/user panels
6. ✅ Supports both text and image modes
7. ✅ Configurable by admin (global) and user (per-user)
8. ✅ Responsive and RTL compatible