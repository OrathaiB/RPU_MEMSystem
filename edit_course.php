<?php
include 'config.php';
include 'admin_nav.php';
ob_start();


// ฟังก์ชันลบรายวิชา
if (isset($_GET['delete'])) {
    $course_code = $_GET['delete'];
    $delete_sql = "DELETE FROM courses WHERE course_code = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("s", $course_code);

    if ($delete_stmt->execute()) {
        // หากลบสำเร็จให้แสดงข้อความแจ้งเตือนและเปลี่ยนหน้า
        echo "<script>
                alert('ทำการลบข้อมูลรายวิชาเรียบร้อยแล้ว');
                window.location.href = 'courses.php';
              </script>";
        exit();
    } else {
        echo "เกิดข้อผิดพลาดในการลบข้อมูล";
    }
    
}

// ตรวจสอบว่ามีการส่ง course_code มาหรือไม่
if (!isset($_GET['code'])) {
    header("Location: courses.php");
    exit();
}

$course_code = $_GET['code'];

// ดึงข้อมูลรายวิชาที่ต้องการแก้ไข
$sql = "SELECT * FROM courses WHERE course_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $course_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: courses.php");
    exit();
}
// ตรวจสอบว่ามีการส่งข้อมูลฟอร์มมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'];
    $curriculum = $_POST['curriculum'];

    // อัปเดตข้อมูลรายวิชาในฐานข้อมูล
    $update_sql = "UPDATE courses SET course_name = ?, curriculum = ? WHERE course_code = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sss", $course_name, $curriculum, $course_code);

    if ($update_stmt->execute()) {
        // หากอัปเดตสำเร็จให้แสดงข้อความแจ้งเตือนและเปลี่ยนหน้า
        echo "<script>
                alert('แก้ไขข้อมูลรายวิชาสำเร็จ');
                window.location.href = 'courses.php';
              </script>";
        exit();
    } else {
        $error = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล";
    }
}



$course = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขรายวิชา</title>
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
                        <h3 class="card-title">แก้ไขรายวิชา: <?php echo htmlspecialchars($course_code); ?></h3>
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
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($course_code); ?>" 
                                       disabled>
                            </div>

                            <div class="mb-3">
                                <label for="course_name" class="form-label">ชื่อวิชา</label>
                                <input type="text" class="form-control" id="course_name" name="course_name" 
                                       required maxlength="255"
                                       value="<?php echo htmlspecialchars($course['course_name']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="curriculum" class="form-label">หลักสูตร</label>
                                <input type="text" class="form-control" id="curriculum" name="curriculum" 
                                       required maxlength="255"
                                       value="<?php echo htmlspecialchars($course['curriculum']); ?>">
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> บันทึกการแก้ไข
                                </button>

                                <!-- <a href="?delete=<?php echo urlencode($course['course_code']); ?>" 
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('ยืนยันการลบรายวิชานี้?');">
                                    <i class="fas fa-trash"></i> ลบข้อมูลรายวิชา
                                </a> -->

                                <a href="?delete=<?php echo urlencode($course['course_code']); ?>" 
   class="btn btn-danger btn-sm"
   onclick="return confirm('ยืนยันการลบรายวิชานี้?');">
   <i class="fas fa-trash"></i> ลบ
</a>




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