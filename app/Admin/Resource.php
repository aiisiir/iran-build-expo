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

    public bool $readOnly = false;

    /** Set created_by/updated_by from the signed-in user. */
    public bool $tracksUsers = false;

    /** Form fields that are not columns of the model (handled in afterSave). */
    public array $virtual = [];

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

        return $data;
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
