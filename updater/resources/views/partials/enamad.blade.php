@php
    // Two presentations, one partial:
    //
    //   widget (default) - the floating corner badge the Enamad widget expects.
    //                      Used by the front site footer, unchanged.
    //   footer           - an in-flow seal that sits inside the <footer> block
    //                      of a template. Used by every tenant theme, the
    //                      tenant dashboard and the admin panel, where a
    //                      fixed-position badge would either cover page
    //                      content or drift out of the theme's own layout.
    //
    // Data may arrive as explicit scalars (what the front footer passes) or as
    // a settings row: basic_settings for the site seal, user_basic_settings
    // for a tenant's own seal. Both camelCase (legacy) and snake_case spellings
    // are accepted so no existing caller breaks.
    $variant = $enamad_variant ?? 'widget';

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

    $showWidget = $enamadStatus === 1 && $enamadSiteId !== '';
    $showFooter = $showWidget && (bool) config('enamad.footer.enabled', true);
@endphp

@if ($showFooter && $variant === 'footer')
    {{-- In-flow seal: lives inside the template's own footer, so it inherits
         the theme's alignment and never covers page content. --}}
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

@if ($showWidget && $variant !== 'footer')
<div class="enamad-badge-container" 
     style="position: fixed; 
            bottom: {{ config('enamad.widget.mobile_bottom', 80) }}px; 
            left: {{ config('enamad.widget.mobile_right', 15) }}px; 
            z-index: {{ config('enamad.widget.z_index', 1000) }}; 
            opacity: {{ config('enamad.widget.opacity', 0.8) }}; 
            transition: opacity 0.3s;"
     id="enamad-seal">
    
    <a href="https://trustseal.enamad.ir/verify?{{ $enamadQuery }}" 
       target="_blank" 
       rel="noopener noreferrer"
       title="{{ __('نماد اعتماد الکترونیک') }}">
        <img src="https://trustseal.enamad.ir/logo.aspx?{{ $enamadQuery }}"
             alt="{{ __('نماد اعتماد الکترونیک') }}"
             style="width: {{ config('enamad.logo.width', 100) }}px; 
                    height: {{ config('enamad.logo.height', 'auto') }}; 
                    border-radius: {{ config('enamad.widget.border_radius', 8) }}px; 
                    box-shadow: {{ config('enamad.widget.box_shadow', '0 4px 12px rgba(0,0,0,0.15)') }};">
    </a>
</div>

<style>
.enamad-badge-container:hover {
    opacity: {{ config('enamad.widget.hover_opacity', 1.0) }};
}

@media (max-width: 768px) {
    .enamad-badge-container {
        bottom: {{ config('enamad.widget.mobile_bottom', 80) }}px;
        left: {{ config('enamad.widget.mobile_right', 15) }}px;
    }
    .enamad-badge-container img {
        max-width: 80px;
    }
}

@media (min-width: 769px) {
    .enamad-badge-container {
        bottom: 20px;
        left: 20px;
    }
    [dir="rtl"] .enamad-badge-container {
        left: auto;
        right: 20px;
    }
}
</style>

<script>
(function() {
    var siteId = '{{ $enamadSiteId }}';
    var container = document.getElementById('enamad-seal');
    
    // Load Enamad Trust Seal widget if available
    if (typeof EnamadTrustSeal !== 'undefined') {
        EnamadTrustSeal.init({
            id: siteId,
            container: 'enamad-seal'
        });
    } else {
        // Fallback: load the widget script dynamically
        var script = document.createElement('script');
        script.src = 'https://trustseal.enamad.ir/Static/js/trustseal.js';
        script.onload = function() {
            if (typeof EnamadTrustSeal !== 'undefined') {
                EnamadTrustSeal.init({
                    id: '{{ $enamadSiteId }}',
                    container: 'enamad-seal'
                });
            }
        };
        document.head.appendChild(script);
    }
})();
</script>
@endif