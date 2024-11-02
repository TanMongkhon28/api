<?php
// �Դ����ʴ��Ţ�ͼԴ��Ҵ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ��駤�� CORS ����͹حҵ�����Ҷ֧�ҡ��¹͡
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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

// �Ѻ user_id ����ͧ��èҡ request (�蹼�ҹ JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$user_id = $data['user_id'] ?? null; // ��Ǩ�ͺ����� user_id �١�����������

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "user_id is required"]);
    exit();
}

// ����� SQL query ���ʹ֧�����źٸ���١�ͧ�� user_id ��д֧������ details �Ҵ���
$sql = "
    SELECT bookings.id AS booking_id, booth.*, bookings.status AS booking_status, 
           bookings.booking_date, bookings.payment_due_date, bookings.details, bookings.event_id
    FROM booth
    JOIN bookings ON booth.id = bookings.booth_id
    WHERE bookings.user_id = ? AND bookings.status != 'cancelled' AND bookings.status != 'expired';
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ��Ǩ�ͺ����բ������������
if ($result->num_rows > 0) {
    $booths = $result->fetch_all(MYSQLI_ASSOC); // �֧�����ŷ�������ٻẺ associative array
    echo json_encode(["status" => "success", "booths" => $booths]);
} else {
    echo json_encode(["status" => "error", "message" => "No booked booths found for this user"]);
}

// �Դ�����������
$stmt->close();
$conn->close();
?>