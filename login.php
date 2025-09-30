<?php
require_once 'config.php';
session_start();

// ฟังก์ชันสำหรับล็อคบัญชี
function lockAccount($conn, $username) {
    $sql = "UPDATE datalogin SET is_locked = 1 WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
}

// ฟังก์ชันสำหรับรีเซ็ตจำนวนครั้งที่ล็อกอินผิด
function resetFailedAttempts($conn, $username) {
    $sql = "UPDATE datalogin SET failed_attempts = 0 WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
}

// ฟังก์ชันสำหรับเพิ่มจำนวนครั้งที่ล็อกอินผิด
function incrementFailedAttempts($conn, $username) {
    $sql = "UPDATE datalogin SET failed_attempts = failed_attempts + 1 WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
}

// ฟังก์ชันสำหรับดึงข้อมูลการล็อกอินผิด
function getFailedAttempts($conn, $username) {
    $sql = "SELECT failed_attempts, is_locked FROM datalogin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// ตรวจสอบการส่งข้อมูลผ่าน POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM datalogin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $attempts = getFailedAttempts($conn, $username);

        if ($attempts['is_locked']) {
            echo json_encode(["success" => false, "message" => "บัญชีของคุณถูกล็อค กรุณาติดต่อแอดมิน"]);
            exit();
        }

        // ใช้ password_verify() เพื่อตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            resetFailedAttempts($conn, $username);
            
            // กำหนดหน้าที่จะ redirect ตาม role
            $redirect_page = '';
            switch ($user['role']) {
                case 'admin':
                    $redirect_page = 'admin_index.php';
                    break;
                case 'teacher':
                    $redirect_page = 'user_index.php';
                    break;
                case 'academic':
                    $redirect_page = 'academic_index.php';
                    break;
                default:
                    $redirect_page = 'index.php';  // หน้าเริ่มต้นถ้าไม่มี role ที่กำหนด
            }
            
            // ส่งข้อมูลกลับเป็น JSON พร้อมกับ URL ที่จะ redirect
            echo json_encode([
                "success" => true, 
                "message" => "เข้าสู่ระบบสำเร็จ", 
                "redirect" => $redirect_page
            ]);
            exit();
        } else {
            // เพิ่มจำนวนครั้งที่ล็อกอินผิด
            incrementFailedAttempts($conn, $username);
            $attempts = getFailedAttempts($conn, $username);
            
            if ($attempts['failed_attempts'] >= 3) {
                lockAccount($conn, $username);
                echo json_encode(["success" => false, "message" => "บัญชีของคุณถูกล็อคเนื่องจากล็อกอินผิดหลายครั้ง กรุณาติดต่อแอดมิน"]);
            } else {
                echo json_encode(["success" => false, "message" => "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง"]);
            }
            exit();
        }
    } else {
        echo json_encode(["success" => false, "message" => "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง"]);
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบจัดการข้อสอบกลางภาค</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
        font-family: "Sarabun", sans-serif;
    }

    .login-container {
        max-width: 400px;
        margin: 50px auto;
    }

    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #fd464a;
        border-radius: 15px 15px 0 0;
        padding: 15px;
    }

    .card-body {
        padding: 30px;
    }

    .form-control {
        border-radius: 10px;
    }

    .btn-primary {
        background-color: #5d8d73;
        border: none;
        border-radius: 10px;
        padding: 10px;
        font-weight: bold;
    }

    .btn-primary:hover {
        background-color: #63cf32;
    }

    .login-title {
        color: #e95c00;
        font-weight: bold;
        margin-bottom: 30px;
    }

    .input-group-text {
        background-color: transparent;
        border-right: none;
    }

    .form-control {
        border-left: none;
    }

    .form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }
    </style>
</head>

<body>


    <div class="container login-container">
    <br>
<h3 class="login-title text-center">ระบบจัดการข้อสอบกลางภาค</h3>
<br>
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0 text-white text-center">เข้าสู่ระบบ</h4>
            </div>
            <div class="card-body">
                <form id="loginForm" method="post" action="login.php">
                    <div class="mb-3">
                        <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required
                                autocomplete="username" placeholder="ชื่อผู้ใช้">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required
                                autocomplete="current-password" placeholder="รหัสผ่าน">
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">ยืนยันข้อมูล</button>
                    </div>
                </form>
            </div>

        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'login.php',
                type: 'post',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        // ใช้ URL ที่ได้รับจากเซิร์ฟเวอร์เพื่อ redirect
                        window.location.href = response.redirect;
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                }
            });
        });
    });
    </script>
</body>

</html>