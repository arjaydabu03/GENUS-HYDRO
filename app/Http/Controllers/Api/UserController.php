<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Response\Status;
use App\Functions\GlobalFunction;

use App\Models\User;
use App\Models\Store;
use App\Models\TagAccount;
use App\Models\TagAccountOrder;
use App\Models\Location;
use App\Models\Role;
use App\Models\Cutoff;

use App\Http\Resources\UserResource;
use App\Http\Resources\LoginResource;

use App\Http\Requests\User\Validation\UsernameRequest;
use App\Http\Requests\User\Validation\CodeRequest;
use App\Http\Requests\User\Validation\MobileRequest;
use App\Http\Requests\User\UserRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\ChangeRequest;
use App\Http\Requests\User\DisplayRequest;
use App\Http\Requests\User\Validation\NameRequest;
use App\Http\Requests\User\Validation\PasswordRequest;

class UserController extends Controller
{
    public function index(DisplayRequest $request)
    {
        $search = $request->search;
        $status = $request->status;
        $rows = $request->rows;
        $paginate = isset($request->paginate) ? $request->paginate : 1;

        $users = User::with("scope_approval", "scope_order", "role")
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
                    ->orWhere("mobile_no", "like", "%" . $search . "%")
                    ->orWhere("username", "like", "%" . $search . "%")
                    ->orWhere("role_id", "like", "%" . $search . "%");
            });
        $users = $paginate
            ? $users->orderByDesc("updated_at")->paginate($rows)
            : $users->orderByDesc("updated_at")->get();

        $is_empty = $users->isEmpty();
        if ($is_empty) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        UserResource::collection($users);
        return GlobalFunction::response_function(Status::USER_DISPLAY, $users);
    }

    public function customer(Request $request)
    {
        $store = Store::with("scope_order")
            ->get()
            ->each(function ($item) {
                $item->account_type = "sms";

                return $item;
            });
        $user = User::with("scope_order")
            ->get()
            ->each(function ($item) {
                $item->account_type = "online";

                return $item;
            });

        $customer = [...$user, ...$store]; //array merge php 8

        $account = collect($customer)
            ->unique("account_code")
            ->values();
        return GlobalFunction::response_function(Status::USER_DISPLAY, $account);
    }

    public function show($id)
    {
        $not_found = User::where("id", $id)->get();
        //  return $not_found;
        if ($not_found->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }
        $users = User::where("id", $id)
            ->with("scope_approval", "scope_order")
            ->get();
        $user_collection = UserResource::collection($users)->first();

        return GlobalFunction::response_function(Status::USER_DISPLAY, $user_collection);
    }

    public function store(UserRequest $request)
    {
        $user = new User([
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

            "role_id" => $request["role_id"],
            "mobile_no" => $request["mobile_no"],
            "username" => $request["username"],
            "password" => Hash::make($request["username"]),
        ]);
        $user->save();

        $scope_order = $request["scope_order"];

        foreach ($scope_order as $key => $value) {
            TagAccountOrder::create([
                "account_id" => $user->id,
                "location_id" => $scope_order[$key]["id"],
                "location_code" => $scope_order[$key]["code"],
            ]);
        }

        $scope_approval = $request["scope_approval"];

        foreach ($scope_approval as $key => $value) {
            TagAccount::create([
                "account_id" => $user->id,
                "location_id" => $scope_approval[$key]["id"],
                "location_code" => $scope_approval[$key]["code"],
            ]);
        }

        $user = new UserResource($user);

        return GlobalFunction::save(Status::REGISTERED, $user);
    }

    public function login(LoginRequest $request)
    {
        $cut_off = Cutoff::get();

        $user = User::where("username", $request->username)
            ->with("scope_approval", "scope_order")
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                "username" => ["The provided credentials are incorrect."],
                "password" => ["The provided credentials are incorrect."],
            ]);

            if ($user || Hash::check($request->password, $user->username)) {
                return GlobalFunction::login_user(Status::INVALID_ACTION);
            }
        }
        $token = $user->createToken("PersonalAccessToken")->plainTextToken;
        $user["token"] = $token;
        $user["cut_off"] = $cut_off;
        $user = new LoginResource($user);

        $cookie = cookie("hydro", $token);

        return GlobalFunction::response_function(Status::LOGIN_USER, $user)->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        // auth()->user()->tokens()->delete();//all token of one user
        auth()
            ->user()
            ->currentAccessToken()
            ->delete(); //current user
        return GlobalFunction::response_function(Status::LOGOUT_USER);
    }

    public function destroy(Request $request, $id)
    {
        $invalid_id = User::where("id", $id)
            ->withTrashed()
            ->get();

        if ($invalid_id->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $user_id = Auth()->user()->id;
        $not_allowed = User::where("id", $id)
            ->where("id", $user_id)
            ->exists();

        if ($not_allowed) {
            return GlobalFunction::invalid(Status::INVALID_ACTION);
        }
        $user = User::withTrashed()->find($id);
        $is_active = User::withTrashed()
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
        $user = new UserResource($user);
        return GlobalFunction::response_function($message, $user);
    }

    public function update(UserRequest $request, $id)
    {
        $user = User::find($id);

        $scope_approval = $request->scope_approval;
        $scope_order = $request->scope_order;

        $not_found = User::where("id", $id)->get();
        if ($not_found->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        // SCOPE FOR APPROVAL
        $newTaggedApproval = collect($scope_approval)
            ->pluck("id")
            ->toArray();
        $currentTaggedApproval = TagAccount::where("account_id", $id)
            ->get()
            ->pluck("location_id")
            ->toArray();

        foreach ($currentTaggedApproval as $location_id) {
            if (!in_array($location_id, $newTaggedApproval)) {
                TagAccount::where("account_id", $id)
                    ->where("location_id", $location_id)
                    ->delete();
            }
        }
        foreach ($scope_approval as $index => $value) {
            if (!in_array($value["id"], $currentTaggedApproval)) {
                TagAccount::create([
                    "account_id" => $id,
                    "location_id" => $value["id"],
                    "location_code" => $value["code"],
                ]);
            }
        }

        // SCOPE FOR ORDERING
        $newTaggedOrder = collect($scope_order)
            ->pluck("id")
            ->toArray();
        $currentTaggedOrder = TagAccountOrder::where("account_id", $id)
            ->get()
            ->pluck("location_id")
            ->toArray();

        foreach ($currentTaggedOrder as $location_id) {
            if (!in_array($location_id, $newTaggedOrder)) {
                TagAccountOrder::where("account_id", $id)
                    ->where("location_id", $location_id)
                    ->delete();
            }
        }
        foreach ($scope_order as $index => $value) {
            if (!in_array($value["id"], $currentTaggedOrder)) {
                TagAccountOrder::create([
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

        $user = new UserResource($user);

        return GlobalFunction::response_function(Status::USER_UPDATE, $user);
    }

    public function reset_password(Request $request, $id)
    {
        $user = User::find($id);

        $new_password = Hash::make($user->username);

        $user->update([
            "password" => $new_password,
        ]);

        return GlobalFunction::response_function(Status::CHANGE_PASSWORD);
    }

    public function change_password(ChangeRequest $request)
    {
        $id = Auth::id();
        $user = User::find($id);

        if ($user->username == $request->password) {
            throw ValidationException::withMessages([
                "password" => ["Please change your password."],
            ]);
        }
        $user->update([
            "password" => Hash::make($request["password"]),
        ]);
        return GlobalFunction::response_function(Status::CHANGE_PASSWORD);
    }

    public function old_password(PasswordRequest $request)
    {
        $id = Auth::id();
        $user = User::find($id);
        //pwedi yang and &&
        if (!Hash::check($request->password, $user->password)) {
            return GlobalFunction::invalid(Status::INVALID_RESPONSE);
        }
    }

    public function validate_username(UsernameRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }

    public function code_validate(CodeRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }

    public function validate_mobile(MobileRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }

    public function validate_name(NameRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }
}
