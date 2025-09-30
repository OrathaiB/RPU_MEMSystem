<?php
session_start();
require_once 'config.php';
include 'user_nav.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// ฟังก์ชันสำหรับดึงข้อมูลแบบ single value
function get_single_value($conn, $sql, $user_id) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $value = $result->fetch_assoc();
    $stmt->close();
    return $value[array_key_first($value)];
}

// ดึงข้อมูลสถิติต่างๆ
$total_count = get_single_value($conn, "SELECT COUNT(*) as count FROM exam_requests WHERE examiner_id = ?", $user_id);
$pending_count = get_single_value($conn, "SELECT COUNT(*) as count FROM exam_requests WHERE examiner_id = ? AND status_doc = 'รออนุมัติ'", $user_id);
$approved_count = get_single_value($conn, "SELECT COUNT(*) as count FROM exam_requests WHERE examiner_id = ? AND status_doc = 'อนุมัติแล้ว'", $user_id);
$rejected_count = get_single_value($conn, "SELECT COUNT(*) as count FROM exam_requests WHERE examiner_id = ? AND status_doc = 'ไม่อนุมัติ'", $user_id);
$printed_count = get_single_value($conn, "SELECT COUNT(*) as count FROM exam_requests WHERE examiner_id = ? AND status_doc = 'จัดพิมพ์แล้ว'", $user_id);

// ดึงข้อมูลภาคเรียน
$sql_semesters = "SELECT DISTINCT semester FROM exam_requests WHERE examiner_id = ? ORDER BY semester DESC";
$stmt_semesters = $conn->prepare($sql_semesters);
$stmt_semesters->bind_param("i", $user_id);
$stmt_semesters->execute();
$result_semesters = $stmt_semesters->get_result();

// กำหนดภาคเรียนและสถานะที่เลือก
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : null;
$selected_status = isset($_GET['status']) ? $_GET['status'] : 'all';

if (!$selected_semester && $result_semesters->num_rows > 0) {
    $result_semesters->data_seek(0);
    $selected_semester = $result_semesters->fetch_assoc()['semester'];
}
$stmt_semesters->close();

// เตรียม SQL query สำหรับดึงข้อมูลตามสาขาวิชาและสถานะ
if ($selected_status == 'all') {
    $sql_by_major = "SELECT major, status_doc, COUNT(*) as count 
                     FROM exam_requests 
                     WHERE examiner_id = ? AND semester = ?
                     GROUP BY major, status_doc
                     ORDER BY major, status_doc";
    $stmt = $conn->prepare($sql_by_major);
    $stmt->bind_param("is", $user_id, $selected_semester);
} else {
    $sql_by_major = "SELECT major, COUNT(*) as count 
                     FROM exam_requests 
                     WHERE examiner_id = ? AND semester = ? AND status_doc = ?
                     GROUP BY major
                     ORDER BY major";
    $stmt = $conn->prepare($sql_by_major);
    $stmt->bind_param("iss", $user_id, $selected_semester, $selected_status);
}
$stmt->execute();
$result_by_major = $stmt->get_result();
$stmt->close();

// เตรียม SQL query สำหรับดึงเอกสารตามภาคเรียนและสถานะที่เลือก
$sql_all_docs = "SELECT id, semester, course_code, course_name, curriculum, major, 
                         exam_date, status_doc, manuscript, numberofsets, examiner_name
                  FROM exam_requests
                  WHERE examiner_id = ? AND semester = ?";
if ($selected_status != 'all') {
    $sql_all_docs .= " AND status_doc = ?";
}
$sql_all_docs .= " ORDER BY id ASC";

$stmt = $conn->prepare($sql_all_docs);
if ($selected_status != 'all') {
    $stmt->bind_param("iss", $user_id, $selected_semester, $selected_status);
} else {
    $stmt->bind_param("is", $user_id, $selected_semester);
}
$stmt->execute();
$result_all_docs = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard ระบบจัดการข้อสอบกลางภาค</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/ionicons@5.0.0/dist/ionicons.js"></script>
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
    </style>
</head>

