<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = $_POST['form_id'];
    
    // ดึงข้อมูลจาก $_POST และทำความสะอาดข้อมูล
    $term = mysqli_real_escape_string($conn, $_POST['term']);
    $yeart = mysqli_real_escape_string($conn, $_POST['yeart']);
    $semester = $term . '/' . $yeart;
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $curriculum = mysqli_real_escape_string($conn, $_POST['curriculum']);
    $major = mysqli_real_escape_string($conn, $_POST['major']);
    $manuscript = mysqli_real_escape_string($conn, $_POST['manuscript']);
    $numberofsets = mysqli_real_escape_string($conn, $_POST['numberofsets']);
    $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date']);
    $exam_time_start = mysqli_real_escape_string($conn, $_POST['exam_time_start']);
    $exam_time_end = mysqli_real_escape_string($conn, $_POST['exam_time_end']);
    $total_exam_time = mysqli_real_escape_string($conn, $_POST['total_exam_time']);
    $examiner_names = isset($_POST['examiner_names']) ? implode(',', $_POST['examiner_names']) : '';
    $exam_methods = mysqli_real_escape_string($conn, $_POST['exam_methods']);
    
    // แยกการจัดการ other_form และ other_form_count
    $other_form_desc = isset($_POST['other_form_desc']) ? $_POST['other_form_desc'] : [];
    $other_form_count = isset($_POST['other_form_count']) ? $_POST['other_form_count'] : [];
    $other_form = [];
    for ($i = 0; $i < count($other_form_desc); $i++) {
        $other_form[] = mysqli_real_escape_string($conn, $other_form_desc[$i]) . ': ' . mysqli_real_escape_string($conn, $other_form_count[$i]);
    }
    $other_form = implode('/', $other_form);

    $other_form = mysqli_real_escape_string($conn, $_POST['other_form']);
    $other_form_count = isset($_POST['other_form_count']) ? intval($_POST['other_form_count']) : 0;
    
    $allowed_equipment = mysqli_real_escape_string($conn, $_POST['allowed_equipment']);
    $additional_details = mysqli_real_escape_string($conn, $_POST['additional_details']);

    // เริ่ม transaction
    $conn->begin_transaction();

    try {
        // อัปเดตข้อมูลในฐานข้อมูล
        $sql = "UPDATE exam_requests SET 
                semester = ?, course_code = ?, course_name = ?, curriculum = ?, major = ?,
                manuscript = ?, numberofsets = ?, exam_date = ?, startexam_time = ?,
                endexam_time = ?, totalexam_time = ?, examiner_name = ?, exam_methods = ?,
                other_form = ?, allowed_equipment = ?, additional_details = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $stmt->bind_param("ssssssssssssssssi", $semester, $course_code, $course_name, $curriculum, $major,
                          $manuscript, $numberofsets, $exam_date, $exam_time_start,
                          $exam_time_end, $total_exam_time, $examiner_names, $exam_methods,
                          $other_form, $allowed_equipment, $additional_details, $form_id);

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        // จัดการกับการอัปโหลดไฟล์ใหม่ (ถ้ามี)
        if (isset($_FILES['new_exam_file']) && $_FILES['new_exam_file']['error'] == 0) {
            $file_name = $_FILES['new_exam_file']['name'];
            $file_tmp = $_FILES['new_exam_file']['tmp_name'];
            $file_path = "uploads/" . $file_name;

            if (!move_uploaded_file($file_tmp, $file_path)) {
                throw new Exception('Failed to upload file');
            }

            $sql_update_file = "UPDATE exam_requests SET file_name = ?, file_path = ? WHERE id = ?";
            $stmt_file = $conn->prepare($sql_update_file);
            if ($stmt_file === false) {
                throw new Exception('Prepare failed for file update: ' . $conn->error);
            }
            $stmt_file->bind_param("ssi", $file_name, $file_path, $form_id);
            if (!$stmt_file->execute()) {
                throw new Exception('Execute failed for file update: ' . $stmt_file->error);
            }
        }

        // ลบไฟล์เก่า (ถ้ามีการระบุ)
        if (isset($_POST['delete_old_file']) && $_POST['delete_old_file'] == 'true') {
            $sql_get_old_file = "SELECT file_path FROM exam_requests WHERE id = ?";
            $stmt_get_old_file = $conn->prepare($sql_get_old_file);
            $stmt_get_old_file->bind_param("i", $form_id);
            $stmt_get_old_file->execute();
            $result = $stmt_get_old_file->get_result();
            $old_file = $result->fetch_assoc();
            
            if ($old_file && file_exists($old_file['file_path'])) {
                unlink($old_file['file_path']);
            }

            $sql_remove_file = "UPDATE exam_requests SET file_name = NULL, file_path = NULL WHERE id = ?";
            $stmt_remove_file = $conn->prepare($sql_remove_file);
            $stmt_remove_file->bind_param("i", $form_id);
            if (!$stmt_remove_file->execute()) {
                throw new Exception('Failed to remove old file reference from database');
            }
        }

        // ยืนยัน transaction
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'บันทึกการแก้ไขเรียบร้อยแล้ว']);
    } catch (Exception $e) {
        // ถ้าเกิดข้อผิดพลาด, ย้อนกลับ transaction
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>