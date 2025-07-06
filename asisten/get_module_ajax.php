<?php
require_once '../config.php';
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$course_id = isset($_GET['praktikum_id']) ? (int)$_GET['praktikum_id'] : 0;
$modules = [];

if ($course_id > 0) {
    $stmt = $conn->prepare(
        "SELECT id, nama_modul FROM modul WHERE id_praktikum = ? ORDER BY nama_modul ASC"
    );
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $modules[] = [
            'id' => $row['id'],
            'module_name' => htmlspecialchars($row['nama_modul'])
        ];
    }
    $stmt->close();
}

echo json_encode($modules);
$conn->close();
?>