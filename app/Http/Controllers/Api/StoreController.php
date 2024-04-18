<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Response\Status;
use App\Functions\GlobalFunction;

use App\Models\Store;
use App\Models\TagStoreLocation;

use App\Http\Resources\StoreResource;
use App\Http\Resources\TagAccountResource;
use App\Http\Requests\StoreAccount\UpdateRequest;
use App\Http\Requests\User\DisplayRequest;
use App\Http\Requests\StoreAccount\StoreRequest;
use App\Http\Requests\StoreAccount\Validation\CodeRequest;
use App\Http\Requests\StoreAccount\Validation\MobileRequest;

class StoreController extends Controller
{
    public function index(DisplayRequest $request)
    {
        $rows = $request->rows;
        $status = $request->status;
        $search = $request->search;

        $user_store = Store::with("scope_order")
            ->when($status === "inactive", function ($query) {
                $query->onlyTrashed();
            })
            ->where(function ($query) use ($search) {
                $query
                    ->where("account_code", "like", "%" . $search . "%")
                    ->orWhere("account_name", "like", "%" . $search . "%")
                    ->orWhere("company_code", "like", "%" . $search . "%")
                    ->orWhere("company", "like", "%" . $search . "%")
                    ->orWhere("department_code", "like", "%" . $search . "%")
                    ->orWhere("department", "like", "%" . $search . "%")
                    ->orWhere("location_code", "like", "%" . $search . "%")
                    ->orWhere("location", "like", "%" . $search . "%")
                    ->orWhere("mobile_no", "like", "%" . $search . "%");
            })
            ->orderByDesc("updated_at")
            ->paginate($rows);

        if ($user_store->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        StoreResource::collection($user_store);

        return GlobalFunction::response_function(Status::STORE_DISPLAY, $user_store);
    }

    public function store(StoreRequest $request)
    {
        $user_store = new Store([
            "account_code" => $request["code"],
            "account_name" => $request["name"],

            "location_id" => $request["location"]["id"],
            "location_code" => $request["location"]["code"],
            "location" => $request["location"]["name"],

            "department_id" => $request["department"]["id"],
            "department_code" => $request["department"]["code"],
            "department" => $request["department"]["name"],

            "company_id" => $request["company"]["id"],
            "company_code" => $request["company"]["code"],
            "company" => $request["company"]["name"],
            "mobile_no" => $request["mobile_no"],
        ]);
        $user_store->save();

        $store_order = $request["scope_order"];

        foreach ($store_order as $key => $value) {
            TagStoreLocation::create([
                "account_id" => $user_store->id,
                "location_id" => $store_order[$key]["id"],
                "location_code" => $store_order[$key]["code"],
            ]);
        }

        $store_collect = new StoreResource($user_store);

        return GlobalFunction::save(Status::STORE_REGISTERED, $store_collect);
    }

    public function update(UpdateRequest $request, $id)
    {
        $user = Store::find($id);

        $tag_store = $request->scope_order;

        $not_found = Store::where("id", $id)->get();
        if ($not_found->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        // SCOPE FOR ORDERING
        $newTaggedOrder = collect($tag_store)
            ->pluck("id")
            ->toArray();
        $currentTaggedOrder = TagStoreLocation::where("account_id", $id)
            ->get()
            ->pluck("location_id")
            ->toArray();

        foreach ($currentTaggedOrder as $location_id) {
            if (!in_array($location_id, $newTaggedOrder)) {
                TagStoreLocation::where("account_id", $id)
                    ->where("location_id", $location_id)
                    ->delete();
            }
        }
        foreach ($tag_store as $index => $value) {
            if (!in_array($value["id"], $currentTaggedOrder)) {
                TagStoreLocation::create([
                    "account_id" => $id,
                    "location_id" => $value["id"],
                    "location_code" => $value["code"],
                ]);
            }
        }

        $user->update([
            "account_code" => $request["code"],
            "account_name" => $request["name"],
            "mobile_no" => $request["mobile_no"],
            "username" => $request["username"],
            "role_id" => $request["role_id"],

            "location_id" => $request["location"]["id"],
            "location_code" => $request["location"]["code"],
            "location" => $request["location"]["name"],

            "department_id" => $request["department"]["id"],
            "department_code" => $request["department"]["code"],
            "department" => $request["department"]["name"],

            "company_id" => $request["company"]["id"],
            "company_code" => $request["company"]["code"],
            "company" => $request["company"]["name"],
        ]);

        $user = new StoreResource($user);

        return GlobalFunction::response_function(Status::USER_UPDATE, $user);
    }

    public function destroy(Request $request, $id)
    {
        $invalid_id = Store::where("id", $id)
            ->withTrashed()
            ->get();

        if ($invalid_id->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $user = Store::withTrashed()->find($id);
        $is_active = Store::withTrashed()
            ->where("id", $id)
            ->first();
        if (!$is_active) {
            return $is_active;
        } elseif (!$is_active->deleted_at) {
            $user->delete();
            $message = Status::ARCHIVE_STATUS;
        } else {
            $user->restore();
            $message = Status::RESTORE_STATUS;
        }
        $user = new StoreResource($user);
        return GlobalFunction::response_function($message, $user);
    }
    public function code_validate(CodeRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }

    public function validate_mobile(MobileRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }
}
