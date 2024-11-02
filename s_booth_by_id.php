<?php
// �Դ����ʴ��Ţ�ͼԴ��Ҵ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ������ǹ CORS header
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// ��Ǩ�ͺ����繤Ӣ� OPTIONS ������� (preflight request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // �ͺ��Ѻ���� 200 OK
    exit();
}

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

// �Ѻ��� booth_id ����ͧ��èҡ request (�蹼�ҹ URL ���� JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$booth_id = $data['booth_id'] ?? null; // ��Ǩ�ͺ����� booth_id �١�����������

if (!$booth_id) {
    echo json_encode(["status" => "error", "message" => "booth_id is required"]);
    exit();
}

// ����� SQL query ���ʹ֧�����źٸ��� booth_id
$sql = "SELECT * FROM booth WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booth_id);
$stmt->execute();
$result = $stmt->get_result();

// ��Ǩ�ͺ����բ����źٸ�������
if ($result->num_rows > 0) {
    $booth = $result->fetch_assoc(); // �֧������������ (�ٸ)
    echo json_encode(["status" => "success", "booth" => $booth]);
} else {
    echo json_encode(["status" => "error", "message" => "Booth not found"]);
}

// �Դ�����������
$stmt->close();
$conn->close();
?>