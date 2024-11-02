<?php
// ��駤�� CORS Headers
header("Access-Control-Allow-Origin: *"); // ͹حҵ�ء����
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // �к��Ըա�÷��͹حҵ
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // �к� headers ���͹حҵ

// ��Ǩ�ͺ������¡ OPTIONS request ����Ѻ��� preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

// �Ѻ�����ŷ����繨ҡ request (��ҹ JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$booking_id = $data['booking_id'] ?? null;
$status = $data['status'] ?? null;

// ��Ǩ�ͺ����բ����ŷ����繤ú�������
if (!$booking_id || !$status) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit();
}

// ����� SQL query �����ѻവʶҹС�èͧ㹵��ҧ bookings ���� booking_id
$sql = "UPDATE bookings SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
}

// Bind ��ҷ������ query (status ��� booking_id)
$stmt->bind_param("si", $status, $booking_id);

// Execute query ���͵�Ǩ�ͺʶҹС�èͧ
if ($stmt->execute()) {
    // ��Ǩ�ͺ��Ҷ��ʶҹ��� 'cancelled' ����ѻവʶҹ�㹵��ҧ payment �� 'failed'
    if ($status === 'cancelled') {
        // ����� SQL query ����Ѻ�ѻവʶҹ� payment �� 'failed'
        $updatePaymentSQL = "UPDATE payment SET payment_status = 'failed' WHERE booking_id = ?";
        $stmtUpdatePayment = $conn->prepare($updatePaymentSQL);
        if (!$stmtUpdatePayment) {
            die(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
        }
        $stmtUpdatePayment->bind_param("i", $booking_id);
        if ($stmtUpdatePayment->execute()) {
            echo json_encode(["status" => "success", "message" => "Booking and payment status updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error updating payment: " . $stmtUpdatePayment->error]);
        }
        $stmtUpdatePayment->close();
    } else {
        echo json_encode(["status" => "success", "message" => "Booking status updated successfully"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Error updating booking: " . $stmt->error]);
}

// �Դ����������Ͱҹ������
$stmt->close();
$conn->close();
?>