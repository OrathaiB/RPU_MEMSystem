<?php

// เรียกใช้ session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบและดึง full_name จาก session
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : $_SESSION['username'];

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
        font-family: "Sarabun", sans-serif;
    }
    
    .navbar {
        padding: 1rem 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        background: linear-gradient(to right, #ffffff, #f8f9fa);
    }

    .navbar-brand {
        font-weight: bold;
        color: #2c3e50 !important;
        font-size: 1.3rem;
    }

    .nav-link {
        color: #34495e !important;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        background-color: #f1f3f4;
        color: #1a73e8 !important;
    }

    .nav-link.active {
        color: #1a73e8 !important;
        background-color: #e8f0fe;
    }

   

    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.5rem 1rem;
        background-color: #f8f9fa;
        border-radius: 25px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }


    .user-role {
        font-size: 0.9rem;
        color: #666;
        background-color: #e9ecef;
        padding: 0.2rem 0.8rem;
        border-radius: 15px;
    }

    .user-name {
        font-weight: 500;
        color: #2c3e50;
    }

    .logout-btn {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        background-color: #dc3545;
        color: white !important;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        font-size: 0.9rem;
    }

    .logout-btn:hover {
        background-color: #c82333;
        transform: translateY(-1px);
    }

    .navbar-toggler {
        border: none;
        padding: 0.5rem;
    }

    .navbar-toggler:focus {
        box-shadow: none;
    }

    @media (max-width: 991.98px) {
        .user-info {
            margin-top: 1rem;
            justify-content: center;
        }
    }
    </style>
   
</head>
<script>
    // ฟังก์ชันยืนยันการออกจากระบบ
    function confirmLogout() {
        var result = confirm("คุณต้องการออกจากระบบหรือไม่?");
        if (result) {
            // ใช้ fetch เพื่อทำการ logout โดยไม่ต้องเปลี่ยนหน้า
            fetch('logout_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    // เมื่อ logout สำเร็จ redirect ไปที่หน้า login.php
                    window.location.href = "login.php";
                }
            }).catch(error => {
                console.error('เกิดข้อผิดพลาดในการ logout:', error);
            });
        }
    }
    </script>

<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-book-reader me-2"></i>
                ระบบจัดการข้อสอบกลางภาค
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
                            href="user_index.php">
                            <i class="fas fa-home me-1"></i>หน้าแรก
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'formrequests.php' ? 'active' : ''; ?>"
                            href="formrequests.php">
                            <i class="fas fa-file-alt me-1"></i>สร้างแบบฟอร์มขออนุมัติ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'document_list.php' ? 'active' : ''; ?>"
                            href="user_document_list.php">
                            <i class="fas fa-list me-1"></i>รายการแบบฟอร์ม
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'approved_list.php' ? 'active' : ''; ?>"
                            href="approved_list.php">
                            <i class="fas fa-check-circle me-1"></i>เอกสารที่อนุมัติแล้ว
                        </a>
                    </li> -->
                    
                    <!-- <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>"
                            href="courses.php">
                            <i class="fas fa-graduation-cap me-1"></i>ข้อมูลหลักสูตร
                        </a>
                    </li> 
                     -->

            

                </ul>
                <div class="user-info">
                    <span class="user-role"><?php echo $role_display[$_SESSION['role']] ?? 'ผู้ใช้งาน'; ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
                    <button type="button" class="logout-btn" onclick="confirmLogout()">
                        <i class="fas fa-sign-out-alt me-1"></i>ออกจากระบบ
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>