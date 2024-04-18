<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Response\Status;
use App\Functions\GlobalFunction;

use App\Models\Warehouse;

use App\Http\Requests\Warehouse\StoreRequest;
use App\Http\Requests\Warehouse\DisplayRequest;
use App\Http\Requests\Warehouse\Validation\CodeRequest;

class WarehouseController extends Controller
{
    public function index(DisplayRequest $request)
    {
        $status = $request->status;
        $search = $request->search;
        $paginate = isset($request->paginate) ? $request->paginate : 1;

        $warehouse = Warehouse::when($status === "inactive", function ($query) {
            $query->onlyTrashed();
        })->when($search, function ($query) use ($search) {
            $query
                ->where("code", "like", "%" . $search . "%")
                ->orWhere("name", "like", "%" . $search . "%");
        });

        $warehouse = $paginate
            ? $warehouse->orderByDesc("updated_at")->paginate($request->rows)
            : $warehouse->orderByDesc("updated_at")->get();

        $is_empty = $warehouse->isEmpty();

        if ($is_empty) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        return GlobalFunction::response_function(Status::WAREHOUSE_DISPLAY, $warehouse);
    }

    public function show($id)
    {
        $warehouse = Warehouse::where("id", $id)->get();

        if ($warehouse->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }
        return GlobalFunction::response_function(Status::WAREHOUSE_DISPLAY, $warehouse->first());
    }

    public function store(StoreRequest $request)
    {
        $warehouse = Warehouse::create([
            "code" => $request["code"],
            "name" => $request["name"],
        ]);
        return GlobalFunction::save(Status::WAREHOUSE_SAVE, $warehouse);
    }

    public function update(StoreRequest $request, $id)
    {
        $warehouse = Warehouse::find($id);

        $not_found = Warehouse::where("id", $id)->get();

        if ($not_found->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }
        $warehouse->update([
            "code" => $request["code"],
            "name" => $request["name"],
        ]);

        return GlobalFunction::response_function(Status::WAREHOUSE_UPDATE, $warehouse);
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::where("id", $id)
            ->withTrashed()
            ->get();

        if ($warehouse->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $warehouse = Warehouse::withTrashed()->find($id);
        $is_active = Warehouse::withTrashed()
            ->where("id", $id)
            ->first();
        if (!$is_active) {
            return $is_active;
        } elseif (!$is_active->deleted_at) {
            $warehouse->delete();
            $message = Status::ARCHIVE_STATUS;
        } else {
            $warehouse->restore();
            $message = Status::RESTORE_STATUS;
        }
        return GlobalFunction::response_function($message, $warehouse);
    }

    public function code_validate(CodeRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }
}
