<?php
require_once 'config.php';
include 'admin_nav.php';

// ตรวจสอบว่ามีการส่ง ID มาหรือไม่
if (!isset($_GET['id'])) {
    echo "<script>alert('ไม่พบ ID ผู้ใช้งาน'); window.location.href='index.php';</script>";
    exit();
}

$id = $_GET['id'];

// ดึงข้อมูลผู้ใช้งานจาก ID
$sql = "SELECT * FROM datalogin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('ไม่พบข้อมูลผู้ใช้งาน'); window.location.href='index.php';</script>";
    exit();
}

$user = $result->fetch_assoc();

// หากมีการส่งฟอร์มแก้ไข
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $gender = $_POST['gender'];
    $department = $_POST['department'];
    $position = trim($_POST['position']);
    $role = $_POST['role'];
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // ตรวจสอบว่าไม่มีช่องว่างเปล่า
    if (empty($username) || empty($full_name) || empty($gender) || empty($department) || empty($position) || empty($role) || empty($email) || empty($phone)) {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบทุกช่อง'); window.history.back();</script>";
        exit();
    }

    // ตรวจสอบว่ามีการเปลี่ยนรหัสผ่านหรือไม่
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            echo "<script>alert('รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน'); window.history.back();</script>";
            exit();
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE datalogin SET username=?, password=?, full_name=?, gender=?, department=?, position=?, role=?, email=?, phone=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssi", $username, $password, $full_name, $gender, $department, $position, $role, $email, $phone, $id);
    } else {
        $sql = "UPDATE datalogin SET username=?, full_name=?, gender=?, department=?, position=?, role=?, email=?, phone=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $username, $full_name, $gender, $department, $position, $role, $email, $phone, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('แก้ไขข้อมูลผู้ใช้งานสำเร็จ'); window.location.href='userlist.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด: " . $stmt->error . "');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลผู้ใช้งาน</title>
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
                <h2 class="mb-0">แก้ไขข้อมูลผู้ใช้งาน</h2>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">ชื่อผู้ใช้งาน</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เพศ</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="ชาย" id="gender_male" name="gender" <?php echo ($user['gender'] == 'ชาย') ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="gender_male">ชาย</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="หญิง" id="gender_female" name="gender" <?php echo ($user['gender'] == 'หญิง') ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="gender_female">หญิง</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">สาขาวิชา</label>
                        <select class="form-select" id="department" name="department" required>
                            <option value="">เลือกสาขาวิชา</option>
                            <option value="1" <?php echo ($user['department'] == '1') ? 'selected' : ''; ?>>สาขาวิชาเทคโนโลยีดิจิทัลเพื่อธุรกิจ</option>
                            <option value="2" <?php echo ($user['department'] == '2') ? 'selected' : ''; ?>>สาขาวิชาเทคโนโลยีดิจิทัล</option>
                            <option value="3" <?php echo ($user['department'] == '3') ? 'selected' : ''; ?>>สาขาวิชาการตลาดดิจิทัล</option>
                            <option value="4" <?php echo ($user['department'] == '4') ? 'selected' : ''; ?>>สาขาวิชาบัญชี</option>
                            <option value="5" <?php echo ($user['department'] == '5') ? 'selected' : ''; ?>>สาขาวิชาการจัดการ</option>
                            <option value="6" <?php echo ($user['department'] == '6') ? 'selected' : ''; ?>>ฝ่ายวิชาการ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">ตำแหน่ง</label>
                        <input type="text" class="form-control" id="position" name="position" value="<?php echo $user['position']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สิทธิ์การใช้งาน</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="admin" id="role_admin" name="role" <?php echo ($user['role'] == 'admin') ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="role_admin">ผู้ดูแลระบบ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="teacher" id="role_teacher" name="role" <?php echo ($user['role'] == 'teacher') ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="role_teacher">อาจารย์ผู้สอน</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="academic" id="role_academic" name="role" <?php echo ($user['role'] == 'academic') ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="role_academic">ฝ่ายวิชาการ</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>" pattern="[0-9]+" title="กรุณากรอกเฉพาะตัวเลข" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">รหัสผ่าน (เว้นว่างหากไม่ต้องการเปลี่ยน)</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}" title="รหัสผ่านต้องมีอย่างน้อย 8 ตัว และประกอบด้วยตัวอักษรพิมพ์ใหญ่ พิมพ์เล็ก ตัวเลข และสัญลักษณ์พิเศษ">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button type="submit" class="btn btn-success me-md-2">บันทึกการแก้ไข</button>
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
</body>
</html>