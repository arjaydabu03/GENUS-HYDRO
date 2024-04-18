<?php

namespace App\Functions;

use App\Response\Status;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\carbon;

use App\Models\User;
use App\Models\Material;
use App\Models\Transaction;
use App\Models\UOM;
use App\Models\Order;
use App\Models\Cutoff;
use App\Models\HRI;
use App\Models\TagStoreLocation;

use App\Functions\SendSMS;

class SmsFunctionHri
{
    #-----------------------------------------------------------------
    #----------------------------MAIN VALIDATIONS---------------------
    #-----------------------------------------------------------------

    public static function validate_header($header, $requestor_no)
    {
        $type = "header";
        $error = collect();

        $unregistered_number = SmsFunctionHri::unregistered_number_permission(
            $header,
            $requestor_no
        );
        if ($unregistered_number) {
            return $unregistered_number;
        }

        $cut_off = SmsFunctionHri::cut_off($header, $requestor_no);
        if ($cut_off) {
            return $cut_off;
        }

        $missing_hashtag = SmsFunctionHri::missing_hashtag($header, $requestor_no);
        if ($missing_hashtag) {
            return $missing_hashtag;
        }

        $multiple_hashtag = SmsFunctionHri::multiple_hashtag($header, $requestor_no);
        if ($multiple_hashtag) {
            return $multiple_hashtag;
        }

        $missing_dash = SmsFunctionHri::missing_dash($header, $type, $requestor_no);
        if ($missing_dash) {
            return $missing_dash;
        }

        $multiple_dash = SmsFunctionHri::multiple_dash($header, $type, $requestor_no);
        if ($multiple_dash) {
            return $multiple_dash;
        }

        $check_alphanumerics = SmsFunctionHri::check_alphanumerics($header, $type, $requestor_no);
        if ($check_alphanumerics) {
            return $check_alphanumerics;
        }

        $missing_code = SmsFunctionHri::missing_code($header, $type, $requestor_no);
        if ($missing_code) {
            return $missing_code;
        }

        $missing_order_no = SmsFunctionHri::missing_order_no($header, $requestor_no);
        if ($missing_order_no) {
            return $missing_order_no;
        }

        $missing_year = SmsFunctionHri::missing_year($header, $requestor_no);
        if ($missing_year) {
            return $missing_year;
        }

        $multiple_year = SmsFunctionHri::multiple_year($header, $requestor_no);
        if ($multiple_year) {
            return $multiple_year;
        }
        $missing_month = SmsFunctionHri::missing_month($header, $requestor_no);
        if ($missing_month) {
            return $missing_month;
        }
        $multiple_month = SmsFunctionHri::multiple_month($header, $requestor_no);
        if ($multiple_month) {
            return $multiple_month;
        }
        $over_count_month = SmsFunctionHri::over_count_month($header, $requestor_no);
        if ($over_count_month) {
            return $over_count_month;
        }
        $missing_day = SmsFunctionHri::missing_day($header, $requestor_no);
        if ($missing_day) {
            return $missing_day;
        }
        $missing_hri = SmsFunctionHri::missing_hri($header, $requestor_no);
        if ($missing_hri) {
            return $missing_hri;
        }
        $multiple_day = SmsFunctionHri::multiple_day($header, $requestor_no);
        if ($multiple_day) {
            return $multiple_day;
        }
        $over_count_day = SmsFunctionHri::over_count_day($header, $requestor_no);
        if ($over_count_day) {
            return $over_count_day;
        }
        $invalid_date = SmsFunctionHri::invalid_date($header, $requestor_no);
        if ($invalid_date) {
            return $invalid_date;
        }
        $store_not_exist = SmsFunctionHri::store_not_exist($header, $requestor_no);
        if ($store_not_exist) {
            return $store_not_exist;
        }
        $hri_not_exist = SmsFunctionHri::hri_not_exist($header, $requestor_no);
        if ($hri_not_exist) {
            return $hri_not_exist;
        }

        return $error
            ->filter()
            ->values()
            ->toArray();
    }

