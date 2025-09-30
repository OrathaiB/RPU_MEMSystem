<?php
// เริ่ม session และเชื่อมต่อฐานข้อมูล
session_start();
require_once 'config.php';

// ตรวจสอบว่ามีการส่ง ID มาหรือไม่
if (!isset($_GET['id'])) {
    die("ไม่พบ ID ของฟอร์ม");
}

$form_id = $_GET['id'];

// ดึงข้อมูลฟอร์มจาก SQL
$sql = "SELECT * FROM exam_requests WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบข้อมูลฟอร์ม");
}

$form_data = $result->fetch_assoc();

// แยก semester เป็น term และ yeart
$semester_parts = explode('/', $form_data['semester']);
$form_data['term'] = $semester_parts[0] ?? '';
$form_data['yeart'] = $semester_parts[1] ?? '';

// ลบคำว่า "ชุด" ออกจาก numberofsets
$form_data['numberofsets'] = preg_replace('/\D/', '', $form_data['numberofsets']);

// ตรวจสอบว่ามีค่า total_exam_time ใน $form_data หรือไม่
$total_exam_time = isset($form_data['total_exam_time']) ? $form_data['total_exam_time'] : '';

// ฟังก์ชันสำหรับแสดงค่าที่เลือกไว้ใน select
function selected($value, $selected) {
    return $value == $selected ? 'selected' : '';
}

// ฟังก์ชันสำหรับแสดงค่าที่เลือกไว้ใน checkbox
function checked($value, $checked) {
    return in_array($value, explode('/', $checked)) ? 'checked' : '';
}
//ส่วนจัดการชื่อผู้ออกข้อสอบ
$sql_examiners = "SELECT id, full_name 
                  FROM datalogin 
                  WHERE role IN ('teacher', 'admin')
                  ORDER BY full_name";
