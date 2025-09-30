<?php
require_once 'config.php';
include 'admin_nav.php';

// ดึงข้อมูลจำนวนเอกสารสำหรับการ์ดแสดงผล
$sql_total = "SELECT COUNT(*) as total FROM exam_requests";
$sql_pending = "SELECT COUNT(*) as pending FROM exam_requests WHERE status_doc = 'รออนุมัติ'";
$sql_approved = "SELECT COUNT(*) as approved FROM exam_requests WHERE status_doc = 'อนุมัติแล้ว'";
$sql_rejected = "SELECT COUNT(*) as rejected FROM exam_requests WHERE status_doc = 'ไม่อนุมัติ'";
$sql_printed = "SELECT COUNT(*) as printed FROM exam_requests WHERE status_doc = 'จัดพิมพ์แล้ว'";

$result_total = $conn->query($sql_total);
$result_pending = $conn->query($sql_pending);
$result_approved = $conn->query($sql_approved);
$result_rejected = $conn->query($sql_rejected);
$result_printed = $conn->query($sql_printed);

$total_count = $result_total->fetch_assoc()['total'];
$pending_count = $result_pending->fetch_assoc()['pending'];
$approved_count = $result_approved->fetch_assoc()['approved'];
$rejected_count = $result_rejected->fetch_assoc()['rejected'];
$printed_count = $result_printed->fetch_assoc()['printed'];

// ดึงข้อมูลภาคเรียนสำหรับ dropdown
$sql_semesters = "SELECT DISTINCT semester FROM exam_requests ORDER BY semester DESC";
$result_semesters = $conn->query($sql_semesters);

// กำหนดภาคเรียนและสถานะที่เลือก
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : null;
$selected_status = isset($_GET['status']) ? $_GET['status'] : 'all';

if (!$selected_semester && $result_semesters->num_rows > 0) {
    $result_semesters->data_seek(0);
    $selected_semester = $result_semesters->fetch_assoc()['semester'];
}

// เตรียม SQL query สำหรับดึงข้อมูลตามสาขาวิชาและสถานะ
// เตรียม SQL query สำหรับดึงข้อมูลตามสาขาวิชาและสถานะ
if ($selected_status == 'all') {
    // ดึงข้อมูลแยกตามสถานะและสาขา
    $sql_by_major = "SELECT major, status_doc, COUNT(*) as count 
                     FROM exam_requests 
                     WHERE semester = ?
                     GROUP BY major, status_doc
                     ORDER BY major, status_doc";
    $stmt = $conn->prepare($sql_by_major);
    $stmt->bind_param("s", $selected_semester);
} else {
    // ดึงข้อมูลเฉพาะสถานะที่เลือก
    $sql_by_major = "SELECT major, COUNT(*) as count 
                     FROM exam_requests 
                     WHERE semester = ? AND status_doc = ?
                     GROUP BY major
                     ORDER BY major";
    $stmt = $conn->prepare($sql_by_major);
    $stmt->bind_param("ss", $selected_semester, $selected_status);
}

$stmt->execute();
$result_by_major = $stmt->get_result();

// จัดเตรียมข้อมูลสำหรับกราฟ
$chart_data = [];
if ($selected_status == 'all') {
    while ($row = $result_by_major->fetch_assoc()) {
        $major = $row['major'];
        $status = $row['status_doc'];
        $count = $row['count'];
        
        if (!isset($chart_data[$major])) {
            $chart_data[$major] = [
                'รออนุมัติ' => 0,
                'อนุมัติแล้ว' => 0,
                'ไม่อนุมัติ' => 0,
                'จัดพิมพ์แล้ว' => 0
            ];
        }
        $chart_data[$major][$status] = $count;
    }
} else {
    while ($row = $result_by_major->fetch_assoc()) {
        $major = $row['major'];
        $count = $row['count'];
        $chart_data[$major] = [$selected_status => $count];
    }
}

// เตรียม SQL query สำหรับดึงเอกสารตามภาคเรียนและสถานะที่เลือก
$sql_all_docs = "SELECT id, semester, course_code, course_name, curriculum, major, 
                         exam_date, status_doc, manuscript, numberofsets, examiner_name
                  FROM exam_requests
                  WHERE semester = ?";
if ($selected_status != 'all') {
    $sql_all_docs .= " AND status_doc = ?";
}
$sql_all_docs .= " ORDER BY id ASC";

$stmt = $conn->prepare($sql_all_docs);
if ($selected_status != 'all') {
    $stmt->bind_param("ss", $selected_semester, $selected_status);
} else {
    $stmt->bind_param("s", $selected_semester);
}
$stmt->execute();
$result_all_docs = $stmt->get_result();

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
        <div class="row">
            <div class="col col-sm-12">
                <div class="alert alert-primary" role="alert">
                    <center>
                        <h4>ระบบจัดการข้อสอบกลางภาค มหาวิทยาลัยราชพฤกษ์</h4>
                    </center>
                </div>
            </div>
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
            <div class="col-sm-8">
                <h5 class="text-end">จำนวนเอกสารที่แสดง: <span id="totalDocuments"></span></h5>
            </div>
        </div>


        <div class="row">
            <div class="col-sm-12">
                <canvas id="myChart" height="100px"></canvas>
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
                                echo "<a href='document_details.php?id=" . $row["id"] . "' class='btn btn-primary btn-sm'>รายละเอียดแบบฟอร์ม</a>";
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

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</body>

</html>