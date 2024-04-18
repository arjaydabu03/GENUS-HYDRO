<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\HRI;
use App\Response\Status;
use App\Functions\GlobalFunction;

use App\Http\Requests\HRI\DisplayRequest;
use App\Http\Requests\HRI\StoreRequest;
use App\Http\Requests\HRI\CodeRequest;
use App\Http\Requests\HRI\ImportRequest;

class HriController extends Controller
{
    public function index(DisplayRequest $request)
    {
        $status = $request->status;
        $search = $request->search;
        $paginate = isset($request->paginate) ? $request->paginate : 1;

        $hri = HRI::when($status === "inactive", function ($query) {
            $query->onlyTrashed();
        })->when($search, function ($query) use ($search) {
            $query
                ->where("code", "like", "%" . $search . "%")
                ->orWhere("name", "like", "%" . $search . "%");
        });

        $hri = $paginate
            ? $hri->orderByDesc("updated_at")->paginate($request->rows)
            : $hri->orderByDesc("updated_at")->get();

        $is_empty = $hri->isEmpty();

        if ($is_empty) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        return GlobalFunction::response_function(Status::HRI_DISPLAY, $hri);
    }

    public function store(StoreRequest $request)
    {
        $hri = HRI::create([
            "code" => $request["code"],
            "name" => $request["name"],
        ]);
        return GlobalFunction::save(Status::HRI_SAVE, $hri);
    }
    public function import_hri(ImportRequest $request)
    {
        $import = $request->all();

        $import = HRI::upsert($import, ["id"], ["code"], ["name"]);

        return GlobalFunction::save(Status::HRI_IMPORT, $request->toArray());
    }
    public function update(StoreRequest $request, $id)
    {
        $hri = HRI::find($id);

        $not_found = HRI::where("id", $id)->get();

        if ($not_found->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $hri->update([
            "code" => $request["code"],
            "name" => $request["name"],
        ]);
        return GlobalFunction::response_function(Status::HRI_UPDATE, $hri);
    }
    public function destroy($id)
    {
        $hri = HRI::where("id", $id)
            ->withTrashed()
            ->get();

        if ($hri->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $hri = HRI::withTrashed()->find($id);
        $is_active = HRI::withTrashed()
            ->where("id", $id)
            ->first();
        if (!$is_active) {
            return $is_active;
        } elseif (!$is_active->deleted_at) {
            $hri->delete();
            $message = Status::ARCHIVE_STATUS;
        } else {
            $hri->restore();
            $message = Status::RESTORE_STATUS;
        }
        return GlobalFunction::response_function($message, $hri);
    }
    public function validate_hri_code(CodeRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }
}
