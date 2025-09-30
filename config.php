
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "exam_management";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// // ตั้งค่า charset เป็น utf8
// $conn->set_charset("utf8");
?>