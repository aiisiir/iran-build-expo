<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * "Only one row may be true" flags are stored as 1 or NULL behind a UNIQUE index.
 * List the column names in $singletonFlags on the model.
 */
trait HasSingletonFlag
{
    protected static function bootHasSingletonFlag(): void
    {
        static::saving(function ($model): void {
            foreach ($model->singletonFlags as $flag) {
                $model->{$flag} = $model->{$flag} ? 1 : null;
            }
        });
    }

    /** Make this row the one holder of $flag, clearing it from the others in $scope. */
    public function claimFlag(string $flag, array $scope = []): void
    {
        DB::transaction(function () use ($flag, $scope): void {
            static::query()->withoutGlobalScopes()->where($scope)->where($flag, 1)
                ->whereKeyNot($this->getKey())->update([$flag => null]);
            $this->forceFill([$flag => 1])->save();
        });
    }
}
