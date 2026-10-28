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
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") { http_response_code(204); exit;}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {respond(405, "error", "Gunakan metode POST.");}

$data = json_decode(file_get_contents("php://input"));
if (!is_object($data) || !isset($data->idToken) || !is_string($data->idToken)) {
    respond(400,"error", "ID Token dari Google tidak ditemukan.");
}
$idToken = $data->idToken ;

$googleVerifyUrl = "https://googleleapis.com" . urlencode($idToken);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $googleVerifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$googleResponse = curl_exec($ch);
curl_close($ch);