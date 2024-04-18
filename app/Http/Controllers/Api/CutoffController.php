<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Response\Status;
use App\Functions\GlobalFunction;

use App\Models\Cutoff;

use App\Http\Requests\Cutoff\StoreRequest;
use App\Http\Requests\Cutoff\DisplayRequest;

class CutoffController extends Controller
{
    public function index(DisplayRequest $request)
    {
        $status = $request->status;
        $search = $request->search;
        $paginate = isset($request->paginate) ? $request->paginate : 1;

        $cut_off = Cutoff::when($status === "inactive", function ($query) {
            $query->onlyTrashed();
        })->when($search, function ($query) use ($search) {
            $query
                ->where("code", "like", "%" . $search . "%")
                ->orWhere("name", "like", "%" . $search . "%");
        });

        $cut_off = $paginate
            ? $cut_off->orderByDesc("updated_at")->paginate($request->rows)
            : $cut_off->orderByDesc("updated_at")->get();

        $is_empty = $cut_off->isEmpty();

        if ($is_empty) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        return GlobalFunction::response_function(Status::CUT_OFF_DISPLAY, $cut_off);
    }

    public function show($id)
    {
        return Cutoff::where("id", $id)->get();
    }
    public function store(StoreRequest $request)
    {
        $cut_off = Cutoff::create([
            "name" => $request["name"],
            "time" => $request["time"],
        ]);
        return GlobalFunction::save(Status::CUTOFF_SAVE, $cut_off);
    }

    public function update(StoreRequest $request, $id)
    {
        $cut_off = Cutoff::find($id);

        $not_found = Cutoff::where("id", $id)->get();

        if ($not_found->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $cut_off->update([
            "name" => $request["name"],
            "time" => $request["time"],
        ]);
        return GlobalFunction::response_function(Status::CUTOFF_SAVE, $cut_off);
    }
    public function destroy($id)
    {
        $cut_off = Cutoff::where("id", $id)
            ->withTrashed()
            ->get();

        if ($cut_off->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $cut_off = Cutoff::withTrashed()->find($id);
        $is_active = Cutoff::withTrashed()
            ->where("id", $id)
            ->first();
        if (!$is_active) {
            return $is_active;
        } elseif (!$is_active->deleted_at) {
            $cut_off->delete();
            $message = Status::ARCHIVE_STATUS;
        } else {
            $cut_off->restore();
            $message = Status::RESTORE_STATUS;
        }
        return GlobalFunction::response_function($message, $cut_off);
    }
}
