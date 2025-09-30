<?php
require_once 'config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve') {
        $sql = "UPDATE exam_requests SET status = 'approved' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'อนุมัติเอกสารเรียบร้อยแล้ว';
        } else {
            $response['message'] = 'เกิดข้อผิดพลาดในการอนุมัติเอกสาร';
        }
    } elseif ($action === 'reject') {
        $reason = $_POST['reason'] ?? '';
        $sql = "UPDATE exam_requests SET status = 'rejected', reject_reason = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $reason, $id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'ไม่อนุมัติเอกสารเรียบร้อยแล้ว';
        } else {
            $response['message'] = 'เกิดข้อผิดพลาดในการไม่อนุมัติเอกสาร';
        }
    } else {
        $response['message'] = 'การดำเนินการไม่ถูกต้อง';
    }
} else {
    $response['message'] = 'Method not allowed';
}

header('Content-Type: application/json');
echo json_encode($response);