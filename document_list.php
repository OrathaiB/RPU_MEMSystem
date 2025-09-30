<?php
session_start();
include 'config.php';
include 'admin_nav.php';

// รับค่า status filter เป็น array
$status_filters = isset($_GET['status']) ? $_GET['status'] : [];

$sql = "SELECT id, semester, course_code, course_name, curriculum, major, 
               exam_date, status_doc, manuscript, numberofsets, examiner_name
        FROM exam_requests";

$where = [];

// ฟังก์ชันสำหรับ escape string เพื่อป้องกัน SQL injection
function escape_string($conn, $string) {
    return $conn->real_escape_string($string);
}

// การค้นหา
$search = isset($_GET['search']) ? escape_string($conn, $_GET['search']) : '';
if($search != '') {
    $where[] = "(id LIKE '%$search%'
               OR course_code LIKE '%$search%' 
               OR course_name LIKE '%$search%' 
               OR curriculum LIKE '%$search%'
               OR major LIKE '%$search%'
               OR exam_date LIKE '%$search%'
               OR status_doc LIKE '%$search%'
               OR manuscript LIKE '%$search%'
               OR examiner_name LIKE '%$search%')";
}

// เพิ่ม status filter เข้าไปใน WHERE clause
if (!empty($status_filters)) {
    $escaped_statuses = array_map(function($status) use ($conn) {
        return "'" . escape_string($conn, $status) . "'";
    }, $status_filters);
    $where[] = "status_doc IN (" . implode(",", $escaped_statuses) . ")";
}

// รวม WHERE clauses
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id ASC";

$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการเอกสาร</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Sarabun", sans-serif;
        }
        
        .table-responsive {
            max-height: 70vh;
            overflow-y: auto;
        }
        .table th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
        }
        .status-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .status-filter label {
            display: flex;
            align-items: center;
            margin-bottom: 0;
        }

        .status-filter input[type="checkbox"] {
            margin-right: 8px; 
        }
    </style>
</head>

<body>
    
    <div class="container-fluid mt-5">

    <div>
            <button onclick="goBack()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </button>
        </div>


        <h2 class="mb-4"><center>รายการเอกสารทั้งหมด</center></h2>

        <!-- ส่วนค้นหาและกรองข้อมูล -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="ค้นหาข้อมูลแบบฟอร์มข้อสอบกลางภาค" 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-6">
                        <div class="status-filter">
                            <label><input type="checkbox" name="status[]" value="รออนุมัติ" <?php echo in_array('รออนุมัติ', $status_filters) ? 'checked' : ''; ?>>   รออนุมัติ</label>
                            <label><input type="checkbox" name="status[]" value="อนุมัติแล้ว" <?php echo in_array('อนุมัติแล้ว', $status_filters) ? 'checked' : ''; ?>>   อนุมัติแล้ว</label>
                            <label><input type="checkbox" name="status[]" value="ไม่อนุมัติ" <?php echo in_array('ไม่อนุมัติ', $status_filters) ? 'checked' : ''; ?>>   ไม่อนุมัติ</label>
                            <label><input type="checkbox" name="status[]" value="จัดพิมพ์แล้ว" <?php echo in_array('จัดพิมพ์แล้ว', $status_filters) ? 'checked' : ''; ?>>   จัดพิมพ์แล้ว</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> ค้นหาและกรอง
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>ภาคเรียน</th>
                        <th>รหัสวิชา</th>
                        <th>ชื่อวิชา</th>
                        <th>หลักสูตร</th>
                        <th>สาขาวิชา</th>
                        <th>วันที่สอบ</th>
                        <th>จำนวนหน้า</th>
                        <th>จำนวนชุด</th>
                        <th>ผู้ออกข้อสอบ</th>
                        <th>สถานะเอกสาร</th>
                        <th>ตรวจสอบเอกสาร</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["semester"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["course_code"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["course_name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["curriculum"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["major"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["exam_date"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["manuscript"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["numberofsets"]) . "</td>";                            
                            echo "<td>" . htmlspecialchars($row["examiner_name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["status_doc"]) . "</td>";
                            echo "<td>";
                            echo "<a href='document_details.php?id=" . htmlspecialchars($row["id"]) . "' class='btn btn-primary btn-sm'>รายละเอียดแบบฟอร์ม</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='12' class='text-center'>ไม่พบข้อมูลเอกสาร</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
function goBack() {
            window.location.href = 'admin_index.php';
        }
        </script>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>