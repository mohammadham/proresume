@if ($watermark_status == 1)
@php
    // Base path of the badge image. Defaults to the admin upload dir;
    // pass 'watermark_base' => 'assets/front/img/user/watermark/' when
    // including this partial with a user's settings row.
    $watermarkBase = $watermark_base ?? 'assets/front/img/';
    // Only http(s) links are allowed so a crafted setting can never
    // become a javascript: or data: href.
    $watermarkHref = (!empty($watermark_url) && preg_match('/^https?:\/\//i', $watermark_url)) ? $watermark_url : '#';
@endphp
<div class="watermark-badge-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; opacity: 0.7; transition: opacity 0.3s;">
    @if ($watermark_image)
        <a href="{{ $watermarkHref }}" target="_blank" rel="noopener noreferrer">
            <img src="{{ asset($watermarkBase . $watermark_image) }}"
                 alt="{{ $watermark_text ?? 'Watermark' }}"
                 style="max-width: 120px; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        </a>
    @elseif ($watermark_text)
        <a href="{{ $watermarkHref }}" target="_blank" rel="noopener noreferrer"
           style="display: inline-block; padding: 8px 16px; background: var(--base-color, #007bff); color: white;
                  border-radius: 50px; font-size: 13px; font-weight: 600; text-decoration: none;
                  box-shadow: 0 4px 12px rgba(0,0,0,0.15); white-space: nowrap;"
           class="text-badge">
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
</style>
@endif