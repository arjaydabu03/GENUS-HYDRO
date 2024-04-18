<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\carbon;
use App\Models\Cutoff;
class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $order_no = $this->input("order_no");
        $keyword = $this->input("keyword_code");
        $customer_code = $this->input("customer.code");

        $requestor_id = $this->user()->id;

        return [
            "order_no" => [
                "required",
                Rule::unique("transactions", "order_no")
                    ->where("requestor_id", $requestor_id)
                    ->where(function ($query) {
                        return $query->whereDate("date_ordered", date("Y-m-d"));
                    })
                    ->whereNull("deleted_at"),
            ],

            "rush" => "nullable",

            "company.id" => "required",
            "company.code" => "required",
            "company.name" => "required",

            "hri.id" => Rule::requiredIf($this->input("keyword.code") == "HRI"),
            "hri.code" => Rule::requiredIf($this->input("keyword.code") == "HRI"),
            "hri.name" => Rule::requiredIf($this->input("keyword.code") == "HRI"),

            "keyword.id" => "required",
            "keyword.code" => "required",
            "keyword.description" => "required",

            "department.id" => "required",
            "department.code" => "required",
            "department.name" => "required",

            "location.id" => "required",
            "location.code" => "required",
            "location.name" => "required",

            "requestor.id" => ["required", "exists:users,id,deleted_at,NULL"],
            "requestor.name" => "required",

            "customer.id" => "required",
            "customer.code" => "required",
            "customer.name" => "required",

            "charge_company.id" => "required",
            "charge_company.code" => "required",
            "charge_company.name" => "required",

            "charge_department.id" => "required",
            "charge_department.code" => "required",
            "charge_department.name" => "required",

            "charge_location.id" => "required",
            "charge_location.code" => "required",
            "charge_location.name" => "required",

            "order.*.material.id" => ["required", "distinct"],
            "order.*.material.code" => [
                "required",
                "exists:materials,code,deleted_at,NULL",
                Rule::unique("order", "material_code")->where(function ($query) use (
                    $customer_code,
                    $order_no,
                    $requestor_id
                ) {
                    return $query
                        ->where("order_no", $order_no)
                        ->where("customer_code", $customer_code)
                        ->where("requestor_id", $requestor_id)
                        ->where(function ($query) {
                            return $query->whereDate("created_at", date("Y-m-d"));
                        })
                        ->whereNull("deleted_at");
                }),
            ],
            "order.*.material.name" => "required",

            "order.*.category.id" => ["required", "exists:categories,id,deleted_at,NULL"],
            "order.*.category.name" => "required",

            "order.*.uom.id" => ["required", "exists:uom,id,deleted_at,NULL"],
            "order.*.uom.code" => "required",

            "order.*.quantity" => "required",
            "order.*.remarks" => "nullable",
        ];
    }

    public function attributes()
    {
        return [
            "order_no" => "order no.",
            "order.*.material.code" => "material",
            "order.*.material.id" => "Item",
            "hri.id" => "Hri id",
            "hri.code" => "Hri code",
            "hri.name" => "Hri name",
        ];
    }

    public function messages()
    {
        return [
            "order.*.material.code.unique" => "This :attribute has already been ordered.",
            "order.*.material.id.distinct" => "This :attribute has already been ordered.",
            "hri.id.required_if" => " :attribute is required.",
            "hri.code.required_if" => " :attribute is required.",
            "hri.name.required_if" => " :attribute is required.",
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // $validator->errors()->add("custom", $this->user()->id);
            // $validator->errors()->add("custom", $this->route()->id);
            // $validator->errors()->add("custom", "STOP!");
            $time_now = Carbon::now()
                ->timezone("Asia/Manila")
                ->format("H:i");
            $date_today = Carbon::now()
                ->timeZone("Asia/Manila")
                ->format("Y-m-d");

            $cutoff_time = Cutoff::get()->value("time");

            $cutoff = date("H:i", strtotime($cutoff_time));

            $is_rush =
                date("Y-m-d", strtotime($this->input("date_needed"))) == $date_today &&
                $time_now > $cutoff;

            $date_today_1 = Carbon::now()
                ->addDay()
                ->timeZone("Asia/Manila")
                ->format("Y-m-d");

            $is_advance =
                date("Y-m-d", strtotime($this->input("date_needed"))) == $date_today_1 &&
                $time_now > $cutoff;

            $invalid_date = date("Y-m-d", strtotime($this->input("date_needed"))) < $date_today;

            if ($invalid_date) {
                return $validator->errors("Invalid date.");
            }

            $with_rush_remarks = !empty($this->input("rush"));

            if ($cutoff_time != null) {
                if ($is_rush && !$with_rush_remarks) {
                    return $validator->errors()->add("rush", "cut off reach.");
                } elseif ($is_advance && !$with_rush_remarks) {
                    $validator->errors()->add("rush", "cut off reach.");
                }
            }
        });
    }
}
