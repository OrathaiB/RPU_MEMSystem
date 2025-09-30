<?php
// เชื่อมต่อกับฐานข้อมูล
require_once 'config.php';
include 'admin_nav.php';
// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// คำสั่ง SQL เพื่อดึงข้อมูลผู้ใช้งานทั้งหมด
$sql = "SELECT * FROM datalogin";
$result = $conn->query($sql);



?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อผู้ใช้งาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h2 class="mb-4">รายชื่อผู้ใช้งาน</h2>
        <div>
            <button onclick="goBack()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </button>
        
        
            
            <a href="add_user.php" class="btn mb-3" style="background-color: orange; color: white; float: right;">เพิ่มผู้ใช้งาน</a>


        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>รหัสผู้ใช้งาน</th>
                    <th>ชื่อผู้ใช้งาน</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>เพศ</th>
                    <th>แผนก</th>
                    <th>ตำแหน่ง</th>
                    <th>สิทธิ์การใช้งาน</th>
                    <th>อีเมล</th>
                    <th>เบอร์โทรศัพท์</th>
                    <th>การดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["username"] . "</td>";
                        echo "<td>" . $row["full_name"] . "</td>";
                        echo "<td>" . $row["gender"] . "</td>";
                        echo "<td>" . $row["department"] . "</td>";
                        echo "<td>" . $row["position"] . "</td>";
                        echo "<td>" . $row["role"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>" . $row["phone"] . "</td>";
                        echo "<td>
                                <a href='edit_user.php?id=" . $row["id"] . "' class='btn btn-sm btn-warning'>แก้ไข</a>
                                <a href='delete_user.php?id=" . $row["id"] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"คุณแน่ใจหรือไม่ที่จะลบผู้ใช้งานนี้?\")'>ลบ</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='11' class='text-center'>ไม่พบข้อมูลผู้ใช้งาน</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
function goBack() {
            window.location.href = 'admin_index.php';
        }
        </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>