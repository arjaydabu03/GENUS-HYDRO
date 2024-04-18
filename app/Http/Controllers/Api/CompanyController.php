<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Response\Status;
use App\Functions\GlobalFunction;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status;
        $search = $request->search;
        $paginate = isset($request->paginate) ? $request->paginate : 1;

        $company = Company::when($status === "inactive", function ($query) {
            $query->onlyTrashed();
        })->when($search, function ($query) use ($search) {
            $query
                ->where("code", "like", "%" . $search . "%")
                ->orWhere("name", "like", "%" . $search . "%");
        });

        $company = $paginate
            ? $company->orderByDesc("updated_at")->paginate($request->rows)
            : $company->orderByDesc("updated_at")->get();

        $is_empty = $company->isEmpty();

        if ($is_empty) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        return GlobalFunction::response_function(Status::COMPANY_DISPLAY, $company);
    }
    public function store(Request $request)
    {
        $sync = $request->all();

        $company = Company::upsert($sync, ["sync_id"], ["code", "name", "deleted_at"]);

        return GlobalFunction::save(Status::COMPANY_IMPORT, $request->toArray());
    }
}
