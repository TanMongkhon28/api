<?php
session_start(); // ����� session

header("Access-Control-Allow-Origin: *"); // ͹حҵ�ء���� (����Ѻ��÷��ͺ)
header("Access-Control-Allow-Methods: POST, OPTIONS"); // ͹حҵ੾�� POST ��� OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // ͹حҵ੾�кҧ header

// �������Ͱҹ������
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

// ��Ǩ�ͺ�����������
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// ��ҹ������ JSON �������
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// �Ѻ�����Ũҡ JSON �������
$user_id = $data['user_id'] ?? null;
$prefix = $data['prefix'] ?? null;
$name = $data['name'] ?? null;
$lastname = $data['lastname'] ?? null;
$phone = $data['phone'] ?? null;
$email = $data['email'] ?? null;
$new_password = $data['new_password'] ?? null;
$confirm_password = $data['confirm_password'] ?? null;

// ��Ǩ�ͺ����բ����ŷ����繤ú�������
if (!$user_id || !$prefix || !$name || !$lastname || !$phone || !$email) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit();
}

// ��Ǩ�ͺ�������Ź��������㹰ҹ����������������� (���ͧ�����ͧ����餹����ͧ)
$checkEmailSQL = "SELECT * FROM users WHERE email = ? AND id != ?";
$stmt = $conn->prepare($checkEmailSQL);
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "This email is already in use"]);
    exit();
}

// ����� SQL ����Ѻ�����䢢����ż����
if ($new_password && $confirm_password) {
    if ($new_password !== $confirm_password) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
        exit();
    }

    // �Ϊ���ʼ�ҹ����
    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

    $sql = "UPDATE users SET prefix = ?, name = ?, lastname = ?, phone = ?, email = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $prefix, $name, $lastname, $phone, $email, $hashedPassword, $user_id);
} else {
    $sql = "UPDATE users SET prefix = ?, name = ?, lastname = ?, phone = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $prefix, $name, $lastname, $phone, $email, $user_id);
}

// ���Թ��� query
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "User information updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating user information: " . $stmt->error]);
}

// �Դ�����������
$stmt->close();
$conn->close();
?>
