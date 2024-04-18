<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Keyword;
use App\Response\Status;
use App\Functions\GlobalFunction;

use App\Http\Requests\Keyword\DisplayRequest;
use App\Http\Requests\Keyword\StoreRequest;
use App\Http\Requests\Keyword\CodeRequest;

class KeywordController extends Controller
{
    public function index(DisplayRequest $request)
    {
        $status = $request->status;
        $search = $request->search;
        $paginate = isset($request->paginate) ? $request->paginate : 1;

        $keyword = Keyword::when($status === "inactive", function ($query) {
            $query->onlyTrashed();
        })->when($search, function ($query) use ($search) {
            $query
                ->where("code", "like", "%" . $search . "%")
                ->orWhere("name", "like", "%" . $search . "%");
        });

        $keyword = $paginate
            ? $keyword->orderByDesc("updated_at")->paginate($request->rows)
            : $keyword->orderByDesc("updated_at")->get();

        $is_empty = $keyword->isEmpty();

        if ($is_empty) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        return GlobalFunction::response_function(Status::KEYWORD_DISPLAY, $keyword);
    }

    public function store(StoreRequest $request)
    {
        $keyword = Keyword::create([
            "code" => $request["code"],
            "description" => $request["description"],
        ]);
        return GlobalFunction::save(Status::KEYWORD_SAVE, $keyword);
    }

    public function update(StoreRequest $request, $id)
    {
        $keyword = Keyword::find($id);

        $not_found = Keyword::where("id", $id)->get();

        if ($not_found->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $keyword->update([
            "code" => $request["code"],
            "description" => $request["description"],
        ]);
        return GlobalFunction::response_function(Status::KEYWORD_UPDATE, $keyword);
    }
    public function destroy($id)
    {
        $keyword = Keyword::where("id", $id)
            ->withTrashed()
            ->get();

        if ($keyword->isEmpty()) {
            return GlobalFunction::not_found(Status::NOT_FOUND);
        }

        $keyword = Keyword::withTrashed()->find($id);
        $is_active = Keyword::withTrashed()
            ->where("id", $id)
            ->first();
        if (!$is_active) {
            return $is_active;
        } elseif (!$is_active->deleted_at) {
            $keyword->delete();
            $message = Status::ARCHIVE_STATUS;
        } else {
            $keyword->restore();
            $message = Status::RESTORE_STATUS;
        }
        return GlobalFunction::response_function($message, $keyword);
    }
    public function validate_keyword_code(CodeRequest $request)
    {
        return GlobalFunction::response_function(Status::SINGLE_VALIDATION);
    }
}
