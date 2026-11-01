<?php

namespace App\Admin;

use Closure;

/** One form field of an admin resource: label, input type and its validation rules in a single place. */
final class Field
{
    private function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $type,
        public readonly array $rules,
        public readonly mixed $options = null, // select/multiselect: array|Closure returning [value => label]
        public readonly ?string $help = null,
        public readonly mixed $default = null,
        public readonly ?Closure $loader = null, // derived value for virtual fields: fn (Model) => value
    ) {}

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
        return new self($name, $label, 'select', $rules, $options, $help, $default);
    }

    public static function multiselect(string $name, string $label, array|Closure $options, Closure $loader): self
    {
        return new self($name, $label, 'multiselect', ['nullable', 'array'], $options, null, [], $loader);
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
