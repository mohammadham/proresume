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
</footer>
