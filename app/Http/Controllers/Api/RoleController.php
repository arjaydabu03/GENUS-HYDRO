<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Http\Resources\RoleResource;

use App\Response\Status;
use App\Functions\GlobalFunction;

use App\Models\Role;

use App\Http\Requests\Role\StoreRequest;
use App\Http\Requests\Role\DisplayRequest;
use App\Http\Requests\Role\Validation\NameRequest;

class RoleController extends Controller
{
    public function index(DisplayRequest $request)
    {
        $status = $request->status;
        $search = $request->search;
        $paginate = isset($request->paginate) ? $request->paginate : 1;

        $role = Role::when($status === "inactive", function ($query) {
            $query->onlyTrashed();
        })->when($search, function ($query) use ($search) {
            $query
                ->where("name", "like", "%" . $search . "%")
                ->orWhere("access_permission", "like", "%" . $search . "%");
        });

        $role = $paginate
            ? $role->orderByDesc("updated_at")->paginate($request->rows)
            : $role->orderByDesc("updated_at")->get();

        $is_empty = $role->isEmpty();

        if ($is_empty) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        RoleResource::collection($role);

        return GlobalFunction::response_function(Status::ROLE_DISPLAY, $role);
    }

    public function show($id)
    {
        $role = Role::where("id", $id)->get();

        if ($role->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }
        return GlobalFunction::response_function(Status::ROLE_DISPLAY, $role->first());
    }

    public function store(StoreRequest $request)
    {
        $access_permission = $request->access_permission;
        $accessConvertedToString = implode(", ", $access_permission);

        $role = Role::create([
            "name" => $request->name,
            "access_permission" => $accessConvertedToString,
        ]);
        return GlobalFunction::save(Status::ROLE_SAVE, $role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        $access_permission = $request->access_permission;
        $accessConvertedToString = implode(", ", $access_permission);

        $not_found = Role::where("id", $id)->get();

        if ($not_found->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }
        $role->update([
            "name" => $request["name"],
            "access_permission" => $accessConvertedToString,
        ]);

        return GlobalFunction::response_function(Status::ROLE_UPDATE, $role);
    }

    public function destroy($id)
    {
        $role = Role::where("id", $id)
            ->withTrashed()
            ->get();

        if ($role->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $role = Role::withTrashed()->find($id);
        $is_active = Role::withTrashed()
            ->where("id", $id)
            ->first();
        if (!$is_active) {
            return $is_active;
        } elseif (!$is_active->deleted_at) {
            $role->delete();
            $message = Status::ARCHIVE_STATUS;
        } else {
            $role->restore();
            $message = Status::RESTORE_STATUS;
        }
        return GlobalFunction::response_function($message, $role);
    }

    public function validate_name(NameRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }
}
