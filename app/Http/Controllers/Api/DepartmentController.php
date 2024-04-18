<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Response\Status;
use App\Functions\GlobalFunction;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status;
        $search = $request->search;
        $paginate = isset($request->paginate) ? $request->paginate : 1;

        $department = Department::when($status === "inactive", function ($query) {
            $query->onlyTrashed();
        })
            ->when($search, function ($query) use ($search) {
                $query
                    ->where("code", "like", "%" . $search . "%")
                    ->orWhere("company_id", "like", "%" . $search . "%")
                    ->orWhere("name", "like", "%" . $search . "%");
            })
            ->when($request->company_id, function ($query) use ($request) {
                return $query->whereHas("company", function ($query) use ($request) {
                    return $query->where("sync_id", $request->company_id);
                });
            });

        $department = $paginate
            ? $department
                ->with("company")
                ->orderByDesc("updated_at")
                ->paginate($request->rows)
            : $department->orderByDesc("updated_at")->get();

        $is_empty = $department->isEmpty();

        if ($is_empty) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        return GlobalFunction::response_function(Status::DEPARTMENT_DISPLAY, $department);
    }
    public function store(Request $request)
    {
        $sync = $request->all();

        $department = Department::upsert(
            $sync,
            ["sync_id"],
            ["code", "name", "company_id", "deleted_at"]
        );

        return GlobalFunction::save(Status::DEPARTMENT_IMPORT, $request->toArray());
    }
}
