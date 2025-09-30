<?php
ob_start(); // Start output buffering
include 'config.php';

// All PHP logic goes here, before any HTML output
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $curriculum = $_POST['curriculum'];

    // Check for duplicate course code
    $check_sql = "SELECT course_code FROM courses WHERE course_code = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $course_code);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "รหัสวิชานี้มีอยู่ในระบบแล้ว";
    } else {
        // Add new course
        $sql = "INSERT INTO courses (course_code, course_name, curriculum) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $course_code, $course_name, $curriculum);
    
        if ($stmt->execute()) {
            echo "<script>
                    alert('บันทึกข้อมูลสำเร็จ');
                    window.location.href = 'courses.php';
                  </script>";
            exit();
        } else {
            $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    }
    
}

include 'admin_nav.php';

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มรายวิชา</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
        font-family: "Sarabun", sans-serif;
    }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">เพิ่มรายวิชาใหม่</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="course_code" class="form-label">รหัสวิชา</label>
                                <input type="text" class="form-control" id="course_code" name="course_code" 
                                       required maxlength="10" pattern="[A-Za-z0-9]+" 
                                       value="<?php echo isset($_POST['course_code']) ? htmlspecialchars($_POST['course_code']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="course_name" class="form-label">ชื่อวิชา</label>
                                <input type="text" class="form-control" id="course_name" name="course_name" 
                                       required maxlength="255"
                                       value="<?php echo isset($_POST['course_name']) ? htmlspecialchars($_POST['course_name']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="curriculum" class="form-label">หลักสูตร</label>
                                <input type="text" class="form-control" id="curriculum" name="curriculum" 
                                       required maxlength="255"
                                       value="<?php echo isset($_POST['curriculum']) ? htmlspecialchars($_POST['curriculum']) : ''; ?>">
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> บันทึก
                                </button>
                                <a href="courses.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ยกเลิก
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php
ob_end_flush(); // End and flush the output buffer
?>