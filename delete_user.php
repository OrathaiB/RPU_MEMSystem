<?php
// เชื่อมต่อกับฐานข้อมูล
require_once 'config.php';
include 'nav.php';

// ตรวจสอบว่ามีการส่ง ID มาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>
            alert('ไม่พบรหัสผู้ใช้งาน');
            window.location.href = 'user_list.php';
          </script>";
    exit();
}

// รับค่า ID จาก URL
$id = $_GET['id'];

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// เตรียมคำสั่ง SQL สำหรับลบข้อมูล
$sql = "DELETE FROM datalogin WHERE id = ?";

// เตรียมและ execute คำสั่ง SQL แบบ prepared statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

// ทำการลบข้อมูล
if ($stmt->execute()) {
    echo "<script>
            alert('ลบข้อมูลผู้ใช้งานเรียบร้อยแล้ว');
            window.location.href = 'userlist.php';
          </script>";
} else {
    echo "<script>
            alert('เกิดข้อผิดพลาดในการลบข้อมูล: " . $stmt->error . "');
            window.location.href = 'userlist.php';
          </script>";
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>