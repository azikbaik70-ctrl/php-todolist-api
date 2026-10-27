<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

function respond(int $statusCode, string $status, mixed $message): void
{
    http_response_code($statusCode);
    echo json_encode(
        ["status" => $status, "message" => $message],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "error", "Gunakan metode POST.");
}

$data = json_decode(file_get_contents("php://input"));

if (
    !is_object($data) ||
    !isset( $data->email, $data->password) ||
    !is_string($data->email) ||
    !is_string($data->password) 
    
) {
    respond(400, "error", "Data tidak lengkap atau format JSON tidak valid.");
}


$email = trim($data->email);
$password = $data->password;


if ( $email === "" || $password === "") {
    respond(400, "error", "Email dan Password tidak boleh kosong.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(400, "error", "Format email tidak valid.");
}



$host = getenv("DB_HOST") ?: "localhost";
$username = getenv("DB_USER") ?: "root";
$databaseName = getenv("DB_NAME") ?: "todolist";
$databasePassword = getenv("DB_PASSWORD") ?: "";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $database = new mysqli($host, $username, $databasePassword, $databaseName);
    $database->set_charset("utf8mb4");

    
    $statement = $database->prepare(
        "SELECT id, nama, password, role FROM users WHERE email = ?"
    );
    $statement->bind_param("s", $email);
    $statement->execute();

    $result = $statement->get_result();

    if ($result->num_rows === 0) {
        respond(401, "error", "Email atau password Salah,Silahkan coba lagi!");
     }
     
     $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        respond(401, "error", "Email atau password Salah,Silahkan coba lagi!");
    }
    respond(200, "success", [
        "message" => "Login Berhasil.",
        "user"  => [
            "id" => (int)$user['id'],
            "user" => $user['nama'],
            "role" => $user['role']
        ]
    ]);
} catch (mysqli_sql_exception $exception) {
    error_log("Register database error: " . $exception->getMessage());
    respond(500, "error", "Terjadi kesalahan pada database.");
}
