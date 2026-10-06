@php
    // The trust seal, rendered in the page flow at the bottom of the footer.
    //
    // It used to have a second, fixed-position variant that floated over the
    // bottom corner of the viewport. Nothing calls it any more: on a
    // single-page portfolio that badge covered content and ignored the
    // theme's own layout, and a seal a site owner cannot place is a seal
    // they remove. Every footer now asks for this one shape.
    //
    // Data may arrive as explicit scalars (what the footers pass) or as a
    // settings row: basic_settings for the site seal, user_basic_settings
    // for a tenant's own. Both camelCase (legacy) and snake_case spellings
    // are accepted so no existing caller breaks.
    $enamadStatus   = $enamadStatus   ?? $enamad_status   ?? (isset($abs) ? ($abs->enamad_status ?? 0) : 0);
    $enamadSiteId   = $enamadSiteId   ?? $enamad_site_id   ?? (isset($abs) ? ($abs->enamad_site_id ?? '') : '');
    $enamadCode     = $enamadCode     ?? $enamad_code     ?? (isset($abs) ? ($abs->enamad_code ?? '') : '');
    $enamadLogoType = $enamadLogoType ?? $enamad_logo_type ?? (isset($abs) ? ($abs->enamad_logo_type ?? 'auto') : 'auto');

    $enamadStatus = (int) $enamadStatus;
    $enamadSiteId = (string) $enamadSiteId;
    $enamadCode = trim((string) $enamadCode);
    $enamadLogoType = trim((string) $enamadLogoType) ?: 'auto';

    // Enamad keys the seal off both id and code; omitting the code still
    // renders, but the seal is then resolved from the id alone and can come
    // back as a generic placeholder for renewals.
    $enamadQuery = 'id=' . rawurlencode($enamadSiteId)
        . ($enamadCode !== '' ? '&Code=' . rawurlencode($enamadCode) : '')
        . '&type=' . rawurlencode($enamadLogoType);

    $show = $enamadStatus === 1 && $enamadSiteId !== '' && (bool) config('enamad.footer.enabled', true);
@endphp

@if ($show)
    {{-- In-flow: sits inside the template's own footer, so it inherits the
         theme's alignment and never covers page content. No widget script -
         the image alone is enough. --}}
    <div class="enamad-footer-seal" style="text-align: {{ config('enamad.footer.alignment', 'center') }}; margin: {{ config('enamad.footer.margin', '18px') }} auto 0;">
        <a href="https://trustseal.enamad.ir/verify?{{ $enamadQuery }}"
           target="_blank"
           rel="noopener noreferrer"
           title="{{ __('نماد اعتماد الکترونیک') }}"
           style="display: inline-block; line-height: 0;">
            <img src="https://trustseal.enamad.ir/logo.aspx?{{ $enamadQuery }}"
                 width="{{ config('enamad.footer.width', 125) }}"
                 height="{{ config('enamad.footer.height', 36) }}"
                 alt="{{ __('نماد اعتماد الکترونیک') }}"
                 loading="lazy"
                 style="border-radius: {{ config('enamad.widget.border_radius', 8) }}px; box-shadow: {{ config('enamad.widget.box_shadow', '0 4px 12px rgba(0,0,0,0.15)') }};">
        </a>
        @if (config('enamad.footer.show_title', true))
            <div class="enamad-footer-seal-title"
                 style="margin-top: 6px; font-size: {{ config('enamad.footer.title_size', 12) }}px; opacity: 0.75;">
                {{ __('نماد اعتماد الکترونیک') }}
            </div>
        @endif
    </div>
@endif