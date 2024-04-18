<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Order;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Cutoff;

use App\Http\Resources\OrderResource;
use App\Http\Resources\TransactionResource;

use App\Response\Status;
use App\Functions\GlobalFunction;
use App\Http\Requests\Order\DisplayRequest;

use Carbon\carbon;

class ApproverController extends Controller
{
    public function index(DisplayRequest $request)
    {
        $search = $request->input("search", "");
        $status = $request->input("status", "");
        $rows = $request->input("rows", 10);
        $from = $request->from;
        $to = $request->to;

        $user_scope = User::where("id", Auth::id())
            ->with("scope_approval")
            ->first()
            ->scope_approval->pluck("location_id");

        $approver_id = User::where("id", Auth::id())->pluck("id");

        $order = Transaction::with("orders")
            ->where(function ($query) use ($user_scope) {
                $query->whereIn("department_id", $user_scope)->whereNot("requestor_id", Auth::id());
            })

            ->where(function ($query) use ($search) {
                $query
                    ->where("date_ordered", "like", "%" . $search . "%")
                    ->orWhere("order_no", "like", "%" . $search . "%")
                    ->orWhere("date_needed", "like", "%" . $search . "%")
                    ->orWhere("date_approved", "like", "%" . $search . "%")
                    ->orWhere("company_name", "like", "%" . $search . "%")
                    ->orWhere("department_name", "like", "%" . $search . "%")
                    ->orWhere("location_name", "like", "%" . $search . "%")
                    ->orWhere("customer_code", "like", "%" . $search . "%")
                    ->orWhere("customer_name", "like", "%" . $search . "%");
            })
            ->when(isset($request->from) && isset($request->to), function ($query) use (
                $from,
                $to
            ) {
                $query->where(function ($query) use ($from, $to) {
                    $query
                        ->whereDate("date_ordered", ">=", $from)
                        ->whereDate("date_ordered", "<=", $to);
                });
            })
            ->when($status === "pending", function ($query) {
                $query->whereNull("date_approved");
            })
            ->when($status === "approve", function ($query) {
                $query->whereNotNull("date_approved");
            })
            ->when($status === "disapprove", function ($query) use ($approver_id) {
                $query
                    ->whereNotNull("date_approved")
                    ->where("approver_id", $approver_id)
                    ->onlyTrashed();
            })
            ->when($status === "all", function ($query) use ($approver_id) {
                $query->where("approver_id", $approver_id)->withTrashed();
            })
            ->orderByRaw("CASE WHEN rush IS NULL AND date_approved IS NULL THEN 0 ELSE 1 END DESC")
            ->orderByDesc("updated_at")
            ->paginate($rows);

        if ($order->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        TransactionResource::collection($order);

        return GlobalFunction::response_function(Status::ORDER_DISPLAY, $order);
    }

    public function update(Request $request, $id)
    {
        $user = Auth()->user();
        $user_scope = User::where("id", $user->id)
            ->with("scope_approval")
            ->first()
            ->scope_approval->pluck("location_id");

        $time_now = Carbon::now()
            ->timezone("Asia/Manila")
            ->format("H:i");
        $date_today = Carbon::now()
            ->timeZone("Asia/Manila")
            ->format("Y-m-d");
        $cutoff = date("H:i", strtotime(Cutoff::get()->value("time")));

        $transaction = Transaction::where("id", $id);

        $not_found = $transaction->get();
        if ($not_found->isEmpty()) {
            return GlobalFunction::denied(Status::NOT_FOUND);
        }

        $not_allowed = $transaction->whereIn("department_id", $user_scope)->get();
        if ($not_allowed->isEmpty()) {
            return GlobalFunction::denied(Status::ACCESS_DENIED);
        }

        $is_rush = Transaction::where("id", $id)
            ->whereNotNull("rush")
            ->whereDate("date_needed", $date_today)
            ->count();
        $is_advance = Transaction::where("id", $id)
            ->where("date_needed", ">", $date_today)
            ->count();

        if ($time_now > $cutoff && !$is_rush && !$is_advance) {
            return GlobalFunction::denied(Status::CUT_OFF);
        }

        $transaction->update([
            "approver_id" => $user->id,
            "approver_name" => $user->account_name,
            "date_approved" => date("Y-m-d H:i:s"),
        ]);

        $transaction = new TransactionResource($transaction->get()->first());

        return GlobalFunction::save(Status::TRANSACTION_APPROVE, $transaction);
    }
    public function restore(Request $request, $id)
    {
        $user = Auth()->user();
        $user_scope = User::where("id", $user->id)
            ->with("scope_approval")
            ->first()
            ->scope_approval->pluck("location_id");

        $transaction = Transaction::where("id", $id);

        $not_found = $transaction->get();
        if ($not_found->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $not_allowed = $transaction->whereIn("department_id", $user_scope)->get();
        if ($not_allowed->isEmpty()) {
            return GlobalFunction::denied(Status::ACCESS_DENIED);
        }

        $transaction->update([
            "approver_id" => null,
            "approver_name" => null,
            "date_approved" => null,
        ]);

        $transaction = new TransactionResource($transaction->get()->first());

        return GlobalFunction::save(Status::TRANSACTION_RETURN, $transaction);
    }

    public function approver_count(Request $request)
    {
        $date_today = Carbon::now()
            ->timeZone("Asia/manila")
            ->format("Y-m-d");

        $user_scope = User::where("id", Auth::id())
            ->with("scope_approval")
            ->first()
            ->scope_approval->pluck("location_id");

        $pending = Transaction::whereNull("date_approved")
            ->whereNot("requestor_id", Auth::id())
            ->where(function ($query) use ($user_scope, $date_today) {
                $query->whereIn("department_id", $user_scope)->whereDate("created_at", $date_today);
            })
            ->get()
            ->count();

        $count = [
            "pending" => $pending,
        ];

        return GlobalFunction::response_function(Status::COUNT_DISPLAY, $count);
    }
}
