{{-- Picture viewer shared by every gallery on the page (see site.js): one dialog, opened from any [data-lightbox] group. --}}
@once
<dialog class="lightbox" aria-label="{{ __('site.gallery') }}" hidden>
    <button type="button" class="lb-close" data-lb-close aria-label="{{ __('site.close') }}"><x-icon name="close" /></button>
    <button type="button" class="lb-nav lb-prev" data-lb-prev aria-label="{{ __('site.previous') }}"><x-icon name="chevron" /></button>
    <figure><img alt=""><figcaption></figcaption></figure>
    <button type="button" class="lb-nav lb-next" data-lb-next aria-label="{{ __('site.next') }}"><x-icon name="chevron" /></button>
    <span class="lb-count" aria-live="polite"></span>
</dialog>
@endonce
