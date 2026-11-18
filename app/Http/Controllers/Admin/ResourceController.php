<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Resource;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/** One controller for every Resource: list, create, edit, soft-delete. */
class ResourceController
{
    public function index(Request $request, Resource $resource): View
    {
        $res = $this->authorize($request, $resource);
        $query = $res->model()::query()->with($res->with())->latest('id');

        foreach ($res->filters() as $column) {
            if ($request->filled($column)) {
                $query->where($column, $request->integer($column));
            }
        }

        return view('admin.resource.index', [
            'resource' => $res,
            'rows' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only($res->filters()),
        ]);
    }

    public function create(Request $request, Resource $resource): View
    {
        $res = $this->authorize($request, $resource, writable: true);
        $model = (new ($res->model()))->forceFill($request->only($res->filters()));

        return view('admin.resource.form', ['resource' => $res, 'model' => $model]);
    }

    public function store(Request $request, Resource $resource): RedirectResponse
    {
        $res = $this->authorize($request, $resource, writable: true);
        $model = $this->persist($res, new ($res->model()), $request);

        return $this->back($res, $model);
    }

    public function edit(Request $request, Resource $resource, int $id): View
    {
        $res = $this->authorize($request, $resource, writable: true);

        return view('admin.resource.form', ['resource' => $res, 'model' => $res->model()::query()->findOrFail($id)]);
    }

    public function update(Request $request, Resource $resource, int $id): RedirectResponse
    {
        $res = $this->authorize($request, $resource, writable: true);
        $model = $this->persist($res, $res->model()::query()->findOrFail($id), $request);

        return $this->back($res, $model);
    }

    public function destroy(Request $request, Resource $resource, int $id): RedirectResponse
    {
        $res = $this->authorize($request, $resource, writable: true);
        $model = $res->model()::query()->findOrFail($id);
        abort_unless($res->canDelete($model), 403);
        $model->delete(); // soft delete: the row stays in the database

        return $this->back($res, $model, 'حذف شد.');
    }

    public function restore(Request $request, Resource $resource, int $id): RedirectResponse
    {
        $res = $this->authorize($request, $resource, writable: true);
        abort_unless($res->hasTrash(), 404);
        $model = $res->model()::onlyTrashed()->findOrFail($id);
        $model->restore();

        return $request->input('from') === 'trash'
            ? redirect()->route('admin.trash')->with('status', 'بازیابی شد.')
            : $this->back($res, $model, 'بازیابی شد.');
    }

    /** Duplicate a record with all its settings (extra pages/categories, child rows) as a draft, then open it for editing. */
    public function replicate(Request $request, Resource $resource, int $id): RedirectResponse
    {
        $res = $this->authorize($request, $resource, writable: true);
        abort_unless($res->copyable, 404);
        $source = $res->model()::query()->findOrFail($id);

        $copy = DB::transaction(function () use ($res, $source): Model {
            $copy = $source->replicate();
            $res->prepareCopy($copy);

            if ($res->tracksUsers) {
                $copy->created_by = $copy->updated_by = auth()->id();
            }

            $copy->save();

            foreach ($res->fields($source) as $field) {
                if ($field->relation) {
                    $copy->{$field->relation}()->sync(($field->loader)($source));
                }
            }

            $res->copyChildren($source, $copy);

            return $copy;
        });

        return redirect()->route('admin.resource.edit', ['resource' => $res->key(), 'id' => $copy->id])
            ->with('status', 'کپی ساخته شد؛ عنوان و آدرس را بازبینی کنید.');
    }

    private function authorize(Request $request, Resource $res, bool $writable = false): Resource
    {
        abort_if($writable && $res->readOnly, 404);
        abort_if($res->adminOnly && ! $request->user()->isAdmin(), 403);

        return $res;
    }

    private function persist(Resource $res, Model $model, Request $request): Model
    {
        $data = $res->validate($request, $model->exists ? $model : null);

        DB::transaction(function () use ($res, $model, $data): void {
            $relations = collect($res->fields($model->exists ? $model : null))->filter(fn ($f) => $f->relation);
            $model->fill(Arr::except($data, [...$res->virtual, ...$relations->pluck('name')]));

            if ($res->tracksUsers) {
                if (! $model->exists) {
                    $model->created_by = auth()->id();
                }
                $model->updated_by = auth()->id();
            }

            $model->save();

            foreach ($relations as $field) {
                $main = $field->relation === 'placedPages' ? 'page_id' : 'category_id'; // the main one is not repeated as an extra
                $model->{$field->relation}()->sync(array_diff((array) ($data[$field->name] ?? []), [$model->{$main}]));
            }

            $res->afterSave($model, $data);
        });

        return $model;
    }

    private function back(Resource $res, Model $model, string $message = 'ذخیره شد.'): RedirectResponse
    {
        $filters = Arr::only($model->getAttributes(), $res->filters());

        return redirect()->route('admin.resource.index', ['resource' => $res->key(), ...$filters])->with('status', $message);
    }
}
