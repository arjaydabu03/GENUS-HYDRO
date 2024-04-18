<?php

namespace App\Functions;

use App\Response\Status;

class GlobalFunction
{
    // SUCCESS
    public static function save($message, $result = [])
    {
        return response()->json(
            [
                "message" => $message,
                "result" => $result,
            ],
            Status::CREATED_STATUS
        );
    }
    public static function response_function($message, $result = [])
    {
        return response()->json(
            [
                "message" => $message,
                "result" => $result,
            ],
            Status::SUCESS_STATUS
        );
    }
    // ERRORS
    public static function not_found($message, $result = [])
    {
        return response()->json(
            [
                "message" => $message,
                "result" => $result,
            ],
            Status::DATA_NOT_FOUND
        );
    }

    public static function invalid($message, $result = [])
    {
        return response()->json(
            [
                "message" => $message,
                "result" => $result,
            ],
            Status::UNPROCESS_STATUS
        );
    }

    public static function denied($message, $result = [])
    {
        return response()->json(
            [
                "message" => $message,
                "result" => $result,
            ],
            Status::DENIED_STATUS
        );
    }
    public static function cutoff($message, $result = [])
    {
        return response()->json(
            [
                "message" => $message,
                "result" => $result,
            ],
            Status::CUT_OFF_STATUS
        );
    }
}
