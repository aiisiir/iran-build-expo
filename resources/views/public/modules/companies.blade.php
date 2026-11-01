<ul class="companies">
    @foreach ($module->items as $company)
        <li>
            <a href="{{ \App\Support\PublicUrl::path($company->path()) }}">
                @if ($company->logo)@include('public.partials.image', ['media' => $company->logo, 'alt' => $company->name])@endif
                <span>{{ $company->name }}</span>
                @if ($company->industry)<small class="badge-industry">{{ $company->industry }}</small>@endif
            </a>
        </li>
    @endforeach
</ul>