    public static function validate_body($header, $data, $requestor_no, $keyword)
    {
        $type = "body";

        $error = collect();
        $error->push(
            $missing_dash_lines = SmsFunctionHri::missing_dash($data, $type, $requestor_no)
        );
        $error->push(
            $multiple_dash_lines = SmsFunctionHri::multiple_dash($data, $type, $requestor_no)
        );
        $error->push(
            $alphanumberic_lines = SmsFunctionHri::check_alphanumerics($data, $type, $requestor_no)
        );
        $error->push(
            $missing_item_code_lines = SmsFunctionHri::missing_code($data, $type, $requestor_no)
        );
        $error->push(
            $unregistered_item_code_lines = SmsFunctionHri::unregistered_item_code(
                $data,
                $requestor_no
            )
        );
        $error->push($missing_qty = SmsFunctionHri::missing_qty($data, $requestor_no));
        $error->push(
            $duplicate_lines = SmsFunctionHri::duplicate($header, $data, $requestor_no, $keyword)
        );

        $error->push(
            $is_multiple_products = SmsFunctionHri::is_multiple_products($data, $requestor_no)
        );

        if (
            count(
                $error
                    ->filter()
                    ->values()
                    ->toArray()
            ) > 0
        ) {
            return SmsFunctionHri::compose_order_error(
                $requestor_no,
                $missing_dash_lines,
                $multiple_dash_lines,
                $alphanumberic_lines,
                $missing_item_code_lines,
                $unregistered_item_code_lines,
                $missing_qty,
                $duplicate_lines,
                $is_multiple_products
            );
        }
    }

    public static function validate_body_delete($header, $data, $requestor_no)
    {
        $type = "body";

        $error = collect();
        $error->push(
            $missing_dash_lines = SmsFunctionHri::missing_dash($data, $type, $requestor_no)
        );
        $error->push(
            $multiple_dash_lines = SmsFunctionHri::multiple_dash($data, $type, $requestor_no)
        );
        $error->push(
            $alphanumberic_lines = SmsFunctionHri::check_alphanumerics($data, $type, $requestor_no)
        );
        $error->push(
            $missing_item_code_lines = SmsFunctionHri::missing_code($data, $type, $requestor_no)
        );
        $error->push(
            $unregistered_item_code_lines = SmsFunctionHri::unregistered_item_code(
                $data,
                $requestor_no
            )
        );
        $error->push($missing_qty = SmsFunctionHri::missing_qty($data, $requestor_no));
        $error->push(
            $is_multiple_products = SmsFunctionHri::is_multiple_products($data, $requestor_no)
        );

        if (
            count(
                $error
                    ->filter()
                    ->values()
                    ->toArray()
            ) > 0
        ) {
            return SmsFunctionHri::compose_order_error(
                $requestor_no,
                $missing_dash_lines,
                $multiple_dash_lines,
                $alphanumberic_lines,
                $missing_item_code_lines,
                $unregistered_item_code_lines,
                $missing_qty,
                $is_multiple_products
            );
        }
    }

    public static function compose_order_error(
        $requestor_no,
        $missing_dash_lines,
        $multiple_dash_lines,
        $alphanumberic_lines,
        $missing_item_code_lines,
        $unregistered_item_code_lines,
        $missing_qty,
        $duplicate_lines = [],
        $is_multiple_products = []
    ) {
        $type =
            "Missing dash : " .
            implode(",", $missing_dash_lines) .
            "\n" .
            "Multiple dash : " .
            implode(",", $multiple_dash_lines) .
            "\n" .
            "Missing Item code : " .
            implode(",", $missing_item_code_lines) .
            "\n" .
            "Unregistered Item code : " .
            implode(",", $unregistered_item_code_lines) .
            "\n" .
            "Missing Qty : " .
            implode(",", $missing_qty) .
            "\n" .
            "Qty not number : " .
            implode(",", $alphanumberic_lines) .
            "\n" .
            "Duplicate Orders : " .
            implode(",", $duplicate_lines) .
            "\n" .
            "Multiple Item code : " .
            implode(",", $is_multiple_products) .
            "\n";

        return $type;
    }

    #-----------------------------------------------------------------
    #----------------------------MINOR VALIDATIONS--------------------
    #-----------------------------------------------------------------

    public static function missing_hashtag($header, $requestor_no)
    {
        $with_hashtag = substr_count($header, "#");
        $type = "Missing hashtag";

        if ($with_hashtag == 0) {
            return $type;
        }
    }