<body>
    <br>
    <div class="container">
        <!-- <div class="row">
            <div class="col col-sm-12">
                <div class="alert alert-primary" role="alert">
                    <center>
                        <h4>ระบบจัดการข้อสอบกลางภาค มหาวิทยาลัยราชพฤกษ์</h4>
                    </center>
                </div>
            </div>
        </div> -->
        <div>
            <button onclick="goBack()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </button>
        </div>

        <br>

        <div class="row">
            <?php
    $cards = [
        ['bg-primary', 'file-tray-full', 'ข้อสอบทั้งหมด', "จำนวน $total_count รายการ" ],
        ['bg-warning', 'sync', 'รออนุมัติ', "จำนวน $pending_count รายการ" ],
        ['bg-success', 'checkmark', 'อนุมัติแล้ว', "จำนวน $approved_count รายการ"],
        ['bg-danger', 'create', 'รอแก้ไขเอกสาร', "จำนวน $rejected_count รายการ"]
    ];

    foreach ($cards as $card) {
        echo "<div class='col-6 col-sm-3'>
                <div class='card text-white {$card[0]} mb-3' style='max-width: 18rem;'>
                    <div class='card-header'>
                        <ion-icon name='{$card[1]}'></ion-icon>
                        {$card[2]}
                    </div>
                    <div class='card-body'>
                        <h5 class='card-title'>{$card[3]}</h5>
                    </div>
                </div>
            </div>";
    }
    ?>
        </div>

        <br>

        <div class="row mb-3">
            <div class="col-sm-4">
                <form action="" method="GET" id="filterForm">
                    <div class="row">
                        <div class="col-6">
                            <select name="semester" class="form-select" onchange="this.form.submit()">
                                <?php
                        $result_semesters->data_seek(0);
                        while ($row = $result_semesters->fetch_assoc()) {
                            $selected = ($row['semester'] == $selected_semester) ? 'selected' : '';
                            echo "<option value='{$row['semester']}' $selected>{$row['semester']}</option>";
                        }
                        ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="all" <?php echo $selected_status == 'all' ? 'selected' : ''; ?>>
                                    เอกสารทั้งหมด</option>
                                <option value="อนุมัติแล้ว"
                                    <?php echo $selected_status == 'อนุมัติแล้ว' ? 'selected' : ''; ?>>อนุมัติแล้ว
                                </option>
                                <option value="รออนุมัติ"
                                    <?php echo $selected_status == 'รออนุมัติ' ? 'selected' : ''; ?>>รออนุมัติ</option>
                                <option value="ไม่อนุมัติ"
                                    <?php echo $selected_status == 'ไม่อนุมัติ' ? 'selected' : ''; ?>>ไม่อนุมัติ
                                </option>
                                <option value="จัดพิมพ์แล้ว"
                                    <?php echo $selected_status == 'จัดพิมพ์แล้ว' ? 'selected' : ''; ?>>จัดพิมพ์แล้ว
                                </option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

        </div>


        <!-- ส่วนแสดงรายการเอกสาร -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>รายการเอกสารทั้งหมด (ภาคเรียน: <?php echo $selected_semester; ?>)</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
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
                        if ($result_all_docs->num_rows > 0) {
                            while($row = $result_all_docs->fetch_assoc()) {
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
                                echo "<a href='user_doc_details.php?id=" . $row["id"] . "' class='btn btn-primary btn-sm'>รายละเอียดแบบฟอร์ม</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='12' class='text-center'>ไม่พบข้อมูลเอกสารสำหรับภาคเรียนที่เลือก</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById("myChart").getContext('2d');
            var selectedStatus = '<?php echo $selected_status; ?>';
            var chartData = <?php echo json_encode($chart_data); ?>;

            // กำหนดสีสำหรับแต่ละสถานะ
            var statusColors = {
                'รออนุมัติ': 'rgba(255, 206, 86, 0.7)',
                'อนุมัติแล้ว': 'rgba(75, 192, 192, 0.7)',
                'ไม่อนุมัติ': 'rgba(255, 99, 132, 0.7)',
                'จัดพิมพ์แล้ว': 'rgba(153, 102, 255, 0.7)'
            };

            // เตรียมข้อมูลสำหรับกราฟ
            var labels = Object.keys(chartData);
            var datasets = [];

            if (selectedStatus === 'all') {
                // สร้าง dataset สำหรับแต่ละสถานะ
                Object.keys(statusColors).forEach(function(status) {
                    var data = labels.map(function(major) {
                        return chartData[major][status] || 0;
                    });

                    datasets.push({
                        label: status,
                        data: data,
                        backgroundColor: statusColors[status],
                        borderColor: statusColors[status].replace('0.7', '1'),
                        borderWidth: 1
                    });
                });
            } else {
                // สร้าง dataset สำหรับสถานะที่เลือก
                var data = labels.map(function(major) {
                    return chartData[major][selectedStatus] || 0;
                });

                datasets.push({
                    label: selectedStatus,
                    data: data,
                    backgroundColor: statusColors[selectedStatus],
                    borderColor: statusColors[selectedStatus].replace('0.7', '1'),
                    borderWidth: 1
                });
            }

            // คำนวณจำนวนเอกสารทั้งหมดที่แสดง
            var totalDocs = 0;
            labels.forEach(function(major) {
                if (selectedStatus === 'all') {
                    Object.values(chartData[major]).forEach(function(count) {
                        totalDocs += count;
                    });
                } else {
                    totalDocs += chartData[major][selectedStatus] || 0;
                }
            });
            document.getElementById('totalDocuments').textContent = totalDocs;

            // สร้างกราฟ
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: selectedStatus === 'all' ?
                                'จำนวนรายการข้อสอบแยกตามสถานะและสาขาวิชา' : 'จำนวนรายการข้อสอบ ' +
                                selectedStatus + ' ตามสาขาวิชา'
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        });


        
        </script>
<script>
function goBack() {
            window.location.href = 'user_index.php';
        }
        </script>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</body>

</html>