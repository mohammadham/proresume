{{-- @php
    $user = Auth::guard('web')->user();
    $userLanguages = \App\Models\User\Language::where('user_id', $user->id)->get();
    
@endphp
@if (Session::has('currentLangCode'))
    @php
        $default = \App\Models\User\Language::where('code', Session::get('currentLangCode'))
            ->where('user_id', $user->id)
            ->first();
    @endphp
@else
    @php
        $default = \App\Models\User\Language::where('is_default', 1)
            ->where('user_id', $user->id)
            ->first();
    @endphp
@endif
@if (!empty($userLanguages))
    <select name="userLanguage" class="form-control language-select"
        onchange="window.location='{{ url()->current() . '?language=' }}'+this.value">

        <option disabled> {{ __('Select a Language') }} </option>
        @foreach ($userLanguages as $lang)
            <option value="{{ $lang->code }}"
                {{ $lang->code == request()->input('language') ? 'selected' : ($default->code == $lang->code ? 'selected' : '') }}>
                {{ $lang->name }}
            </option>
        @endforeach
    </select>
@endif --}}
