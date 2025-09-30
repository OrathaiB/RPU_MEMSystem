<?php
session_start();
include 'config.php';
include 'academic_nav.php';

// SQL query to select only unapproved documents
$sql = "SELECT id, semester, course_code, course_name, curriculum, major, 
               exam_date, status_doc, manuscript, numberofsets, examiner_name
        FROM exam_requests
        WHERE status_doc = 'อนุมัติแล้ว'
        ORDER BY id ASC";

$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error);
}

// การค้นหาและกรองข้อมูล
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = "";
if($search != '') {
    $where = " WHERE id LIKE '%$search%'
               OR course_code LIKE '%$search%' 
               OR course_name LIKE '%$search%' 
               OR curriculum LIKE '%$search%'
               OR major LIKE '%$search%'
               OR exam_date LIKE '%$search%'
               OR status_doc LIKE '%$search%'
               OR manuscript  LIKE '%$search%'
               OR examiner_name  LIKE '%$search%'";
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการเอกสารที่อนุมัติแล้ว</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap"
        rel="stylesheet">
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
    </style>
</head>

<body>


    <div class="container-fluid mt-5">

    <h2 class="mb-4">
        <button onclick="goBack()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> ย้อนกลับ
        </button>

        


        
            <center>รายการเอกสารที่อนุมัติแล้ว</center>
        </h2>

        <!-- ส่วนค้นหาและกรองข้อมูล -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control"
                            placeholder="ค้นหาข้อมูลแบบฟอร์มข้อสอบกลางภาค"
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> ค้นหา
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
                            echo "<td>" . $row["id"] . "</td>";
                            echo "<td>" . $row["semester"] . "</td>";
                            echo "<td>" . $row["course_code"] . "</td>";
                            echo "<td>" . $row["course_name"] . "</td>";
                            echo "<td>" . $row["curriculum"] . "</td>";
                            echo "<td>" . $row["major"] . "</td>";
                            echo "<td>" . $row["exam_date"] . "</td>";
                            echo "<td>" . $row["manuscript"] . "</td>";
                            echo "<td>" . $row["numberofsets"] . "</td>";                            
                            echo "<td>" . $row["examiner_name"] . "</td>";
                            echo "<td>" . $row["status_doc"] . "</td>";
                            echo "<td>";
                            echo "<a href='academic_doc_details.php?id=" . $row["id"] . "' class='btn btn-primary btn-sm'>รายละเอียดแบบฟอร์ม</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='12' class='text-center'>ไม่พบข้อมูลเอกสารที่อนุมัติแล้ว</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
<script>
function goBack() {
    window.history.back();
}
</script>

</html>

<?php
$conn->close();
?>