$examiner_result = $conn->query($sql_examiners);
$examiners = [];
while($row = $examiner_result->fetch_assoc()) {
    $examiners[] = $row;
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขแบบฟอร์มการขอจัดทำข้อสอบ</title>
    <!-- เพิ่ม CSS เหมือนกับในหน้าสร้างฟอร์ม -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- เพิ่ม custom CSS ตามต้องการ -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap"
        rel="stylesheet">

    <style>
    body {
        font-family: 'Sarabun', sans-serif;
        background-color: #f8f9fa;
    }

    .form-container {
        background-color: #ffffff;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-top: 50px;
    }

    .form-label {
        font-weight: bold;
    }

    .form-control {
        height: auto;
        /* ให้ความสูงปรับตามเนื้อหา */
        margin-bottom: 10px;

    }

    .form-check {
        margin-bottom: 10px;
    }

    .card {
        border: none;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        background-color: #007bff;
        /* color: white; */
        font-weight: bold;
    }

    .button-container {
        display: flex;
        justify-content: center;
        gap: 20px;
        /* ระยะห่างระหว่างปุ่ม */
        margin-top: 20px;
        /* ระยะห่างจากด้านบน */
    }

    .btn-primary {
        background-color: #04ff00;
        border-color: #04ff00;
    }

    .btn-primary:hover {
        background-color: #00b333;
        border-color: #00b333;
    }

    .examiner-item,
    .other-form-item,
        {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
    }

    .input-group-text {
        width: 40px;
        /* กำหนดความกว้างคงที่ */
        text-align: center;
        /* จัดตำแหน่งตัวเลขให้อยู่กึ่งกลาง */
        padding: 0.375rem 0.75rem;
        /* ปรับ padding ให้เหมาะสม */
        font-size: 1rem;
        /* กำหนดขนาดตัวอักษร */
        line-height: 1.5;
        /* กำหนดความสูงของบรรทัด */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .input-group {
        align-items: stretch;
        /* ทำให้ทุกส่วนของ input-group มีความสูงเท่ากัน */
    }

    .btn.btn-danger.remove-examiner {
        height: auto;
        /* ให้ความสูงปรับตามเนื้อหา */
    }

    #term,
    #yeart {
        height: auto;
        /* ให้ความสูงปรับตามเนื้อหา */
        margin-bottom: 10px;
        width: 180px;
    }

    .file-display {
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 5px;
    }

    .file-name-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .file-icon-row {
        display: flex;
        align-items: center;
    }

    .file-name {
        font-weight: bold;
        word-break: break-all;
    }
    </style>
</head>

<body>
    <?php include 'admin_nav.php'; ?>
    <div class="container">
        <div class="form-container">

            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0"><i class="fas fa-edit me-2"></i>แก้ไขแบบฟอร์มการขอจัดทำข้อสอบ</h2>
                </div>
                <div class="card-body">
                    <form id="editExamForm" action="update_form.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">

                        <!-- ภาคเรียนและปีการศึกษา -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="term" class="form-label">ภาคเรียนที่</label>
                                <select class="form-select" id="term" name="term" required>
                                    <option value="">เลือกภาคเรียน</option>
                                    <option value="1" <?php echo selected("1", $form_data['term']); ?>>1</option>
                                    <option value="2" <?php echo selected("2", $form_data['term']); ?>>2</option>
                                    <option value="3" <?php echo selected("3", $form_data['term']); ?>>3</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="yeart" class="form-label">ปีการศึกษา</label>
                                <select class="form-select" id="yeart" name="yeart" required>
                                    <option value="">เลือกปีการศึกษา</option>
                                    <?php
                                    $currentYear = date('Y') + 543;
                                    for ($i = 0; $i < 10; $i++) {
                                        $year = $currentYear + $i;
                                        echo "<option value=\"$year\"" . selected($year, $form_data['yeart']) . ">$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <?php
                            require_once 'config.php';

                            // เปลี่ยน SQL query ให้ดึงข้อมูลทั้งหมดจากตาราง courses
                            $sql = "SELECT course_code, course_name, curriculum FROM courses ORDER BY course_code";
                            $result = $conn->query($sql);

                            // ถ้ามี $form_id และ $form_data ให้ดึงข้อมูลของฟอร์มที่กำลังแก้ไข
                            $selected_course = '';
                            if (isset($form_id) && isset($form_data)) {
                                $selected_course = $form_data['course_code'];
                            }
                        ?>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="course_code" class="form-label">รหัสวิชา</label>
                                <select class="form-select" id="course_code" name="course_code" required>
                                    <option value="">กรุณาเลือกรหัสรายวิชา</option>
                                    <?php
                                        if ($result->num_rows > 0) {
                                            while($row = $result->fetch_assoc()) {
                                                $selected = ($row['course_code'] == $selected_course) ? 'selected' : '';
                                                echo "<option value='" . htmlspecialchars($row['course_code']) . "' data-name='" . htmlspecialchars($row['course_name']) . "' data-curriculum='" . htmlspecialchars($row['curriculum']) . "' {$selected}>" . htmlspecialchars($row['course_code'] . " - " . $row['course_name']) . "</option>";
                                            }
                                        } else {
                                            echo "<option value=''>ไม่มีข้อมูลรายวิชา</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="course_name" class="form-label">ชื่อวิชา</label>
                                <input type="text" class="form-control" id="course_name" name="course_name" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="curriculum" class="form-label">หลักสูตร</label>
                                <input type="text" class="form-control" id="curriculum" name="curriculum" readonly>
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="major" class="form-label">สาขาวิชา</label>
                                <select class="form-select" id="major" name="major" required>
                                    <option value="">เลือกสาขาวิชา</option>
                                    <option value="สาขาวิชาเทคโนโลยีดิจิทัลเพื่อธุรกิจ"
                                        <?php echo selected("สาขาวิชาเทคโนโลยีดิจิทัลเพื่อธุรกิจ", $form_data['major']); ?>>
                                        สาขาวิชาเทคโนโลยีดิจิทัลเพื่อธุรกิจ
                                    </option>
                                    <option value="สาขาวิชาเทคโนโลยีดิจิทัล"
                                        <?php echo selected("สาขาวิชาเทคโนโลยีดิจิทัล", $form_data['major']); ?>>
                                        สาขาวิชาเทคโนโลยีดิจิทัล
                                    </option>
                                    <option value="สาขาวิชาการตลาดดิจิทัล"
                                        <?php echo selected("สาขาวิชาการตลาดดิจิทัล", $form_data['major']); ?>>
                                        สาขาวิชาการตลาดดิจิทัล
                                    </option>
                                    <option value="สาขาวิชาบัญชี"
                                        <?php echo selected("สาขาวิชาบัญชี", $form_data['major']); ?>>
                                        สาขาวิชาบัญชี
                                    </option>
                                    <option value="สาขาวิชาการจัดการ"
                                        <?php echo selected("สาขาวิชาการจัดการ", $form_data['major']); ?>>
                                        สาขาวิชาการจัดการ
                                    </option>
                                </select>
                            </div>
                        </div>


                        <!-- ต้นฉบับและจำนวนชุด -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="manuscript" class="form-label">ต้นฉบับ (จำนวนหน้า)</label>
                                <input type="text" class="form-control" id="manuscript" name="manuscript"
                                    value="<?php echo htmlspecialchars($form_data['manuscript']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="numberofsets" class="form-label">จำนวนชุดที่ต้องการจัดพิมพ์</label>
                                <input type="number" class="form-control" id="numberofsets" name="numberofsets"
                                    value="<?php echo htmlspecialchars($form_data['numberofsets']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="exam_date" class="form-label">วันที่สอบ</label>
                                <div class="input-group">
                                    <input type="text" id="exam_date" name="exam_date" class="form-control datepicker"
                                        value="<?php echo htmlspecialchars($form_data['exam_date']); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="exam_time_start" class="form-label">เวลาเริ่มสอบ</label>
                                <div class="input-group">
                                    <input type="text" id="exam_time_start" name="exam_time_start"
                                        class="form-control timepicker"
                                        value="<?php echo htmlspecialchars($form_data['startexam_time']); ?>" readonly>

                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="exam_time_end" class="form-label">เวลาสิ้นสุดสอบ</label>
                                <div class="input-group">
                                    <input type="text" id="exam_time_end" name="exam_time_end"
                                        class="form-control timepicker"
                                        value="<?php echo htmlspecialchars($form_data['endexam_time']); ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- เวลาสอบรวม -->
                        <div class="mb-3">
                            <label for="total_exam_time" class="form-label">เวลาสอบรวม</label>
                            <input type="text" id="total_exam_time" name="total_exam_time" class="form-control"
                                value="<?php echo htmlspecialchars($total_exam_time); ?>" readonly>
                        </div>
                        <!-- ชื่อผู้ออกข้อสอบ -->
                        <div class="mb-3">
                            <label class="form-label">ชื่อผู้ออกข้อสอบ</label>
                            <div id="examiner_names_container">
                                <?php
        $examiner_names = explode(',', $form_data['examiner_name']);
        foreach ($examiner_names as $name) {
        ?>
                                <div class='examiner-item mb-2'>
                                    <div class='input-group'>
                                        <input type="text" class="form-control"
                                            value="<?php echo htmlspecialchars($name); ?>" readonly>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>



                        <hr class="mt-4 mb-4">

                        <!-- วิธีการสอบ -->
                        <div class="mb-3">
                            <label class="form-label">วิธีการสอบ</label>
                            <div class="exam-method-container">
                                <?php
                                    $exam_methods = explode('/', $form_data['exam_methods']);
                                    $exam_method_options = [
                                        'answer_in_exam' => 'ตอบคำถามในข้อสอบ',
                                        'answer_book' => 'กระดาษทำการเขียนคำตอบ',
                                        'choice_120' => 'กระดาษคำตอบปรนัย (Choice 120 ข้อ)',
                                    ];

                                    foreach ($exam_method_options as $value => $label) {
                                        $is_checked = false;
                                        $count_value = '';
                                        foreach ($exam_methods as $method) {
                                            if (strpos($method, $label) !== false) {
                                                $is_checked = true;
                                                $count_value = trim(explode(':', $method)[1] ?? '');
                                                break;
                                            }
                                        }
                                        echo "<div class='form-check'>
                                                <input class='form-check-input exam-method' type='checkbox' id='$value' name='exam_method[]' value='$label' " . ($is_checked ? 'checked' : '') . ">
                                                <label class='form-check-label' for='$value'>$label</label>
                                                <input type='number' class='form-control d-" . ($is_checked ? 'inline-block' : 'none') . " input-inline' id='{$value}_count' name='{$value}_count' placeholder='จำนวน' value='$count_value' style='width: 100px;'>
                                            </div>";
                                    }
                                ?>
                            </div>
                        </div>

                        <!-- ส่วนแสดงแบบฟอร์มอื่นๆ -->

                        <div class="mb-3">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="other_forms_checkbox"
                                    name="exam_methods[]" value="แบบฟอร์มอื่นๆ">
                                <label class="form-check-label" for="other_forms_checkbox">แบบฟอร์มอื่นๆ</label>
                            </div>

                            <div id="other_forms_container" class="d-none">
                                <table class="table table-bordered" id="other_forms_table">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">ลำดับ</th>
                                            <th style="width: 40%;">ชื่อแบบฟอร์ม</th>
                                            <th style="width: 15%;">จำนวน</th>
                                            <th style="width: 25%;">ไฟล์</th>
                                            <th style="width: 15%;">การจัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- แถวของแบบฟอร์มอื่นๆจะถูกเพิ่มที่นี่โดย JavaScript -->
                                    </tbody>
                                </table>

                                <button type="button" id="add_other_form" class="btn btn-success mt-2">
                                    <i class="fas fa-plus"></i> เพิ่มแบบฟอร์มอื่นๆ
                                </button>
                            </div>
                        </div>


                        <hr class="mt-4 mb-4">

                        <!-- การนำอุปกรณ์หรือเอกสารเข้าห้องสอบ -->
                        <div class="mb-3">
                            <label class="form-label">การนำอุปกรณ์หรือเอกสารเข้าห้องสอบ</label>
                            <div class="exam-equipment-container">
                                <?php
                                    $allowed_equipment = explode('/', $form_data['allowed_equipment']);
                                    $equipment_options = [
                                        'exam_document' => 'นำเอกสารเข้าห้องสอบ',
                                        'open_book' => 'เปิดตำรา',
                                        'calculator' => 'เครื่องคำนวณ',
                                        'other_equipment' => 'อุปกรณ์อื่นๆ'
                                    ];

                                    foreach ($equipment_options as $value => $label) {
                                        $is_checked = false;
                                        $detail = '';
                                        foreach ($allowed_equipment as $equipment) {
                                            if (strpos($equipment, $label) !== false) {
                                                $is_checked = true;
                                                $detail = trim(explode(':', $equipment)[1] ?? '');
                                                break;
                                            }
                                        }
                                        echo "<div class='form-check'>
                                                <input type='checkbox' id='$value' class='form-check-input exam-equipment-checkbox' " . ($is_checked ? 'checked' : '') . ">
                                                <label for='$value' class='form-check-label'>$label</label>";
                                        
                                        // เพิ่มเงื่อนไขสำหรับ "เครื่องคำนวณ"
                                        if ($value !== 'calculator') {
                                            echo "<input type='text' id='{$value}_detail' class='form-control " . ($is_checked ? 'd-inline-block' : 'd-none') . " input-inline' placeholder='กรุณาระบุรายละเอียด' value='$detail' style='width: 300px;'>";
                                        }
                                        
                                        echo "</div>";
                                    }
                                ?>
                            </div>
                        </div>

                        <hr class="mt-4 mb-4">

                        <!-- รายละเอียดอื่นๆ -->
                        <div class="mb-3">
                            <label for="additional_details" class="form-label">รายละเอียดอื่นๆ</label>
                            <textarea class="form-control" id="additional_details" name="additional_details" rows="3"
                                placeholder="กรุณาระบุรายละเอียดเพิ่มเติม (ถ้ามี)"><?php echo htmlspecialchars($form_data['additional_details']); ?></textarea>
                        </div>

                        <!-- แสดงไฟล์ข้อสอบ -->
                        <div class="mb-3">
                            <label class="form-label">ไฟล์ข้อสอบปัจจุบัน</label>
                            <?php if (!empty($form_data['file_path'])): ?>
                            <div class="file-display">
                                <div class="file-name-row">
                                    <span
                                        class="file-name"><?php echo htmlspecialchars($form_data['file_name']); ?></span>
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="deleteFile('exam', <?php echo $form_id; ?>)">
                                        <i class="fas fa-trash"></i> ลบไฟล์
                                    </button>
                                </div>
                                <div class="file-icon-row">
                                    <i class="fas fa-file-alt document-icon me-2"
                                        style="font-size: 32px; cursor: pointer;"
                                        onclick="showFile('<?php echo htmlspecialchars($form_data['file_path']); ?>')"></i>
                                    <span>คลิกที่ไอคอนเพื่อดูไฟล์</span>
                                </div>
                            </div>
                            <?php else: ?>
                            <p>ไม่มีไฟล์ข้อสอบ</p>
                            <?php endif; ?>
                        </div>

                        <!-- อัพโหลดไฟล์ข้อสอบใหม่ -->
                        <?php if (empty($form_data['file_path'])): ?>
                        <div class="mb-3">
                            <label for="new_exam_file" class="form-label">อัพโหลดไฟล์ข้อสอบใหม่</label>
                            <input type="file" class="form-control" id="new_exam_file" name="new_exam_file"
                                accept=".doc,.docx,.pdf">
                        </div>
                        <?php endif; ?>


                        <!-- File Viewer Modal -->
                        <div class="modal fade" id="fileViewerModal" tabindex="-1"
                            aria-labelledby="fileViewerModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="fileViewerModalLabel">ดูไฟล์</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <iframe id="fileViewer" src="" style="width: 100%; height: 500px;"
                                            frameborder="0"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="container">
                            <div class="button-container">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>บันทึกการแก้ไข
                                </button>
                                <button type="button" class="btn btn-danger btn-lg" id="cancelEditButton">
                                    <i class="fas fa-times-circle me-2"></i>ยกเลิกการแก้ไข
                                </button>
                            </div>
                        </div>



                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <!-- Moment.js -->
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <!-- jQuery DateTimePicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js">
    </script>
    <!-- jQuery DateTimePicker CSS -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">
    <!-- เรียกใช้ jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <!-- เรียกใช้ jQuery UI JavaScript -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <script>
    $(document).ready(function() {
        //chang course_name and curriculum
        $('#course_code').change(function() {
            var selectedOption = $(this).find('option:selected');
            var courseName = selectedOption.data('name');
            var curriculum = selectedOption.data('curriculum');

            $('#course_name').val(courseName);
            $('#curriculum').val(curriculum);
        });

        // Trigger change event on page load if a course is already selected
        $('#course_code').trigger('change');

        // Date picker configuration
        $.datepicker.regional['th'] = {
            closeText: 'ปิด',
            prevText: 'ก่อนหน้า',
            nextText: 'ถัดไป',
            currentText: 'วันนี้',
            monthNames: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
            ],
            monthNamesShort: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
            ],
            dayNames: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'],
            dayNamesShort: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'],
            dayNamesMin: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'],
            weekHeader: 'สัปดาห์',
            dateFormat: 'dd/mm/yy',
            firstDay: 0,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };

        $.datepicker.setDefaults($.datepicker.regional['th']);

        function toBuddhistYear(date) {
            return new Date(date.getFullYear(), date.getMonth(), date.getDate());
        }

        $('.datepicker').datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            isBuddhist: true,
            defaultDate: toBuddhistYear(new Date()),
            beforeShow: function(input, inst) {
                var widget = $(inst.dpDiv);
                widget.css('font-size', '0.8em');
                $(input).val('');
            },
            onSelect: function(dateText, inst) {
                var date = $(this).datepicker('getDate');
                var buddhistYear = date.getFullYear() + 543;
                $(this).val($.datepicker.formatDate('dd/mm/', date) + buddhistYear);
            },
            yearRange: '-100:+0'
        });

        var currentYear = new Date().getFullYear() + 543;
        $('.datepicker').datepicker('option', 'yearRange', (currentYear - 100) + ':' + currentYear);

        // Time picker
        $('.timepicker').datetimepicker({
            datepicker: false,
            format: 'H:i',
            step: 5
        });

        // Edit date/time button
        $('.edit-date-time').click(function() {
            var targetId = $(this).data('target');
            var $input = $('#' + targetId);
            if ($input.hasClass('datepicker')) {
                $input.datepicker('show');
            } else if ($input.hasClass('timepicker')) {
                $input.datetimepicker('show');
            }
        });

        // Calculate total exam time
        function calculateTotalTime() {
            var startTime = $('#exam_time_start').val();
            var endTime = $('#exam_time_end').val();
            if (startTime && endTime && typeof moment !== 'undefined') {
                var start = moment(startTime, 'HH:mm');
                var end = moment(endTime, 'HH:mm');
                var duration = moment.duration(end.diff(start));
                var hours = Math.floor(duration.asHours());
                var minutes = duration.minutes();
                $('#total_exam_time').val(hours + ' ชั่วโมง ' + minutes + ' นาที');
            }
        }

        $('#exam_time_start, #exam_time_end').on('change', calculateTotalTime);
        calculateTotalTime();

        //ส่วนจัดการชื่อผู้ออกข้อสอบ
        const container = document.getElementById('examiner_names_container');
        const addButton = document.getElementById('add_examiner_name');

        // Add new examiner dropdown
        addButton.addEventListener('click', function() {
            const items = container.getElementsByClassName('examiner-item');
            const newItem = items[0].cloneNode(true);
            const index = items.length + 1;

            // Update index number examiner
            newItem.querySelector('.input-group-text').textContent = index;

            // Reset select value
            newItem.querySelector('select').value = '';

            // Add remove button functionality examiner
            newItem.querySelector('.remove-examiner').addEventListener('click', function() {
                this.closest('.examiner-item').remove();
                updateIndexNumbers();
            });

            container.appendChild(newItem);
        });

        // Add remove functionality to initial examiner
        document.querySelector('.remove-examiner').addEventListener('click', function() {
            if (container.getElementsByClassName('examiner-item').length > 1) {
                this.closest('.examiner-item').remove();
                updateIndexNumbers();
            }
        });

        // Update index numbers examiner
        function updateIndexNumbers() {
            const items = container.getElementsByClassName('examiner-item');
            for (let i = 0; i < items.length; i++) {
                items[i].querySelector('.input-group-text').textContent = i + 1;
            }
        }


        // Update input visibility for exam methods and equipment
        function updateInputVisibility(checkbox) {
            var input = $(checkbox).closest('.form-check').find('.input-inline');
            if (checkbox.checked) {
                input.removeClass('d-none').addClass('d-inline-block').prop('required', true);
            } else {
                input.removeClass('d-inline-block').addClass('d-none').prop('required', false).val('');
            }
        }

        $('.exam-method, .exam-equipment-checkbox').each(function() {
            updateInputVisibility(this);
        }).change(function() {
            updateInputVisibility(this);
        });

        // Toggle other forms visibility
        $('#other_forms_checkbox').change(function() {
            $('#other_forms_container').toggleClass('d-none', !this.checked);
        });

        // Show file
        window.showFile = function(filePath) {
            var fileViewer = document.getElementById('fileViewer');
            fileViewer.src = filePath;
            var fileModal = new bootstrap.Modal(document.getElementById('fileViewerModal'));
            fileModal.show();
        };

        function showFile(filePath) {
            var fileViewer = document.getElementById('fileViewer');
            fileViewer.src = filePath;
            var fileModal = new bootstrap.Modal(document.getElementById('fileModal'));
            fileModal.show();
        }



        $(document).ready(function() {
            //ส่วนจัดการชื่อผู้ออกข้อสอบ
            const examinerContainer = $('#examiner_names_container');
            const examiners = <?php echo json_encode($examiners); ?>;

            // Add new examiner dropdown
            $('#add_examiner_name').click(function() {
                const items = examinerContainer.find('.examiner-item');
                const index = items.length + 1;

                const newSelect = $('<select>').addClass('form-select examiner-select')
                    .attr('name', 'examiner_names[]')
                    .attr('required', true);

                newSelect.append($('<option>').val('').text('เลือกผู้ออกข้อสอบ'));
                examiners.forEach(examiner => {
                    newSelect.append($('<option>')
                        .val(examiner.full_name)
                        .text(examiner.full_name));
                });

                const newItem = $('<div>').addClass('examiner-item mb-2')
                    .append($('<div>').addClass('input-group')
                        .append($('<span>').addClass('input-group-text').text(index))
                        .append(newSelect)
                        .append($('<button>').addClass('btn btn-danger remove-examiner')
                            .append($('<i>').addClass('fas fa-trash'))
                        )
                    );

                examinerContainer.append(newItem);
                updateExaminerNumbers();
            });

            // Remove examiner handling
            $(document).on('click', '.remove-examiner', function() {
                if (examinerContainer.find('.examiner-item').length > 1) {
                    $(this).closest('.examiner-item').remove();
                    updateExaminerNumbers();
                } else {
                    alert('ต้องมีผู้ออกข้อสอบอย่างน้อย 1 คน');
                }
            });

            // Update examiner numbers
            function updateExaminerNumbers() {
                examinerContainer.find('.examiner-item').each((index, item) => {
                    $(item).find('.input-group-text').text(index + 1);
                });
            }

            // เรียกใช้ฟังก์ชันเพื่อแสดงแบบฟอร์มที่มีอยู่
            if ($('#other_forms_checkbox').is(':checked')) {
                displayExistingOtherForms();
            }

            // แสดงแบบฟอร์มอื่นๆที่มีอยู่
            function displayExistingOtherForms() {
                if (!$('#other_forms_checkbox').is(':checked')) {
                    $('#other_forms_container').addClass('d-none');
                    return;
                }
                $('#other_forms_table tbody').empty();

                otherForms.forEach((form, index) => {
                    const count = otherFormCounts[index] || '';
                    const filePath = otherFormFiles[index] || '';
                    const fileName = filePath.split('/').pop();

                    addOtherFormRow(index + 1, form, count, filePath, fileName);
                });

                // ถ้าไม่มีแบบฟอร์มอื่นๆ ให้เพิ่มแถวว่างหนึ่งแถว
                if ($('#other_forms_table tbody tr').length === 0) {
                    addNewOtherForm();
                }

                // แสดง container ของแบบฟอร์มอื่นๆ
                $('#other_forms_container').removeClass('d-none');
                $('#other_forms_checkbox').prop('checked', true);
            }

            // ฟังก์ชันเพิ่มแถวใหม่สำหรับแบบฟอร์มอื่นๆ
            function addNewOtherForm() {
                const rowCount = $('#other_forms_table tbody tr').length;
                addOtherFormRow(rowCount + 1);
            }

            // ฟังก์ชันสร้างแถวสำหรับแบบฟอร์มอื่นๆ
            function addOtherFormRow(rowNumber, form = '', count = '', filePath = '', fileName = '') {
                const newRow = `
            <tr>
                <td>${rowNumber}</td>
                <td><input type='text' class='form-control' name='other_form_desc[]' value='${form}' required></td>
                <td><input type='number' class='form-control numeric-input' name='other_form_count[]' value='${count}' min='0' required></td>
                <td>
                    ${filePath ? createFileDisplay(filePath, fileName) : createFileUpload()}
                </td>
                <td>
                    <button type='button' class='btn btn-danger btn-sm remove-form'><i class='fas fa-trash'></i> ลบ</button>
                </td>
            </tr>`;
                $('#other_forms_table tbody').append(newRow);
            }

            // ฟังก์ชันสร้าง HTML สำหรับแสดงไฟล์ที่มีอยู่
            function createFileDisplay(filePath, fileName) {
                return `
            <div class="file-display">
                <div class="file-icon-row">
                    
                     <?php if (!empty($form_data['other_form_file'])): ?>                                                              
                        <i class="fas fa-file-alt document-icon me-2"
                            style="font-size: 32px; cursor: pointer;"
                            onclick="showFile('<?php echo htmlspecialchars($form_data['other_form_file']); ?>')">
                        </i>  
                        <spen>กดไอคอนเพื่อเปิดไฟล์</spen>                                                               
                            
                    <?php else: ?>
                        <p>ไม่มีไฟล์แบบฟอร์มอื่นๆ</p>
                    <?php endif; ?>
                </div>
            </div>
            <input type='hidden' name='other_form_file[]' value='${filePath}'>`;
            }

            // ฟังก์ชันสร้าง HTML สำหรับอัปโหลดไฟล์ใหม่
            function createFileUpload() {
                return '<input type="file" class="form-control other-form-file" name="other_form_file[]" accept=".doc,.docx,.pdf">';
            }

            // เพิ่มแบบฟอร์มอื่นๆ
            $('#add_other_form').click(addNewOtherForm);

            // ลบแบบฟอร์มอื่นๆ
            $(document).on('click', '.remove-form', function() {
                $(this).closest('tr').remove();
                updateOtherFormNumbers();
            });

            // อัปเดตลำดับของแบบฟอร์มอื่นๆ
            function updateOtherFormNumbers() {
                $('#other_forms_table tbody tr').each((index, element) => {
                    $(element).find('td:first').text(index + 1);
                });
            }

            // เรียกใช้ฟังก์ชันเพื่อแสดงแบบฟอร์มที่มีอยู่
            displayExistingOtherForms();

            // อัปเดตการแสดงผลของ container เมื่อ checkbox เปลี่ยนแปลง
            $('#other_forms_checkbox').change(function() {
                $('#other_forms_container').toggleClass('d-none', !this.checked);
            });

        });

        // ปุ่มยกเลิกการแก้ไข
        $('#cancelEditButton').click(function() {
            var confirmCancel = confirm(
                "คุณแน่ใจหรือไม่ที่จะยกเลิกการแก้ไข? ข้อมูลที่เปลี่ยนแปลงจะไม่ถูกบันทึก");
            if (confirmCancel) {
                var id = <?php echo json_encode($form_id); ?>;
                window.location.href = 'document_details.php?id=' + id;
            }
        });

        // Form submission
        $('#editExamForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            // Add new exam file if exists
            var newExamFileInput = document.getElementById('new_exam_file');
            if (newExamFileInput && newExamFileInput.files.length > 0) {
                formData.append('new_exam_file', newExamFileInput.files[0]);
            }

            // Add exam methods
            var examMethods = $('.exam-method:checked').map(function() {
                var method = $(this).val();
                var count = $(this).closest('.form-check').find('.input-inline').val();
                return method + (count ? ': ' + count : '');
            }).get().join('/');
            formData.set('exam_methods', examMethods);

            // Add other forms
            var otherForms = $('#other_forms_table tbody tr').map(function() {
                var desc = $(this).find('input[name="other_form_desc[]"]').val();
                var count = $(this).find('input[name="other_form_count[]"]').val();
                return desc + ': ' + count;
            }).get().join('/');
            formData.set('other_form', otherForms);

            // Add allowed equipment
            var allowedEquipment = $('.exam-equipment-checkbox:checked').map(function() {
                var equipment = $(this).next('label').text();
                var detail = $(this).closest('.form-check').find('.input-inline').val();
                return equipment + (detail ? ': ' + detail : '');
            }).get().join('/');
            formData.set('allowed_equipment', allowedEquipment);

            $.ajax({
                url: 'update_form.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location.href = 'document_list.php';
                    } else {
                        alert("เกิดข้อผิดพลาด: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Ajax error:", status, error);
                    alert("เกิดข้อผิดพลาดในการส่งข้อมูล: " + error);
                }
            });
        });



        // Go back function
        window.goBack = function() {
            var confirmLeave = confirm("ข้อมูลที่แก้ไขจะไม่ถูกบันทึก คุณต้องการย้อนกลับหรือไม่?");
            if (confirmLeave) {
                var id = <?php echo json_encode($form_id); ?>;
                window.location.href = 'document_details.php?id=' + id;
            }
        };
    });
    </script>
</body>

</html>