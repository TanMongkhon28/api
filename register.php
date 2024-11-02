<?php
require 'vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->post('/register', function (Request $request, Response $response) {
    // เชื่อมต่อฐานข้อมูล
    $servername = "151.106.124.154";
    $username = "u583789277_wag19";
    $password = "2567Inspire";
    $dbname = "u583789277_wag19";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    // รับข้อมูล JSON ที่ส่งมา
    $data = json_decode($request->getBody()->getContents(), true);

    // รับข้อมูลจาก JSON
    $prefix = $data['prefix'];
    $name = $data['name'];
    $lastname = $data['lastname'];
    $phone = $data['phone'];
    $email = $data['email'];
    $password = $data['password'];
    $confirmPassword = $data['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        $response->getBody()->write(json_encode(["status" => "error_password_mismatch"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบอีเมล
    $checkEmailSQL = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkEmailSQL);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response->getBody()->write(json_encode(["status" => "error_email_exists"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (prefix, name, lastname, phone, email, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $prefix, $name, $lastname, $phone, $email, $hashedPassword);

        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(["status" => "success"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } else {
            $response->getBody()->write(json_encode(["status" => "error", "message" => $stmt->error]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    $stmt->close();
    $conn->close();
});

$app->run();
