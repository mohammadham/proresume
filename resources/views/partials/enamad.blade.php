@php
$enamadStatus = $enamadStatus ?? (isset($abs) ? $abs->enamad_status : (isset($data) ? $data->enamad_status : (isset($watermarkStatus) ? false : false)));
$enamadSiteId = $enamadSiteId ?? (isset($abs) ? $abs->enamad_site_id : (isset($data) ? $data->enamad_site_id : ''));
$enamadLogoType = $enamadLogoType ?? (isset($abs) ? $abs->enamad_logo_type : (isset($data) ? $data->enamad_logo_type : 'auto'));
$enamadStatus = $enamadStatus ?? 0;
@endphp

@if ($enamadStatus == 1 && $enamadSiteId)
<div class="enamad-badge-container" 
     style="position: fixed; 
            bottom: {{ config('enamad.widget.mobile_bottom', 80) }}px; 
            left: {{ config('enamad.widget.mobile_right', 15) }}px; 
            z-index: {{ config('enamad.widget.z_index', 1000) }}; 
            opacity: {{ config('enamad.widget.opacity', 0.8) }}; 
            transition: opacity 0.3s;"
     id="enamad-seal">
    
    <a href="https://trustseal.enamad.ir/verify?id={{ $enamadSiteId }}" 
       target="_blank" 
       rel="noopener noreferrer"
       title="{{ __('نماد اعتماد الکترونیک') }}">
        <img src="https://trustseal.enamad.ir/logo.aspx?id={{ $enamadSiteId }}&type={{ $enamadLogoType ?? 'auto' }}"
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

@endif

@if ($enamadStatus == 1 && $enamadSiteId)
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