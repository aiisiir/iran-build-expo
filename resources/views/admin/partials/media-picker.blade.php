{{-- One shared popup for every media field (see admin.js). --}}
<dialog id="media-picker" data-list="{{ route('admin.media.picker') }}" data-upload="{{ route('admin.media.quick') }}" aria-labelledby="picker-title">
    <header>
        <h2 id="picker-title">انتخاب تصویر یا فایل</h2>
        <button type="button" class="link" data-picker-close aria-label="بستن">✕</button>
    </header>
    <div class="picker-tabs" role="tablist">
        <button type="button" role="tab" aria-selected="true" data-picker-tab="library">کتابخانه</button>
        <button type="button" role="tab" aria-selected="false" data-picker-tab="upload">بارگذاری جدید</button>
    </div>
    <section data-picker-pane="library">
        <input type="search" name="q" placeholder="جستجو در نام، عنوان یا متن جایگزین…" aria-label="جستجو">
        <ul class="picker-grid"></ul>
        <p class="muted" data-picker-empty hidden>موردی پیدا نشد.</p>
        <button type="button" class="btn ghost" data-picker-more hidden>نمایش بیشتر</button>
    </section>
    <section data-picker-pane="upload" hidden>
        <p class="muted">فایل همین‌جا بارگذاری و انتخاب می‌شود. تصویر تا ۲ مگابایت، PDF تا ۲۰ مگابایت.</p>
        <input type="file" name="file" accept="image/jpeg,image/png,image/webp,image/svg+xml,application/pdf" aria-label="فایل">
        <input type="text" name="alt" maxlength="191" placeholder="متن جایگزین تصویر (alt) — برای سئو و دسترسی" aria-label="متن جایگزین">
        <button type="button" class="btn" data-picker-upload>بارگذاری و انتخاب</button>
        <p class="error" role="alert" data-picker-error hidden></p>
    </section>
</dialog>
