<?php

namespace App\Admin;

use App\Enums\CategoryType;
use App\Enums\MediaKind;
use Illuminate\Validation\Rule;
use Closure;
use Illuminate\Database\Eloquent\Model;

/** One form field of an admin resource: label, input type and its validation rules in a single place. */
final class Field
{
    /** Form tabs in display order: key => label. */
    public const TABS = ['general' => 'عمومی', 'place' => 'دسته‌بندی، صفحه و زبان', 'media' => 'تصاویر و فایل‌ها', 'publish' => 'انتشار', 'seo' => 'سئو', 'advanced' => 'پیشرفته'];

    private ?string $tab = null;

    private ?MediaKind $kind = null;

    private function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $type,
        public readonly array $rules,
        public readonly mixed $options = null, // select/multiselect: array|Closure returning [value => label]
        public readonly ?string $help = null,
        public readonly mixed $default = null,
        public readonly ?Closure $loader = null, // derived value for virtual fields: fn (Model) => value
        public readonly ?string $relation = null, // multiselect: BelongsToMany/MorphToMany method that the controller syncs
    ) {}

    /** Put the field on another tab than the one guessed from its name. */
    public function in(string $tab): self
    {
        $this->tab = $tab;

        return $this;
    }

    public function tab(): string
    {
        return $this->tab ?? match (true) {
            (bool) preg_match('/^(meta_|og_|noindex)/', $this->name) => 'seo',
            (bool) preg_match('/^css_/', $this->name) => 'advanced',
            (bool) preg_match('/^(language_id|translation_key|page_id|source_page_id|placed_pages)$|categor/', $this->name) => 'place',
            (bool) preg_match('/(image|media|logo|icon|file|photo)/', $this->name) => 'media',
            (bool) preg_match('/^(status|published_at|expires_at|is_active|is_featured|sort_order|show_|edition)/', $this->name) => 'publish',
            default => 'general',
        };
    }

    public static function text(string $name, string $label, array $rules = ['nullable', 'string', 'max:191'], ?string $help = null, mixed $default = null): self
    {
        return new self($name, $label, 'text', $rules, null, $help, $default);
    }

    public static function textarea(string $name, string $label, array $rules = ['nullable', 'string', 'max:5000'], ?string $help = null): self
    {
        return new self($name, $label, 'textarea', $rules, null, $help);
    }

    public static function rich(string $name, string $label, array $rules = ['nullable', 'string', 'max:500000']): self
    {
        return new self($name, $label, 'rich', $rules);
    }

    public static function number(string $name, string $label, int $default = 0, ?string $help = null): self
    {
        return new self($name, $label, 'number', ['nullable', 'integer', 'min:0', 'max:65535'], null, $help, $default);
    }

    public static function checkbox(string $name, string $label, bool $default = true, ?Closure $loader = null): self
    {
        return new self($name, $label, 'checkbox', ['boolean'], null, null, $default, $loader);
    }

    public static function select(string $name, string $label, array|Closure $options, array $rules, mixed $default = null, ?string $help = null): self
    {
        $default ??= $name === 'language_id' ? \App\Models\Language::default()?->id : null; // forms start on the default language

        return new self($name, $label, 'select', $rules, $options, $help, $default);
    }

    /** Picture/file chosen in a popup (search, thumbnails, upload); stores a media_assets id. */
    public static function media(string $name, string $label, ?MediaKind $kind, array $rules, ?string $help = null): self
    {
        $exists = Rule::exists('media_assets', 'id')->whereNull('deleted_at')->when($kind, fn ($r) => $r->where('kind', $kind->value));
        $field = new self($name, $label, 'media', array_map(fn ($r) => $r === 'exists:media_assets,id' ? $exists : $r, $rules), null, $help);
        $field->kind = $kind;

        return $field;
    }

    public function mediaKind(): ?MediaKind
    {
        return $this->kind;
    }

    public static function multiselect(string $name, string $label, array|Closure $options, Closure $loader, ?string $relation = null, ?string $help = null): self
    {
        return new self($name, $label, 'multiselect', ['nullable', 'array'], $options, $help, [], $loader, $relation);
    }

    /** "Also shown in these pages" (HasPlacements); the record's own page field stays the main/canonical page. */
    public static function placedPages(): self
    {
        return self::multiselect('placed_pages', 'نمایش در صفحه‌های دیگر', fn () => Options::pages(),
            fn (Model $m) => $m->exists ? $m->placedPages()->pluck('pages.id')->all() : [], 'placedPages',
            'هم‌زبان رکورد باشند. اگر رکورد آدرس خودش را دارد، این صفحه‌ها همان آدرس را با canonical به آدرس اصلی نشان می‌دهند.');
    }

    /** "Also in these categories" (HasExtraCategories). */
    public static function extraCategories(CategoryType $type): self
    {
        return self::multiselect('extra_categories', 'دسته‌های دیگر', fn () => Options::categories($type),
            fn (Model $m) => $m->exists ? $m->extraCategories()->pluck('categories.id')->all() : [], 'extraCategories', 'هم‌زبان رکورد باشند.');
    }

    public static function datetime(string $name, string $label, ?string $help = null): self
    {
        return new self($name, $label, 'datetime', ['nullable', 'date'], null, $help);
    }

    public static function date(string $name, string $label): self
    {
        return new self($name, $label, 'date', ['nullable', 'date']);
    }

    public static function password(string $name, string $label, bool $required): self
    {
        return new self($name, $label, 'password', [$required ? 'required' : 'nullable', 'string', 'min:6', 'max:200']);
    }

    public static function color(string $name, string $label, string $default = '#1d3557'): self
    {
        return new self($name, $label, 'color', ['required', 'regex:/^#[0-9a-fA-F]{6}$/'], null, null, $default);
    }

    public function choices(): array
    {
        return $this->options instanceof Closure ? ($this->options)() : (array) $this->options;
    }

    public function isRequired(): bool
    {
        return in_array('required', $this->rules, true);
    }
}
