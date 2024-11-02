<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// �����š���������Ͱҹ������
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

// ��Ǩ�ͺ����������Ͱҹ������
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// ��Ǩ�ͺ�Ӣ������ POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ��ҹ������ JSON ����������� request body
    $data = json_decode(file_get_contents('php://input'), true);

    // ��Ǩ�ͺ����դ�� booth_name �١����
    if (!isset($data['booth_name'])) {
        echo json_encode(["status" => "error", "message" => "Booth name is required"]);
        exit();
    }

    // ��˹���� booth_name �ҡ�����ŷ���Ѻ��
    $booth_name = $data['booth_name'];

    // ���������� SQL ����ź�����ŵ�� booth_name ����˹�
    $stmt = $conn->prepare("DELETE FROM booth WHERE booth_name = ?");
    $stmt->bind_param("s", $booth_name);

    // �ѹ����� query ��е�Ǩ�ͺ���Ѿ��
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Booth deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "No booth found with that name"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete booth"]);
    }

    // �Դ statement ��� connection
    $stmt->close();
}

$conn->close();
?>
