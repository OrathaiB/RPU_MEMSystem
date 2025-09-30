<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

require_once 'config.php';

// Start the session (if not already started)
session_start();

// Check database connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

$response = array('success' => false, 'message' => '');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Check and sanitize input data
    $fields = ['semester', 'course_code', 'course_name', 'curriculum', 'major', 'manuscript', 'numberofsets', 'exam_date', 'exam_time_start', 'exam_time_end', 'total_exam_time', 'exam_methods', 'examiner_name'];
    $sanitized_data = [];

    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            $errors[] = "กรุณากรอก $field";
        } else {
            $sanitized_data[$field] = sanitize_input($_POST[$field]);
        }
    }

    // Get the user's id from the session
    if (!isset($_SESSION['user_id'])) {
        $errors[] = "ไม่พบข้อมูลผู้ใช้ กรุณาเข้าสู่ระบบใหม่";
    } else {
        $sanitized_data['examiner_id'] = $_SESSION['user_id'];
    }

    // Convert number of sets to text
    $sanitized_data['numberofsets'];

    // Check and set default values for allowed_equipment and additional_details
    $sanitized_data['allowed_equipment'] = isset($_POST['allowed_equipment']) && $_POST['allowed_equipment'] !== '' 
        ? sanitize_input($_POST['allowed_equipment'])
        : "ไม่อนุมัติให้นำอุปกรณ์อื่นๆ เครื่องคำนวณหรือเอกสารเข้าห้องสอบ";

    $sanitized_data['additional_details'] = isset($_POST['additional_details']) && $_POST['additional_details'] !== ''
        ? sanitize_input($_POST['additional_details'])
        : "ไม่ระบุรายละเอียดอื่นๆเพิ่มเติม";

    // Check main file upload
    if (!isset($_FILES['exam_file']) || $_FILES['exam_file']['error'] != UPLOAD_ERR_OK) {
        $errors[] = "Exam file is required";
    } else {
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['exam_file']['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only PDF and Word documents are allowed.";
        } elseif ($_FILES['exam_file']['size'] > $max_size) {
            $errors[] = "File size exceeds the limit of 5MB.";
        }
    }

    // Check other form file uploads
    $other_form_data = [];
    if (isset($_POST['other_form_desc']) && is_array($_POST['other_form_desc'])) {
        for ($i = 0; $i < count($_POST['other_form_desc']); $i++) {
            if (isset($_FILES['other_form_file']['name'][$i]) && $_FILES['other_form_file']['error'][$i] == UPLOAD_ERR_OK) {
                $file_name = basename($_FILES['other_form_file']['name'][$i]);
                $file_tmp = $_FILES['other_form_file']['tmp_name'][$i];
                $file_dest = 'other_forms/' . $file_name;

                $other_form_data[] = [
                    'desc' => sanitize_input($_POST['other_form_desc'][$i]),
                    'count' => sanitize_input($_POST['other_form_count'][$i]),
                    'file_name' => $file_name,
                    'file_dest' => $file_dest,
                    'file_tmp' => $file_tmp
                ];
            }
        }
    }

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        $conn->begin_transaction();
    
        try {
            // Prepare SQL statement for inserting data
            $stmt = $conn->prepare("INSERT INTO exam_requests (semester, course_code, course_name, curriculum, major, manuscript, numberofsets, exam_date, startexam_time, endexam_time, totalexam_time, exam_methods, allowed_equipment, additional_details, examiner_name, examiner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
            if (!$stmt) {
                throw new Exception("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: (" . $conn->errno . ") " . $conn->error);
            }
    
            $stmt->bind_param("sssssssssssssssi", 
                $sanitized_data['semester'],
                $sanitized_data['course_code'],
                $sanitized_data['course_name'],
                $sanitized_data['curriculum'],
                $sanitized_data['major'],
                $sanitized_data['manuscript'],
                $sanitized_data['numberofsets'],
                $sanitized_data['exam_date'],
                $sanitized_data['exam_time_start'],
                $sanitized_data['exam_time_end'],
                $sanitized_data['total_exam_time'],
                $sanitized_data['exam_methods'],
                $sanitized_data['allowed_equipment'],
                $sanitized_data['additional_details'],
                $sanitized_data['examiner_name'],
                $sanitized_data['examiner_id']
            );
    
            if (!$stmt->execute()) {
                throw new Exception("ERROR: Could not execute query: " . $stmt->error);
            }
    
            // Get the newly created ID
            $new_id = $conn->insert_id;
    
            // Handle main file upload
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $original_file_name = basename($_FILES['exam_file']['name']);
            $new_file_name = $new_id . '_' . $original_file_name;
            $upload_file = $upload_dir . $new_file_name;
            
            if (!move_uploaded_file($_FILES['exam_file']['tmp_name'], $upload_file)) {
                throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดไฟล์");
            }
    
            // Handle other form file uploads
            $other_form_files = [];
            foreach ($other_form_data as &$other_form) {
                if (!file_exists('other_forms/')) {
                    mkdir('other_forms/', 0777, true);
                }
    
                $original_other_file_name = $other_form['file_name'];
                $new_other_file_name = $new_id . '_other_' . $original_other_file_name;
                $other_form['file_dest'] = 'other_forms/' . $new_other_file_name;
    
                if (!move_uploaded_file($other_form['file_tmp'], $other_form['file_dest'])) {
                    throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดไฟล์แบบฟอร์มอื่นๆ");
                }
    
                $other_form_files[] = $other_form['file_dest'];  // Store file path instead of file name
            }
    
            $other_form_files_string = implode(", ", $other_form_files);
            $other_form_descs = implode(", ", array_column($other_form_data, 'desc'));
            $other_form_counts = implode(", ", array_column($other_form_data, 'count'));
    
            // Update file information
            $update_stmt = $conn->prepare("UPDATE exam_requests SET file_name = ?, file_path = ?, other_form = ?, other_form_count = ?, other_form_file = ? WHERE id = ?");
            $update_stmt->bind_param("sssssi", $new_file_name, $upload_file, $other_form_descs, $other_form_counts, $other_form_files_string, $new_id);
    
            if (!$update_stmt->execute()) {
                throw new Exception("ERROR: Could not update file information: " . $update_stmt->error);
            }
    
            // Commit all changes
            $conn->commit();
            $response['success'] = true;
            $response['message'] = "บันทึกข้อมูลทั้งหมดเรียบร้อยแล้ว";
        } catch (Exception $e) {
            // Rollback if an error occurred
            $conn->rollback();
            $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    } else {
        $response['message'] = "พบข้อผิดพลาด:<br>" . implode("<br>", $errors);
    }
    
} else {
    $response['message'] = "ไม่มีข้อมูลถูกส่งมา หรือไม่ได้ใช้วิธี POST";
}

header('Content-Type: application/json');
echo json_encode($response);
$conn->close();
?>