    public static function multiple_hashtag($header, $requestor_no)
    {
        $multiple_hashtag = substr_count($header, "#") > 1;
        $type = "Multiple hashtag";

        if ($multiple_hashtag) {
            return $type;
        }
    }

    public static function missing_dash($data, $type, $requestor_no)
    {
        $affected_rows = [];
        $with_dash = substr_count($data, "-");

        if ($with_dash < 5 && $type == "header") {
            $type = "Missing dash";
            return $type;
        } elseif ($type == "body") {
            $orders = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $data)));

            foreach ($orders as $k => $order) {
                $dash_count = substr_count($order, "-");

                if ($dash_count < 1) {
                    $line_no = $k + 1;
                    array_push($affected_rows, $line_no);
                }
            }

            return $affected_rows;
        }
    }

    public static function multiple_dash($data, $type, $requestor_no)
    {
        $affected_rows = [];
        $multiple_dash = substr_count($data, "-");

        if ($multiple_dash > 5 && $type == "header") {
            $type = "Multiple dash";
            return $type;
        } elseif ($type == "body") {
            $orders = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $data)));

            foreach ($orders as $k => $order) {
                $dash_count = substr_count($order, "-");

                if ($dash_count > 2) {
                    $line_no = $k + 1;
                    array_push($affected_rows, $line_no);
                }
            }

            return $affected_rows;
        }
    }

    public static function missing_code($data, $type, $requestor_no)
    {
        if ($type == "header") {
            $store_code = explode("-", $data)[0];
            $type = "Missing store code";

            if (!$store_code) {
                return $type;
            }
        } elseif ($type == "body") {
            $affected_rows = [];
            $orders = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $data)));

            foreach ($orders as $k => $order) {
                if (!explode("-", $order)[0]) {
                    array_push($affected_rows, $k + 1);
                }
            }
            return $affected_rows;
        }
    }

    public static function missing_order_no($header, $requestor_no)
    {
        $order_no = explode("-", $header)[1];
        $type = "Missing order no.";

        if (!$order_no) {
            return $type;
        }
    }
    public static function missing_hri($header, $requestor_no)
    {
        $hri = explode("-", $header)[2];
        $type = "Missing hri customer.";

        if (!$hri) {
            return $type;
        }
    }

    public static function missing_year($header, $requestor_no)
    {
        $year = explode("-", $header)[3];
        $type = "Missing year.";

        if (!$year) {
            return $type;
        }
    }

    public static function multiple_year($header, $requestor_no)
    {
        $valid_year = strlen(explode("-", $header)[3]) == 4;

        $type = "Invalid year format.";

        if (!$valid_year) {
            return $type;
        }
    }

    public static function missing_month($header, $requestor_no)
    {
        $month = explode("-", $header)[4];
        $type = "Missing month.";

        if (!$month) {
            return $type;
        }
    }

    public static function multiple_month($header, $requestor_no)
    {
        $valid_month = strlen(explode("-", $header)[4]) == 2;
        $type = "Invalid month format.";

        if (!$valid_month) {
            return $type;
        }
    }

    public static function over_count_month($header, $requestor_no)
    {
        $multiple_day = current(explode("#", explode("-", $header)[4])) > 12;
        $type = "Month must be less than or equal to 12.";

        if ($multiple_day) {
            return $type;
        }
    }

    public static function missing_day($header, $requestor_no)
    {
        $header_4 = isset(explode("-", $header)[5]) ? explode("-", $header)[5] : null;
        $day = current(explode("#", $header_4));
        $type = "Missing day.";

        if (!$day) {
            return $type;
        }
    }

    public static function multiple_day($header, $requestor_no)
    {
        $header_4 = isset(explode("-", $header)[5]) ? explode("-", $header)[5] : null;

        $valid_day = strlen(current(explode("#", $header_4))) == 2;
        $type = "Invalid day format.";

        if (!$valid_day) {
            return $type;
        }
    }

    public static function over_count_day($header, $requestor_no)
    {
        $header_4 = isset(explode("-", $header)[5]) ? explode("-", $header)[5] : null;
        $multiple_day = current(explode("#", $header_4)) > 31;
        $type = "Day must be less than or equal to 31.";

        if ($multiple_day) {
            return $type;
        }
    }

    public static function check_alphanumerics($data, $type, $requestor_no)
    {
        if ($type == "header") {
            $order_no = isset(explode("-", $data)[1]) ? explode("-", $data)[1] : null;
            $year = isset(explode("-", $data)[3]) ? explode("-", $data)[3] : null;
            $month = isset(explode("-", $data)[4]) ? explode("-", $data)[4] : null;
            $day = isset(explode("-", $data)[5]) ? explode("-", $data)[5] : null;

            $order_no_validation = SmsFunctionHri::is_alphanumeric(
                $order_no,
                "order number",
                $requestor_no
            );

            if ($order_no_validation) {
                return $order_no_validation;
            }

            $year_validation = SmsFunctionHri::is_alphanumeric($year, "year", $requestor_no);
            if ($year_validation) {
                return $year_validation;
            }

            $month_validation = SmsFunctionHri::is_alphanumeric($month, "month", $requestor_no);
            if ($month_validation) {
                return $month_validation;
            }

            $day_validation = SmsFunctionHri::is_alphanumeric($day, "day", $requestor_no);
            if ($day_validation) {
                return $day_validation;
            }
        } elseif ($type == "body") {
            $affected_rows = [];
            $orders = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $data)));

            foreach ($orders as $k => $order) {
                if (isset(explode("-", $order)[1])) {
                    if (explode("-", $order)[1]) {
                        $qty = explode("-", $order)[1];
                        if (!is_numeric($qty)) {
                            array_push($affected_rows, $k + 1);
                        }
                    }
                }
            }
            return $affected_rows;
        }
    }

    public static function is_alphanumeric($number, $date_type, $requestor_no)
    {
        if (preg_match("/[a-zA-Z]/", $number)) {
            $type = "Invalid " . $date_type . " number format.";
            return $type;
        }
    }

    public static function invalid_date($header, $requestor_no)
    {
        $header_2 = isset(explode("-", $header)[3]) ? explode("-", $header)[3] : null;
        $header_3 = isset(explode("-", $header)[4]) ? explode("-", $header)[4] : null;
        $header_4 = isset(explode("-", $header)[5]) ? explode("-", $header)[5] : null;
        $header_hash_0 = isset(explode("#", $header_4)[0]) ? explode("#", $header_4)[0] : null;

        $year = $header_2;
        $month = $header_3;
        $day = $header_hash_0;

        $date = $year . "-" . $month . "-" . $day;
        $date_today = date("Y-m-d", strtotime(Carbon::now()));

        if ($date < $date_today) {
            $type = "Invalid date needed.";
            return $type;
            // } elseif ($date == $date_today) {
            //     $type = "date needed must be tommorow.";
            //     return $type;
        }
    }

    public static function store_not_exist($header, $requestor_no)
    {
        $store_code = explode("-", $header)[0];
        $is_tag = User::where("mobile_no", $requestor_no)
            ->whereHas("scope_order", function ($query) use ($store_code) {
                $query->where("location_code", $store_code);
            })
            ->exists();

        if (!$is_tag) {
            return "Store not tagged.";
        }
    }
    public static function hri_not_exist($header, $requestor_no)
    {
        $hri_code = explode("-", $header)[2];
        $is_tag = HRI::where("code", $hri_code)->exists();

        if (!$is_tag) {
            return "Invalid HRI Customer.";
        }
    }

    public static function cut_off($header, $requestor_no)
    {
        $cutoff = Cutoff::get()->first();
        $time_now = Carbon::now()
            ->timezone("Asia/Manila")
            ->format("H:i");

        if ($time_now > $cutoff) {
            $type = "Cut off reach.";
            return $type;
        }
    }

    public static function unregistered_number_permission($header, $requestor_no)
    {
        $registered_no = User::where("mobile_no", $requestor_no)->exists();

        if (!$registered_no) {
            $type = "Unregistered number.";
            return $type;
        }
        $invalid_requestor = User::with("role")
            ->where("mobile_no", $requestor_no)
            ->get()
            ->first();
        $roles = $invalid_requestor->role->access_permission;
        $valid_role = explode(", ", $roles);
        $sms_role = in_array("sms", $valid_role);

        if (!$sms_role) {
            $type = "Account doesn't have sms permission.";
            return $type;
        }
    }

    public static function unregistered_item_code($data, $requestor_no)
    {
        $affected_rows = [];
        $orders = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $data)));
        $materials = SmsFunctionHri::get_materials();

        foreach ($orders as $k => $order) {
            $item_code = explode("-", $order)[0];

            if (!isset($materials->firstWhere("code", $item_code)->id)) {
                array_push($affected_rows, $k + 1);
            }
        }

        return $affected_rows;
    }

    public static function missing_qty($data, $requestor_no)
    {
        $affected_rows = [];
        $orders = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $data)));

        foreach ($orders as $k => $order) {
            $product = explode("-", $order)[1];
            if (empty($product)) {
                array_push($affected_rows, $k + 1);
            }
        }
        return $affected_rows;
    }

    public static function duplicate($header, $data, $requestor_no, $keyword)
    {
        $affected_rows = [];

        $order_no = explode("-", $header)[1];
        $order_no = explode("#", $order_no)[0];
        $orders = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $data)));
        $transactions = SmsFunctionHri::get_transaction_orders();
        $date_today = date("Y-m-d", strtotime(Carbon::now()));
        $requestor_id = User::where("mobile_no", $requestor_no)->first()->id;
        $account_name = User::where("mobile_no", $requestor_no)->first()->account_name;

        $transactions = $transactions
            ->where("requestor_id", $requestor_id)
            ->where("order_no", $order_no)
            ->where("order_type", $keyword)
            ->whereBetween("date_ordered", [$date_today . " 00:00:00", $date_today . " 24:00:00"]);

        if (!$transactions->isEmpty()) {
            foreach ($orders as $k => $order) {
                $material_code = explode("-", $order)[0];

                $orders_in_db = $transactions->first()->orders;
                foreach ($orders_in_db as $order_in_db) {
                    if ($material_code == $order_in_db->material_code) {
                        array_push($affected_rows, $k + 1);
                    }
                }
            }
        }
        return $affected_rows;
    }

    public static function is_multiple_products($data)
    {
        $orders = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $data)));
        $product_codes = array_map(function ($item) {
            return explode("-", $item)[0];
        }, $orders);
        $unique = array_unique($product_codes);

        $duplicates = array_diff_assoc($product_codes, $unique);
        $duplicate_keys = array_keys(array_intersect($product_codes, $duplicates));
        $duplicate_keys = array_map(function ($value) {
            return $value + 1;
        }, $duplicate_keys);
        return $duplicate_keys;
    }

    public static function get_materials()
    {
        return Material::with("category", "uom")->get();
    }

    public static function get_transaction_orders()
    {
        return Transaction::with("orders")->get();
    }

    public static function send($requestor_no, $type)
    {
        return SendSMS::send($type, $requestor_no);
    }

    public static function viber($requestor_id, $type)
    {
        return SendSMS::viber($type, $requestor_id);
    }

    public static function save_sms_order($header, $body, $requestor_no, $requestor_id, $keyword)
    {
        $type = "success";
        $store_code = explode("-", $header)[0];
        $store_details = User::where("mobile_no", $requestor_no)->first();
        $hri_code = explode("-", $header)[2];
        $hri_details = HRI::where("code", $hri_code)->first();
        $hri_id = $hri_details->id;
        $hri_code = $hri_details->code;
        $hri_name = $hri_details->name;
        $location_id = $store_details->location_id;
        $location_code = $store_details->location_code;
        $location = $store_details->location;
        $department_id = $store_details->department_id;
        $department_code = $store_details->department_code;
        $department = $store_details->department;
        $company_id = $store_details->company_id;
        $company_code = $store_details->company_code;
        $company = $store_details->company;
        $account_id = $store_details->id;
        $account_code = $store_details->account_code;
        $account_name = $store_details->account_name;
        $role = $store_details->role_id;

        $order_no = explode("-", $header)[1];
        $year = explode("-", $header)[3];
        $month = explode("-", $header)[4];
        $day = explode("-", $header)[5];
        $date_needed = $year . "-" . $month . "-" . $day;

        $get_materials = SmsFunctionHri::get_materials();
        $orders = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $body)));

        $transaction = Transaction::create([
            "order_no" => $order_no,
            "date_needed" => $date_needed,

            "hri_id" => $hri_id,
            "hri_code" => $hri_code,
            "hri_name" => $hri_name,

            "company_id" => $company_id,
            "company_code" => $company_code,
            "company_name" => $company,

            "department_id" => $department_id,
            "department_code" => $department_code,
            "department_name" => $department,

            "charge_company_id" => $company_id,
            "charge_company_code" => $company_code,
            "charge_company_name" => $company,

            "charge_department_id" => $department_id,
            "charge_department_code" => $department_code,
            "charge_department_name" => $department,

            "charge_location_id" => $location_id,
            "charge_location_code" => $location_code,
            "charge_location_name" => $location,

            "location_id" => $location_id,
            "location_code" => $location_code,
            "location_name" => $location,

            "customer_id" => $location_id,
            "customer_code" => $location_code,
            "customer_name" => $location,
            "order_type" => $keyword,

            "requestor_id" => $account_id,
            "requestor_name" => $account_name,

            "approver_id" => null,
            "approver_name" => null,
            "date_ordered" => Carbon::now()->timeZone("Asia/Manila"),
            "date_approved" => $role == 5 ? Carbon::now()->timeZone("Asia/Manila") : null,
        ]);

        foreach ($orders as $order) {
            $material_code = explode("-", $order)[0];
            $material_id = $get_materials->firstWhere("code", $material_code)->id;
            $material_name = $get_materials->firstWhere("code", $material_code)->name;
            $material_uom_id = $get_materials->firstWhere("code", $material_code)->uom->id;
            $material_uom_name = $get_materials->firstWhere("code", $material_code)->uom->code;
            $category_id = $get_materials->firstWhere("code", $material_code)->category->id;
            $category_name = $get_materials->firstWhere("code", $material_code)->category->name;

            $qty = explode("-", $order)[1];
            $remarks = isset(explode("-", $order)[2]) ? explode("-", $order)[2] : "";

            Order::create([
                "transaction_id" => $transaction->id,
                "requestor_id" => $account_id,

                "order_no" => $order_no,

                "customer_code" => $location_code,

                "material_id" => $material_id,
                "material_code" => $material_code,
                "material_name" => $material_name,

                "uom_id" => $material_uom_id,
                "uom_code" => $material_uom_name,

                "category_id" => $category_id,
                "category_name" => $category_name,

                "quantity" => $qty,
                "remarks" => $remarks,
            ]);
        }

        return SmsFunctionHri::viber($requestor_id, $type);
    }

    public static function get_transaction($date_needed, $store_code, $order_no)
    {
        return Transaction::with([
            "orders" => function ($query) {
                $query->whereNull("deleted_at")->select("id", "transaction_id", "material_code");
            },
        ])
            ->whereDate("date_needed", $date_needed)
            ->where([
                "location_code" => $store_code,
                "order_no" => $order_no,
            ])
            ->first();
    }

    public static function cancel_order($header, $body, $requestor_no)
    {
        $type = "delete";
        $store_code = explode("-", $header)[0];
        $order_no = explode("-", $header)[1];
        $date_needed =
            explode("-", $header)[2] .
            "-" .
            explode("-", $header)[3] .
            "-" .
            explode("-", $header)[4];

        $orders = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $body)));
        $transaction = SmsFunctionHri::get_transaction($date_needed, $store_code, $order_no);

        if ($transaction && $transaction->orders) {
            foreach ($orders as $order) {
                $material_code = explode("-", $order)[0];
                $orders = $transaction->orders;
                $order_details = $orders->where("material_code", $material_code)->first();

                if ($order_details) {
                    $order_id = $order_details->id;
                    Order::where("id", $order_id)->delete();
                    $response = "delete";
                } else {
                    $response = "Order already cancelled";
                }
            }
        }

        $transaction = SmsFunctionHri::get_transaction($date_needed, $store_code, $order_no);
        $is_empty_transaction = empty($transaction);

        if ($is_empty_transaction) {
            $response = "Order already cancelled";
        } else {
            if ($transaction->orders->count() == 0) {
                $transaction->delete();
            }
        }

        return $response;
    }
}
