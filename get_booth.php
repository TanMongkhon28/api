<?php
// �Դ����ʴ��Ţ�ͼԴ��Ҵ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ͹حҵ�����Ҷ֧�ҡ�ء���
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// �������Ͱҹ������
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

// ��Ǩ�ͺ����������Ͱҹ������
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error); // �ѹ�֡��ͼԴ��Ҵ
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// �֧�����źٸ
$sql = "SELECT * FROM booth";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $booths = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["status" => "success", "booths" => $booths]);
} else {
    echo json_encode(["status" => "error", "message" => "No booths found"]);
}

$conn->close();
?>