@extends('user.layout')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4>یکپارچگی اپ موبایل</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('api.integration.update') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>نوع فعالیت</label>
                        <select name="app_type" class="form-control">
                            <option value="barber" {{ old('app_type', $data->app_type ?? 'barber') == 'barber' ? 'selected' : '' }}>آرایشگر</option>
                            <option value="doctor" {{ old('app_type', $data->app_type ?? '') == 'doctor' ? 'selected' : '' }}>دکتر</option>
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label>
                            <input type="checkbox" name="is_active" {{ old('is_active', $data->is_active ?? false) ? 'checked' : '' }}>
                            فعال‌سازی یکپارچگی
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">ذخیره</button>
                </form>

                @if($data)
                <hr>
                <h5>API Key</h5>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ $data->api_key }}" readonly>
                    <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $data->api_key }}')">کپی</button>
                    <form action="{{ route('api.integration.regenerate') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">بازنشانی</button>
                    </form>
                </div>
                @endif

                <hr>
                <h5>اطلاعات مکانی</h5>
                <form action="{{ route('api.integration.profile') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>استان</label>
                        <select name="state" id="province" class="form-control">
                            <option value="">انتخاب کنید</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->name }}" {{ old('state', $user->state ?? '') == $province->name ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>شهر</label>
                        <select name="city" id="city" class="form-control">
                            <option value="">انتخاب کنید</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->name }}" {{ old('city', $user->city ?? '') == $city->name ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>منطقه</label>
                        <input type="text" name="district" class="form-control" value="{{ old('district', $user->district ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label>آدرس</label>
                        <textarea name="address" class="form-control">{{ old('address', $user->address ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>عرض جغرافیایی (Lat)</label>
                                <input type="text" name="lat" class="form-control" value="{{ old('lat', $user->lat ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>طول جغرافیایی (Lng)</label>
                                <input type="text" name="lng" class="form-control" value="{{ old('lng', $user->lng ?? '') }}">
                            </div>
                        </div>
                    </div>

                    @if($data && $data->app_type == 'doctor')
                    <div class="form-group">
                        <label>تخصص</label>
                        <input type="text" name="specialty" class="form-control" value="{{ old('specialty', $user->specialty ?? '') }}">
                    </div>
                    @endif

                    <button type="submit" class="btn btn-primary mt-3">ذخیره اطلاعات</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$('#province').on('change', function() {
    var provinceName = $(this).val();
    // AJAX call to get cities
});
</script>
@endsection
