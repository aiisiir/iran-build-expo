<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Registry;
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
    public function index(Request $request, string $resource): View
    {
        $res = $this->resolve($request, $resource);
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

    public function create(Request $request, string $resource): View
    {
        $res = $this->resolve($request, $resource, writable: true);
        $model = (new ($res->model()))->forceFill($request->only($res->filters()));

        return view('admin.resource.form', ['resource' => $res, 'model' => $model]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $res = $this->resolve($request, $resource, writable: true);
        $model = $this->persist($res, new ($res->model()), $request);

        return $this->back($res, $model);
    }

    public function edit(Request $request, string $resource, int $id): View
    {
        $res = $this->resolve($request, $resource, writable: true);

        return view('admin.resource.form', ['resource' => $res, 'model' => $res->model()::query()->findOrFail($id)]);
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $res = $this->resolve($request, $resource, writable: true);
        $model = $this->persist($res, $res->model()::query()->findOrFail($id), $request);

        return $this->back($res, $model);
    }

    public function destroy(Request $request, string $resource, int $id): RedirectResponse
    {
        $res = $this->resolve($request, $resource, writable: true);
        $model = $res->model()::query()->findOrFail($id);
        abort_unless($res->canDelete($model), 403);
        $model->delete(); // soft delete: the row stays in the database

        return $this->back($res, $model, 'حذف شد (در دیتابیس باقی می‌ماند).');
    }

    private function resolve(Request $request, string $key, bool $writable = false): Resource
    {
        $res = Registry::find($key);
        abort_if($res === null || ($writable && $res->readOnly), 404);
        abort_if($res->adminOnly && ! $request->user()->isAdmin(), 403);

        return $res;
    }

    private function persist(Resource $res, Model $model, Request $request): Model
    {
        $data = $res->validate($request, $model->exists ? $model : null);

        DB::transaction(function () use ($res, $model, $data): void {
            $model->fill(Arr::except($data, $res->virtual));

            if ($res->tracksUsers) {
                if (! $model->exists) {
                    $model->created_by = auth()->id();
                }
                $model->updated_by = auth()->id();
            }

            $model->save();
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
