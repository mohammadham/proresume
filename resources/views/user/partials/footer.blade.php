<footer class="footer">
  <div class="container-fluid">
    <div class="d-block mx-auto">
      {!! replaceBaseUrl($bs->copyright_text) !!}
    </div>
  </div>
    {{-- Enamad trust seal, rendered in the footer flow so it
         follows the theme instead of floating over it. --}}
    @include('partials.enamad', [
        'enamad_status' => $userBs->enamad_status ?? 0,
        'enamad_site_id' => $userBs->enamad_site_id ?? '',
        'enamad_code' => $userBs->enamad_code ?? '',
        'enamad_logo_type' => $userBs->enamad_logo_type ?? 'auto',
    ])
</footer>
