<?php

namespace App\Core;

class Response {
    static function json(string $message = "success", int $code = 200, array $data = []) {
        http_response_code($code);
        header("Content-Type: application/json");
        echo json_encode([
            "message" => $message,
            "code" => $code,
            "data" => $data
        ]);
        exit;
    }
}