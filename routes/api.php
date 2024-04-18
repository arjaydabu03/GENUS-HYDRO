<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\UOMController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ApproverController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\CutoffController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\HriController;
use App\Http\Controllers\Api\KeywordController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Route::get('genus_location', [UserController::class, 'get_genus_location']);

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::post("logout", [UserController::class, "logout"]);
    Route::patch("user/{id}", [UserController::class, "destroy"]);
    Route::post("code_validate", [UserController::class, "code_validate"]);
    Route::post("validate_username", [UserController::class, "validate_username"]);
    Route::post("validate_mobile", [UserController::class, "validate_mobile"]);
    Route::post("validate_name", [UserController::class, "validate_name"]);
    Route::put("user/reset/{id}", [UserController::class, "reset_password"]);
    Route::get("customer", [UserController::class, "customer"]);

    Route::put("user/old_password/{id}", [UserController::class, "old_password"]);
    Route::put("user/change_password/", [UserController::class, "change_password"]);
    Route::put("user/{id}", [UserController::class, "update"]);
    Route::apiResource("user", UserController::class);
    Route::get("transaction/notification", [OrderController::class, "count"]);

    Route::patch("archive/{id}", [StoreController::class, "destroy"]);
    Route::apiResource("user_store", StoreController::class);
    Route::post("code_validate_store", [StoreController::class, "code_validate"]);
    Route::post("validate_mobile_store", [StoreController::class, "validate_mobile"]);

    Route::patch("category/{id}", [CategoryController::class, "destroy"]);
    Route::apiResource("category", CategoryController::class);

    Route::patch("material/{id}", [MaterialController::class, "destroy"]);
    Route::apiResource("material", MaterialController::class);
    Route::post("validate_code", [MaterialController::class, "validate_code"]);
    Route::post("import", [MaterialController::class, "import_material"]);

    Route::apiResource("order", OrderController::class);
    Route::patch("order/cancel/{id}", [OrderController::class, "cancelOrder"]);
    Route::patch("transaction/cancel/{id}", [OrderController::class, "cancelTransaction"]);
    Route::get("transaction/notification", [OrderController::class, "count"]);
    Route::get("elixir_order", [OrderController::class, "elixir_order"]);

    Route::apiResource("approval", ApproverController::class);
    Route::patch("order/restore/{id}", [ApproverController::class, "restore"]);
    Route::put("order/approve/{id}", [ApproverController::class, "update"]);
    Route::get("approver_count", [ApproverController::class, "approver_count"]);

    Route::post("uom/validate", [UOMController::class, "code_validate"]);
    Route::patch("uom/{id}", [UOMController::class, "destroy"]);
    Route::apiResource("uom", UOMController::class);

    Route::post("warehouse/validate", [WarehouseController::class, "code_validate"]);
    Route::patch("warehouse/{id}", [WarehouseController::class, "destroy"]);
    Route::apiResource("warehouse", WarehouseController::class);

    Route::post("role/validate", [RoleController::class, "validate_name"]);
    Route::patch("role/{id}", [RoleController::class, "destroy"]);
    Route::apiResource("role", RoleController::class);

    Route::get("report", [ReportController::class, "view"]);
    Route::get("count", [ReportController::class, "count"]);
    Route::get("request_count", [ReportController::class, "requestor_count"]);
    Route::get("export", [ReportController::class, "export"]);
    Route::patch("serve/{id}", [ReportController::class, "serve"]);

    Route::patch("cut_off/{id}", [CutoffController::class, "destroy"]);
    Route::apiResource("cut_off", CutoffController::class);

    Route::apiResource("company", CompanyController::class);
    Route::apiResource("department", DepartmentController::class);
    Route::apiResource("location", LocationController::class);
    Route::apiResource("hri", HriController::class);
    Route::post("import_hri", [HriController::class, "import_hri"]);
    Route::post("hri/validate", [HriController::class, "validate_hri_code"]);
    Route::patch("hri_archive/{id}", [HriController::class, "destroy"]);

    Route::apiResource("keyword", KeywordController::class);
    Route::post("keyword_validate", [KeywordController::class, "validate_keyword_code"]);
    Route::patch("keyword_archive/{id}", [KeywordController::class, "destroy"]);
});

Route::post("login", [UserController::class, "login"]);
Route::post("sms_order_falcon", [OrderController::class, "sms_order_falcon"]);
Route::post("sms_order_hri", [OrderController::class, "sms_order_hri"]);
Route::post("sms_cancel", [OrderController::class, "sms_cancel"]);
// Route::get("reports", [ReportController::class, "view"]);
// Route::get("users", [UserController::class, "index"]);
