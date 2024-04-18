<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Response\Status;
use App\Functions\GlobalFunction;
use App\Models\Location;
use App\Models\Location_Department;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status;
        $search = $request->search;
        $paginate = isset($request->paginate) ? $request->paginate : 1;
        $location = Location::when($status === "inactive", function ($query) {
            $query->onlyTrashed();
        })
            ->when($search, function ($query) use ($search) {
                $query
                    ->where("sync_id", "like", "%" . $search . "%")
                    ->orWhere("code", "like", "%" . $search . "%")
                    ->orWhere("name", "like", "%" . $search . "%");
            })
            ->when($request->department_id, function ($query) use ($request) {
                return $query->whereHas("departments", function ($query) use ($request) {
                    return $query->where("sync_id", $request->department_id);
                });
            });
        $location = $paginate
            ? $location
                ->with("departments")
                ->orderByDesc("updated_at")
                ->paginate($request->rows)
            : $location->orderByDesc("updated_at")->get();

        $is_empty = $location->isEmpty();

        if ($is_empty) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        return GlobalFunction::response_function(Status::LOCATION_DISPLAY, $location);
    }
    public function store(Request $request)
    {
        $sync_all = $request->all();

        foreach ($sync_all as $location) {
            $sync_id = $location["sync_id"];
            $code = $location["code"];
            $name = $location["name"];
            $deleted_at = $location["deleted_at"];

            $locations = Location::withTrashed()->updateOrCreate(
                [
                    "sync_id" => $sync_id,
                ],
                [
                    "sync_id" => $sync_id,
                    "code" => $code,
                    "name" => $name,
                    "deleted_at" => $deleted_at,
                ]
            );

            $locations->departments()->sync($location["departments"]);
        }

        return GlobalFunction::save(Status::LOCATION_IMPORT, $request->toArray());
    }
}
