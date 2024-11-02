<?php
// �Դ����ʴ��Ţ�ͼԴ��Ҵ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ��駤�� CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// �Ѵ��äӢ� OPTIONS (Preflight Request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
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

// �Ѻ��� booking_id �ҡ request
$booking_id = $_POST['booking_id'] ?? null;

// ��Ǩ�ͺ����� booking_id �������������
if (!$booking_id || !isset($_FILES['payment_slip'])) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit();
}

// ��Ǩ�ͺʶҹТͧ booking ��͹
$sql_check_status = "SELECT status FROM bookings WHERE id = ?";
$stmt_check_status = $conn->prepare($sql_check_status);
$stmt_check_status->bind_param("i", $booking_id);
$stmt_check_status->execute();
$result = $stmt_check_status->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo json_encode(["status" => "error", "message" => "Booking not found"]);
    exit();
}

$status = $booking['status'];

// ���͹حҵ�������Թ���ʶҹ��� 'expired' ���� 'cancelled'
if ($status === 'expired' || $status === 'cancelled') {
    echo json_encode(["status" => "error", "message" => "Cannot make a payment for a booking that is expired or cancelled"]);
    exit();
}

// ��駤����������кѹ�֡���
$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true); // ���ҧ��á���ն���ѧ�����
}

// ��駪�������������������ѻ��Ŵ��ѧ���������˹�
$target_file = $target_dir . basename($_FILES["payment_slip"]["name"]);
if (move_uploaded_file($_FILES["payment_slip"]["tmp_name"], $target_file)) {
    $payment_slip_url = "https://yourdomain.com/" . $target_file; // ���ҧ URL �ͧ���
} else {
    echo json_encode(["status" => "error", "message" => "Error uploading file"]);
    exit();
}

// ����� SQL query �����ѻവ payment_slip ��� payment_status 㹵��ҧ payment ���� booking_id �繤���
$sql_update_payment = "UPDATE payment SET payment_slip = ?, payment_status = 'pending' WHERE booking_id = ?";
$stmt_update_payment = $conn->prepare($sql_update_payment);

if (!$stmt_update_payment) {
    die(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
}

// Bind ��ҷ������ query (payment_slip_url ��� booking_id)
$stmt_update_payment->bind_param("si", $payment_slip_url, $booking_id);

// Execute query
if ($stmt_update_payment->execute()) {
    echo json_encode(["status" => "success", "message" => "Payment slip updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating payment: " . $stmt_update_payment->error]);
}

// �Դ����������Ͱҹ������
$stmt_check_status->close();
$stmt_update_payment->close();
$conn->close();
?>
