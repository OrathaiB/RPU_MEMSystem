<?php
require_once 'config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileType = $_POST['file_type'];
    $formId = $_POST['form_id'];
    $fileIndex = isset($_POST['file_index']) ? $_POST['file_index'] : null;

    // ดึงข้อมูลฟอร์มจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM exam_requests WHERE id = ?");
    $stmt->bind_param("i", $formId);
    $stmt->execute();
    $result = $stmt->get_result();
    $form = $result->fetch_assoc();

    if ($form) {
        if ($fileType === 'exam') {
            // ลบไฟล์ข้อสอบหลัก
            if (unlink($form['file_path'])) {
                $updateStmt = $conn->prepare("UPDATE exam_requests SET file_path = NULL, file_name = NULL WHERE id = ?");
                $updateStmt->bind_param("i", $formId);
                if ($updateStmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'ลบไฟล์สำเร็จ';
                } else {
                    $response['message'] = 'เกิดข้อผิดพลาดในการอัปเดตฐานข้อมูล';
                }
                $updateStmt->close();
            } else {
                $response['message'] = 'เกิดข้อผิดพลาดในการลบไฟล์';
            }
        } elseif ($fileType === 'other_form' && $fileIndex !== null) {
            // ลบไฟล์แบบฟอร์มอื่นๆ
            $otherFormFiles = explode('/', $form['other_form_file']);
            if (isset($otherFormFiles[$fileIndex]) && unlink($otherFormFiles[$fileIndex])) {
                unset($otherFormFiles[$fileIndex]);
                $newOtherFormFiles = implode('/', $otherFormFiles);
                $updateStmt = $conn->prepare("UPDATE exam_requests SET other_form_file = ? WHERE id = ?");
                $updateStmt->bind_param("si", $newOtherFormFiles, $formId);
                if ($updateStmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'ลบไฟล์สำเร็จ';
                } else {
                    $response['message'] = 'เกิดข้อผิดพลาดในการอัปเดตฐานข้อมูล';
                }
                $updateStmt->close();
            } else {
                $response['message'] = 'เกิดข้อผิดพลาดในการลบไฟล์';
            }
        } else {
            $response['message'] = 'ประเภทไฟล์ไม่ถูกต้อง';
        }
    } else {
        $response['message'] = 'ไม่พบข้อมูลฟอร์ม';
    }

    $stmt->close();
} else {
    $response['message'] = 'คำขอไม่ถูกต้อง';
}

echo json_encode($response);
?>