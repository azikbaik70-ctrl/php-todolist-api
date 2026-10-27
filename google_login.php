<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

function respond(int $statusCode, string $status, mixed $message): void {
    http_response_code($statusCode);
    echo json_encode(["status" => $status, "message" => $message], JSON_UNESCAPED_UNICODE);
    exit;
}