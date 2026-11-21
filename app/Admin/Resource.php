<?php

namespace App\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Describes one admin-managed model: its form fields (with validation), list columns and hooks.
 * One generic controller + two views serve every resource, so a new content type is one small class.
 */
abstract class Resource
{
    public bool $adminOnly = false;

    /** Only superadmins see and use it (custom code). Implies adminOnly. */
    public bool $superOnly = false;

    public bool $readOnly = false;

    /** Show the "copy" action; off for records with a unique identity (languages, users…). */
    public bool $copyable = true;

    /** Set created_by/updated_by from the signed-in user. */
    public bool $tracksUsers = false;

    /** Form fields that are not columns of the model (handled in afterSave). */
    public array $virtual = [];

    public function allows(\App\Models\User $user): bool
    {
        return (! $this->superOnly || $user->isSuper()) && (! $this->adminOnly || $user->isAdmin());
    }

    /** @return class-string<Model> */
    abstract public function model(): string;

    abstract public function key(): string;

    abstract public function title(): string;

    /** @return list<Field> */
    abstract public function fields(?Model $model): array;

    /** @return array<string, string|\Closure> header => attribute path or closure(Model) */
    abstract public function columns(): array;

    /** Attributes the list can be filtered by through the query string (also prefill the create form). */
    public function filters(): array
    {
        return [];
    }

    public function with(): array
    {
        return [];
    }

    /** Links to child resources shown on the edit form: label => url. */
    public function children(Model $model): array
    {
        return [];
    }

    /** Optional read-only panel shown above the edit form (a Blade view that receives $model). */
    public function outlineView(): ?string
    {
        return null;
    }

    /** Deleted rows are soft-deleted when the model supports it, so they can be restored from the trash view. */
    public function hasTrash(): bool
    {
        return in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($this->model()), true);
    }

    /** Short label of a row, for side lists and the trash: the first "name-like" column. */
    public function rowTitle(Model $model): string
    {
        $paths = array_filter($this->columns(), 'is_string');
        $path = collect($paths)->first(fn ($p) => in_array($p, ['title', 'name', 'label', 'question', 'caption', 'code', 'full_name'], true)) ?? array_values($paths)[0] ?? 'id';

        return Format::cell(data_get($model, $path));
    }

    /** Small image per row (first of image_id, logo_id, media_id…), loaded in one query: [row id => MediaAsset]. */
    public function thumbnails(\Illuminate\Support\Collection $rows): array
    {
        $column = collect(['image_id', 'logo_id', 'media_id', 'icon_media_id', 'og_media_id'])
            ->first(fn ($c) => in_array($c, (new ($this->model()))->getFillable(), true));

        if (! $column) {
            return [];
        }

        $assets = \App\Models\MediaAsset::whereIn('id', $rows->pluck($column)->filter())->get()->keyBy('id');

        return $rows->mapWithKeys(fn ($row) => [$row->id => $assets->get($row->{$column})])->filter()->all();
    }

    /** Public address of the record (for the "view" link), when it has one. */
    public function viewUrl(Model $model): ?string
    {
        if (! method_exists($model, 'path')) {
            return null;
        }

        $model->loadMissing(array_filter(['language', 'page'], fn ($r) => method_exists($model, $r)));

        return \App\Support\PublicUrl::path($model->path());
    }

    /** Cross-field validation; throw ValidationException on failure. */
    public function check(array $data, ?Model $model): void {}

    public function afterSave(Model $model, array $data): void {}

    public function canDelete(Model $model): bool
    {
        return true;
    }

    public function validate(Request $request, ?Model $model): array
    {
        $fields = $this->fields($model);
        $data = $request->validate(collect($fields)->mapWithKeys(fn (Field $f) => [$f->name => $f->rules])->all());

        foreach ($fields as $field) {
            if ($field->type === 'checkbox') {
                $data[$field->name] = $request->boolean($field->name);
            }
            if ($field->type === 'password' && empty($data[$field->name])) {
                unset($data[$field->name]);
            }
        }

        $this->check($data, $model);
        $this->checkRelationLanguages($fields, $data);

        return $data;
    }

    /** Extra pages/categories must be in the record's language, like the main ones. */
    private function checkRelationLanguages(array $fields, array $data): void
    {
        foreach ($fields as $field) {
            if (! $field->relation || empty($data[$field->name]) || empty($data['language_id'])) {
                continue;
            }

            $related = (new ($this->model()))->{$field->relation}()->getRelated();

            if ($related::query()->whereIn('id', $data[$field->name])->where('language_id', '!=', $data['language_id'])->exists()) {
                throw ValidationException::withMessages([$field->name => 'همهٔ موردها باید هم‌زبان رکورد باشند.']);
            }
        }
    }

    /**
     * Fields to copy over when a record is duplicated: called on the new, already saved copy.
     * Resources with child rows (page modules, gallery items…) override it.
     */
    public function copyChildren(Model $from, Model $to): void {}

    /** Make a fresh copy distinguishable and safe: draft, new slug, no unique flags. */
    public function prepareCopy(Model $copy): void
    {
        foreach (['title', 'name', 'label', 'question'] as $attribute) {
            if (isset($copy->{$attribute})) {
                $copy->{$attribute} = mb_substr($copy->{$attribute}, 0, 170).' (کپی)';
                break;
            }
        }

        foreach (['slug', 'translation_key', 'is_home', 'is_current', 'is_default'] as $unique) {
            if (array_key_exists($unique, $copy->getAttributes())) {
                $copy->{$unique} = null;
            }
        }

        if (array_key_exists('status', $copy->getAttributes())) {
            $copy->status = 'draft';
        }

        if (array_key_exists('view_count', $copy->getAttributes())) {
            $copy->view_count = 0;
        }
    }

    /**
     * Every referenced record must be in the same language as the record being saved.
     *
     * @param  array<string, class-string<Model>>  $references  field => model class
     */
    protected function requireSameLanguage(int $languageId, array $data, array $references): void
    {
        $errors = [];

        foreach ($references as $field => $class) {
            if (! empty($data[$field]) && (int) $class::query()->whereKey($data[$field])->value('language_id') !== $languageId) {
                $errors[$field] = 'این مورد باید هم‌زبان رکورد باشد.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
