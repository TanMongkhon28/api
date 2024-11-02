<?php
// ������õ�駤�� CORS ����ҹ���ش�ͧ���
header('Access-Control-Allow-Origin: *'); // ͹حҵ���ء������Ҷ֧��
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // ͹حҵ����� POST, GET ��� OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With'); // ͹حҵ Content-Type, Authorization, ��� X-Requested-With
header('Content-Type: application/json');

// �Դ����ʴ��Ţ�ͼԴ��Ҵ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// �������Ͱҹ������
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

// ��Ǩ�ͺ����������Ͱҹ������
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// �Ѻ��� zone_id �ҡ JSON request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// ��Ǩ�ͺ����ŧ JSON ��С���Ѻ zone_id
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
    exit();
}

$zone_id = $data['zone_id'] ?? null;

// ��Ǩ�ͺ��� zone_id �١�����������
if (!$zone_id || !is_numeric($zone_id)) {
    echo json_encode(["status" => "error", "message" => "Invalid or missing zone_id"]);
    exit();
}

// ����� SQL query ���ʹ֧�����źٸ��� zone_id
$sql = "SELECT * FROM booth WHERE zone_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Failed to prepare SQL: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $zone_id);
$stmt->execute();
$result = $stmt->get_result();

// ��Ǩ�ͺ����բ����źٸ�������
if ($result->num_rows > 0) {
    $booths = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["status" => "success", "booths" => $booths]);
} else {
    echo json_encode(["status" => "error", "message" => "No booths found for this zone"]);
}

$stmt->close();
$conn->close();
?>