<?php
require_once 'config.php';
include 'academic_nav.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ไม่พบ ID เอกสารที่ระบุ");
}


// ดึงข้อมูลเอกสาร
$sql = "SELECT * FROM exam_requests WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

if (!$document) {
    die("ไม่พบเอกสารที่ตรงกับ ID ที่ระบุ");
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดเอกสาร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
        font-family: "Sarabun", sans-serif;
    }
    

    .form-container {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .document-icon {
        font-size: 50px;
        color: #007bff;
        cursor: pointer;
        margin-top: 10px;
        transition: color 0.3s ease;
    }

    .document-icon:hover {
        color: #0056b3;
    }

    /* เพิ่มสไตล์สำหรับ Modal แสดงไฟล์ */
    .file-viewer-modal .modal-dialog {
        max-width: 90%;
        max-height: 90vh;
        margin: 1.75rem auto;
    }

    .file-viewer-modal .modal-content {
        height: 90vh;
    }

    .file-viewer-modal .modal-body {
        padding: 0;
        height: calc(90vh - 120px);
    }

    .file-viewer-modal iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="form-container">

            <div class="row mb-3">
                <div class="col d-flex justify-content-between">
                    <button onclick="goBack()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> ย้อนกลับ
                    </button>

                </div>
            </div>


            <h2 class="mb-4 ">
                <center>ตรวจสอบแบบฟอร์มการขอจัดทำข้อสอบ</center>
            </h2>
            <div class="card">
                <div class="card-body">
                    <hr class="mt-4 mb-4">
                    
                    <p><strong>
                            <center>สรุปรายการเอกสารที่ต้องจัดพิมพ์</center>
                        </strong></p>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <th style="border: 1px solid black; padding: 10px; background-color: #f2f2f2;">ไฟล์ข้อสอบ
                            </th>
                            <th style="border: 1px solid black; padding: 10px; background-color: #f2f2f2;">แบบฟอร์มอื่นๆ
                            </th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 10px;">
                                <strong>ชื่อไฟล์:</strong> <?php echo htmlspecialchars($document['file_name']); ?><br>
                                <strong>จำนวน:</strong> <?php echo htmlspecialchars($document['numberofsets']); ?> <br>
                                <i class="fas fa-file-alt document-icon ml-4"
                                    onclick="showFile('<?php echo htmlspecialchars($document['file_path']); ?>')"
                                    style="cursor: pointer;"></i>
                            </td>
                            <td style="border: 1px solid black; padding: 10px;">
                                <strong>ชื่อแบบฟอร์ม:</strong>
                                <?php echo htmlspecialchars($document['other_form']); ?><br>
                                <strong>จำนวน:</strong> <?php echo htmlspecialchars($document['other_form_count']); ?>
                                ชุด<br>
                                <?php if (!empty($document['other_form'])): ?>
                                <i class="fas fa-file-alt document-icon ml-4"
                                    onclick="showFile('<?php echo htmlspecialchars($document['other_form_file']); ?>')"
                                    style="cursor: pointer;"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <hr class="mt-4 mb-4">

                    <h5 class="card-title"><strong>เลขที่เอกสาร:
                            <?php echo htmlspecialchars($document['id']); ?></strong></h5>


                    <hr class="mt-4 mb-4">
                    <p><strong>ภาคเรียนที่:</strong> <?php echo htmlspecialchars($document['semester']); ?></p>
                    <p><strong>รหัสวิชา:</strong> <?php echo htmlspecialchars($document['course_code']); ?></p>
                    <p><strong>ชื่อวิชา:</strong> <?php echo htmlspecialchars($document['course_name']); ?></p>
                    <p><strong>หลักสูตร:</strong> <?php echo htmlspecialchars($document['curriculum']); ?></p>
                    <p><strong>สาขาวิชา:</strong> <?php echo htmlspecialchars($document['major']); ?></p>
                    <p><strong>ต้นฉบับ:</strong> <?php echo htmlspecialchars($document['manuscript']); ?> หน้า</p>
                    <p><strong>จำนวนชุดที่ต้องการจัดพิมพ์:</strong>
                        <?php echo htmlspecialchars($document['numberofsets']); ?></p>
                    <p><strong>วันที่สอบ:</strong> <?php echo htmlspecialchars($document['exam_date']); ?></p>
                    <p><strong>เวลาสอบ:</strong>
                        <?php echo htmlspecialchars($document['startexam_time']) . ' - ' . htmlspecialchars($document['endexam_time']); ?>
                    </p>
                    <p><strong>จำนวนเวลาสอบ:</strong> <?php echo htmlspecialchars($document['totalexam_time']); ?></p>

                    <hr class="mt-4 mb-4">

                    <h6><strong>วิธีการสอบ:</strong></h6>
                    <?php
                        $exam_methods = explode('/', $document['exam_methods']);
                        echo "<ol>";
                        foreach ($exam_methods as $method) {
                            echo "<li>" . htmlspecialchars(trim($method)) . "</li>";
                        }
                        echo "</ol>";
                    ?>

                    <hr class="mt-4 mb-4">

                    <h6><strong>การนำอุปกรณ์หรือเอกสารเข้าห้องสอบ:</strong></h6>
                    <?php
                        $allowed_equipment = explode('/', $document['allowed_equipment']);
                        echo "<ol>";
                        foreach ($allowed_equipment as $equipment) {
                            echo "<li>" . htmlspecialchars(trim($equipment)) . "</li>";
                        }
                        echo "</ol>";
                    ?>

                    <hr class="mt-4 mb-4">

                    <h6><strong>ผู้ออกข้อสอบ:</strong></h6>
                    <?php
                        $examiners = explode('/', $document['examiner_name']);
                        echo "<ol>";
                        foreach ($examiners as $examiner) {
                            echo "<li>" . htmlspecialchars(trim($examiner)) . "</li>";
                        }
                        echo "</ol>";
                    ?>

                    <hr class="mt-4 mb-4">

                    <h6><strong>รายละเอียดเพิ่มเติม:</strong></h6>
                    <p><?php echo nl2br(htmlspecialchars($document['additional_details'])); ?></p>

                    <hr class="mt-4 mb-4">


                     


                </div>
            </div>

            <!-- Modal สำหรับแสดงไฟล์ -->
            <div class="modal fade file-viewer-modal" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fileModalLabel">เอกสาร</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <iframe id="fileViewer" src="" frameborder="0"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        </div>
                    </div>
                </div>
            </div>




            <script>
            function goBack() {
                window.history.back();
            }

            function showFile(filePath) {
                var fileViewer = document.getElementById('fileViewer');
                fileViewer.src = filePath;
                var fileModal = new bootstrap.Modal(document.getElementById('fileModal'));
                fileModal.show();
            }

            
            </script>
        </div>
</body>

</html>