<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UOM;
use Illuminate\Validation\ValidationException;

use App\Response\Status;
use App\Functions\GlobalFunction;
use App\Http\Requests\UOM\StoreRequest;
use App\Http\Requests\UOM\Validation\DisplayRequest;
use App\Http\Requests\UOM\Validation\CodeRequest;

class UOMController extends Controller
{
    public function index(DisplayRequest $request)
    {
        $status = $request->status;
        $search = $request->search;
        $paginate = isset($request->paginate) ? $request->paginate : 1;

        $uom = UOM::when($status === "inactive", function ($query) {
            $query->onlyTrashed();
        })->when($search, function ($query) use ($search) {
            $query
                ->where("code", "like", "%" . $search . "%")
                ->orWhere("description", "like", "%" . $search . "%");
        });

        $uom = $paginate
            ? $uom->orderByDesc("updated_at")->paginate($request->rows)
            : $uom->orderByDesc("updated_at")->get();

        $is_empty = $uom->isEmpty();

        if ($is_empty) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        return GlobalFunction::response_function(Status::UOM_DISPLAY, $uom);
    }

    public function show($id)
    {
        $uom = UOM::where("id", $id)->get();

        if ($uom->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }
        return GlobalFunction::response_function(Status::UOM_DISPLAY, $uom->first());
    }

    public function store(StoreRequest $request)
    {
        $uom = UOM::create([
            "code" => $request["code"],
            "description" => $request["description"],
        ]);
        return GlobalFunction::save(Status::UOM_SAVE, $uom);
    }

    public function update(StoreRequest $request, $id)
    {
        $uom = UOM::find($id);

        $not_found = UOM::where("id", $id)->get();

        if ($not_found->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }
        $uom->update([
            "code" => $request["code"],
            "description" => $request["description"],
        ]);

        return GlobalFunction::response_function(Status::UOM_UPDATE, $uom);
    }

    public function destroy($id)
    {
        $uom = UOM::where("id", $id)
            ->withTrashed()
            ->get();

        if ($uom->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $uom = UOM::withTrashed()->find($id);
        $is_active = UOM::withTrashed()
            ->where("id", $id)
            ->first();
        if (!$is_active) {
            return $is_active;
        } elseif (!$is_active->deleted_at) {
            $uom->delete();
            $message = Status::ARCHIVE_STATUS;
        } else {
            $uom->restore();
            $message = Status::RESTORE_STATUS;
        }
        return GlobalFunction::response_function($message, $uom);
    }
    public function code_validate(CodeRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }
}
