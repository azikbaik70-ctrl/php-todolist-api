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

$googleVerifyUrl = "https://googleapis.com" . urlencode($idToken);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $googleVerifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$googleResponse = curl_exec($ch);
curl_close($ch);

$payload = json_decode($googleResponse);

if (!is_object($payload) || isset($payload->error_description)) {
    respond(401, "error", "Token Google  tidak valid atau telah kaduwarsa.");
}

$googleEmail = $payload->email;
$googleNama = $payload->nama;

$host = "localhost"; $username = "root"; $databasePassword = ""; $databaseName = mysqli_report(MYSQLI_REPORT_STRICT);

try {
    $database = new mysqli($host, $username, $databasePassword, $databaseName);
    $database->set_charset("utfmb4");

    $statement = $database->prepare("SELECT id, nama, role FROM users WHERE email = ?");
    $statement->bind_param("s", $googleEmail);
    $statement->execute();
    $result = $statement->get_result();

    if($result->num_rows === 0) {
        respond(404, "error", "Email Google Anda belum teradaftar di sistem sekolah.");
    }
    $user = $result->fetch_assoc();

    respond(200, "success", [
        "message" => "Login via Google Berhasil.",
        "user" => [
            "id" => (int)$user['id'],
            "nama" => $user['nama'],
            "role" => $user['role']
        ]
    ]);
} catch (mysqli_sql_exception $execption) {
    respond(500, "Error", "Terjadi Kesalahn database.");
}