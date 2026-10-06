<footer class="footer">
  @if (Session::has('admin_lang'))
    @php
        $admin_lang = Session::get('admin_lang');
        $cd = str_replace('admin_', '', $admin_lang);
        $default = \App\Models\Language::where('code', $cd)->first();
    @endphp
@else
    @php
        $default = \App\Models\Language::where('is_default', 1)->first();
    @endphp
@endif
  @php
  $copyRightText = \App\Models\BasicSetting::select('copyright_text')->where('language_id', $default->id)->firstOrFail();
  @endphp
  <div class="container-fluid">
    <div class="d-block mx-auto">
      {!! replaceBaseUrl($copyRightText->copyright_text) !!}
    </div>
  </div>
    {{-- Enamad trust seal, rendered in the footer flow so it
         follows the theme instead of floating over it. --}}
    @include('partials.enamad', [
        'enamad_status' => $bs->enamad_status ?? 0,
        'enamad_site_id' => $bs->enamad_site_id ?? '',
        'enamad_code' => $bs->enamad_code ?? '',
        'enamad_logo_type' => $bs->enamad_logo_type ?? 'auto',
    ])
</footer>
