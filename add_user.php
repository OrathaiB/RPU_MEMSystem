<?php
require_once 'config.php';
include 'admin_nav.php';

// Function to check if username already exists
function usernameExists($conn, $username) {
    $stmt = $conn->prepare("SELECT id FROM datalogin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// ตรวจสอบว่ามีการส่งข้อมูลผ่าน POST หรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $gender = $_POST['gender'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password
    $password_regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!preg_match($password_regex, $password)) {
        echo "<script>alert('รหัสผ่านต้องมีอย่างน้อย 8 ตัว และประกอบด้วยตัวอักษรพิมพ์ใหญ่ พิมพ์เล็ก ตัวเลข และสัญลักษณ์พิเศษ'); window.history.back();</script>";
        exit;
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน'); window.history.back();</script>";
        exit;
    }

    // Check if username already exists
    if (usernameExists($conn, $username)) {
        echo "<script>alert('ชื่อผู้ใช้งานนี้มีอยู่แล้ว กรุณาใช้ชื่อผู้ใช้งานอื่น'); window.history.back();</script>";
        exit;
    }

    $password = password_hash($password, PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่าน

    // เตรียมคำสั่ง SQL สำหรับการเพิ่มข้อมูล
    $sql = "INSERT INTO datalogin (username, full_name, gender, department, position, role, email, phone, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // เตรียมและดำเนินการคำสั่ง SQL
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $username, $full_name, $gender, $department, $position, $role, $email, $phone, $password);
    
    // ตรวจสอบผลการดำเนินการและแสดงข้อความ
    if ($stmt->execute()) {
        echo "<script>alert('เพิ่มผู้ใช้งานสำเร็จ'); window.location.href='userlist.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด: " . $stmt->error . "');</script>";
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มผู้ใช้งานใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
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
    <div class="container mt-5">
        <div class="card">
            
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0">เพิ่มผู้ใช้งานใหม่</h2>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">ชื่อผู้ใช้งาน</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เพศ</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="ชาย" id="gender_male" name="gender" required>
                            <label class="form-check-label" for="gender_male">ชาย</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="หญิง" id="gender_female" name="gender" required>
                            <label class="form-check-label" for="gender_female">หญิง</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">สาขาวิชา</label>
                        <select class="form-select" id="department" name="department" required>
                            <option value="">เลือกสาขาวิชา</option>
                            <option value="1">สาขาวิชาเทคโนโลยีดิจิทัลเพื่อธุรกิจ</option>
                            <option value="2">สาขาวิชาเทคโนโลยีดิจิทัล</option>
                            <option value="3">สาขาวิชาการตลาดดิจิทัล</option>
                            <option value="4">สาขาวิชาบัญชี</option>
                            <option value="5">สาขาวิชาการจัดการ</option>
                            <option value="6">ฝ่ายวิชาการ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">ตำแหน่ง</label>
                        <input type="text" class="form-control" id="position" name="position" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สิทธิ์การใช้งาน</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="admin" id="role_admin" name="role" required>
                            <label class="form-check-label" for="role_admin">ผู้ดูแลระบบ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="teacher" id="role_teacher" name="role" required>
                            <label class="form-check-label" for="role_teacher">อาจารย์ผู้สอน</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="academic" id="role_academic" name="role" required>
                            <label class="form-check-label" for="role_academic">ฝ่ายวิชาการ</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" class="form-control" id="phone" name="phone" pattern="[0-9]+" title="กรุณากรอกเฉพาะตัวเลข">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}" title="รหัสผ่านต้องมีอย่างน้อย 8 ตัว และประกอบด้วยตัวอักษรพิมพ์ใหญ่ พิมพ์เล็ก ตัวเลข และสัญลักษณ์พิเศษ">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button type="submit" class="btn btn-success me-md-2">เพิ่มผู้ใช้งาน</button>
                        <button type="reset" class="btn btn-secondary me-md-2">ล้างข้อมูล</button>
                        <a href="userlist.php" class="btn btn-primary">ย้อนกลับ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            const icon = button.querySelector('i');

            button.addEventListener('click', function () {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        }

        togglePasswordVisibility('password', 'togglePassword');
        togglePasswordVisibility('confirm_password', 'toggleConfirmPassword');
    </script>
    <script>
function goBack() {
            window.location.href = 'user_index.php';
        }
        </script>
</body>
</html>