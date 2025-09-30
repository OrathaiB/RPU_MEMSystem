<?php
session_start();

// ตรวจสอบว่าผู้ใช้ยืนยันการออกจากระบบหรือไม่
if (isset($_POST['confirm_logout'])) {
    // ลบข้อมูล session ทั้งหมด
    session_unset();
    session_destroy();
    
    // เปลี่ยนเส้นทางไปยังหน้า login.php
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script>
        // ฟังก์ชันเพื่อยืนยันการออกจากระบบ
        function confirmLogout() {
            var result = confirm("คุณต้องการออกจากระบบหรือไม่?");
            if (result) {
                // ส่งฟอร์มเพื่อยืนยันการ logout
                document.getElementById("logoutForm").submit();
            }
        }
    </script>
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

    <h1>ยืนยันการออกจากระบบ</h1>
    <form id="logoutForm" method="POST" action="logout.php">
        <input type="hidden" name="confirm_logout" value="1">
        <button type="button" onclick="confirmLogout()">ออกจากระบบ</button>
    </form>

</body>
</html>
