<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบฟอร์มการขอจัดทำข้อสอบ</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .examiner-item,
    .other-form-item {
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
    </style>

    <?php
// เริ่ม session และเชื่อมต่อฐานข้อมูล
session_start();
require_once 'config.php';

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['username'])) {
    // ถ้าไม่มีการล็อกอิน ให้ redirect ไปยังหน้า login
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลผู้ใช้จาก session
$username = $_SESSION['username'];

// Query เพื่อดึงชื่อเต็มของผู้ใช้
$sql = "SELECT full_name FROM datalogin WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

                       
$sql = "SELECT course_code, course_name, curriculum FROM courses";
$result = $conn->query($sql);
                    


// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>


</head>

<body>
    <?php include 'user_nav.php'; ?>

    <div class="container">

        <div class="form-container">

            <div>
                <button onclick="goBack()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                </button>
            </div>

            <br>
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0"><i class="fas fa-file-alt me-2"></i>แบบฟอร์มการขอจัดทำข้อสอบ</h2>
                </div>
                <div class="card-body">
                    <form id="examForm" action="process.php" method="post" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="term" class="form-label">ภาคเรียนที่</label>
                                <select class="form-select" id="term" name="term" required>
                                    <option value="">เลือกภาคเรียน</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="yeart" class="form-label">ปีการศึกษา</label>
                                <select class="form-select" id="yeart" name="yeart" required>
                                    <option value="">เลือกปีการศึกษา</option>
                                </select>
                            </div>
                            <input type="hidden" id="semester" name="semester">
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="course_code" class="form-label">รหัสวิชา</label>
                                <select class="form-control" id="course_code" name="course_code">
                                    <option value="">กรุณาเลือกรหัสรายวิชา</option>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['course_code'] . "' data-name='" . $row['course_name'] . "' data-curriculum='" . $row['curriculum'] . "'>" . $row['course_code'] . " - " . $row['course_name'] . "</option>";
                                        }
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
                                    <option value="สาขาวิชาเทคโนโลยีดิจิทัลเพื่อธุรกิจ">
                                        สาขาวิชาเทคโนโลยีดิจิทัลเพื่อธุรกิจ</option>
                                    <option value="สาขาวิชาเทคโนโลยีดิจิทัล">สาขาวิชาเทคโนโลยีดิจิทัล</option>
                                    <option value="สาขาวิชาการตลาดดิจิทัล">สาขาวิชาการตลาดดิจิทัล</option>
                                    <option value="สาขาวิชาบัญชี">สาขาวิชาบัญชี</option>
                                    <option value="สาขาวิชาการจัดการ">สาขาวิชาการจัดการ</option>
                                </select>
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="manuscript" class="form-label">ต้นฉบับ (จำนวนหน้า)</label>
                                <input type="text" class="form-control" id="manuscript" name="manuscript" required>
                            </div>
                            <div class="col-md-6">
                                <label for="numberofsets" class="form-label">จำนวนชุดที่ต้องการจัดพิมพ์</label>
                                <input type="number" class="form-control" id="numberofsets" name="numberofsets"
                                    required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="exam_date" class="form-label">วันที่สอบ</label>
                                <input type="text" id="exam_date" name="exam_date" class="form-control datepicker"
                                    readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="exam_time_start" class="form-label">เวลาเริ่มสอบ</label>
                                <input type="text" id="exam_time_start" name="exam_time_start"
                                    class="form-control timepicker" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="exam_time_end" class="form-label">เวลาสิ้นสุดสอบ</label>
                                <input type="text" id="exam_time_end" name="exam_time_end"
                                    class="form-control timepicker" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="total_exam_time" class="form-label">เวลาสอบรวม</label>
                            <input type="text" id="total_exam_time" name="total_exam_time" class="form-control"
                                readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ชื่อผู้ออกข้อสอบ</label>
                            <div id="examiner_names_container">
                                <div class="examiner-item">
                                    <div class="input-group">

                                        <input type="text" class="form-control" name="examiner_name"
                                            value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                                        <input type="hidden" name="examiner_id"
                                            value="<?php echo htmlspecialchars($username); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="mt-4 mb-4">

                        <div class="mb-3">
                            <label class="form-label">วิธีการสอบ</label>
                            <div class="exam-method-container">
                                <div class="form-check">
                                    <input class="form-check-input exam-method" type="checkbox" id="answer_in_exam"
                                        name="exam_method[]" value="ตอบคำถามในข้อสอบ">
                                    <label class="form-check-label" for="answer_in_exam">ตอบคำถามในข้อสอบ</label>
                                    <input type="number" class="form-control d-none input-inline"
                                        id="answer_in_exam_count" name="answer_in_exam_count"
                                        placeholder="จำนวนแผ่น/ข้อ" style="width: 200px;">
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input exam-method" type="checkbox" id="answer_book"
                                        name="exam_method[]" value="กระดาษทำการเขียนคำตอบ">
                                    <label class="form-check-label" for="answer_book">กระดาษทำการเขียนคำตอบ</label>
                                    <input type="number" class="form-control d-none input-inline" id="answer_book_count"
                                        name="answer_book_count" placeholder="จำนวนแผ่น" style="width: 200px;">
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input exam-method" type="checkbox" id="choice_120"
                                        name="exam_method[]" value="กระดาษคำตอบปรนัย">
                                    <label class="form-check-label" for="choice_120">กระดาษคำตอบปรนัย (Choice 120
                                        ข้อ)</label>
                                    <input type="number" class="form-control d-none input-inline" id="choice_120_count"
                                        name="choice_120_count" placeholder="จำนวนแผ่น" style="width: 200px;">
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input exam-method" type="checkbox" id="other_form"
                                        name="exam_method[]" value="แบบฟอร์มอื่นๆ">
                                    <label class="form-check-label" for="other_form">แบบฟอร์มอื่นๆ</label>
                                </div>
                            </div>
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

                        <hr class="mt-4 mb-4">
                        <div class="mb-3">
                            <label class="form-label">การนำอุปกรณ์หรือเอกสารเข้าห้องสอบ</label>
                            <div class="exam-equipment-container">
                                <div class="form-check">
                                    <input type="checkbox" id="exam_document"
                                        class="form-check-input exam-equipment-checkbox">
                                    <label for="exam_document" class="form-check-label">นำเอกสารเข้าห้องสอบ</label>
                                    <input type="text" id="exam_document_detail"
                                        class="form-control d-none input-inline" placeholder="กรุณาระบุชื่อเอกสาร"
                                        style="width: 500px;">
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="open_book"
                                        class="form-check-input exam-equipment-checkbox">
                                    <label for="open_book" class="form-check-label">เปิดตำรา</label>
                                    <input type="text" id="open_book_detail" class="form-control d-none input-inline"
                                        placeholder="กรุณาระบุชื่อตำราที่อนุญาตให้นำเข้า" style="width: 500px;">
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="calculator"
                                        class="form-check-input exam-equipment-checkbox">
                                    <label for="calculator" class="form-check-label">เครื่องคำนวณ</label>

                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="other_equipment"
                                        class="form-check-input exam-equipment-checkbox">
                                    <label for="other_equipment" class="form-check-label">อุปกรณ์อื่นๆ</label>
                                    <input type="text" id="other_equipment_detail"
                                        class="form-control d-none input-inline" placeholder="กรุณาระบุรายละเอียด"
                                        style="width: 500px;">
                                </div>
                            </div>
                        </div>

                        <hr class="mt-4 mb-4">

                        <div class="mb-3">
                            <label class="form-label">แนบไฟล์ข้อสอบ</label>
                            <input type="file" class="form-control" id="exam_file" name="exam_file"
                                accept=".doc,.docx,.pdf" required>
                        </div>

                        <div class="mb-3">
                            <label for="additional_details" class="form-label">รายละเอียดอื่นๆ</label>
                            <textarea class="form-control" id="additional_details" name="additional_details" rows="3"
                                placeholder="กรุณาระบุรายละเอียดเพิ่มเติม (ถ้ามี)"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>บันทึก
                        </button>
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
    //ส่วนเลือกรหัสวิชา ชื่อวิชา และหลักสูตร
    document.addEventListener('DOMContentLoaded', function() {
        var courseSelect = document.getElementById('course_code');
        var courseNameInput = document.getElementById('course_name');
        var curriculumInput = document.getElementById('curriculum');

        courseSelect.addEventListener('change', function() {
            var selectedOption = courseSelect.options[courseSelect.selectedIndex];

            if (selectedOption.value) {
                courseNameInput.value = selectedOption.getAttribute('data-name');
                curriculumInput.value = selectedOption.getAttribute('data-curriculum');
            } else {
                courseNameInput.value = '';
                curriculumInput.value = '';
            }
        });
    });

    $(document).ready(function() {
        // สร้าง dropdownlist ปีการศึกษา
        function populateYearDropdown() {
            var select = document.getElementById('yeart');
            var currentYear = new Date().getFullYear() + 543; // แปลงเป็นปี พ.ศ.
            for (var i = 0; i < 10; i++) {
                var year = currentYear + i;
                var option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                select.appendChild(option);
            }
        }

        function updateSemester() {
            var term = document.getElementById('term').value;
            var yeart = document.getElementById('yeart').value;
            if (term && yeart) {
                var semester = term + '/' + yeart;
                document.getElementById('semester').value = semester;
            } else {
                document.getElementById('semester').value = '';
            }
        }

        // เรียกใช้ฟังก์ชัน dropdownlist ปีการศึกษา
        populateYearDropdown();
        $('#term').on('change', updateSemester);
        $('#yeart').on('change', updateSemester);

        // เพิ่ม jQuery UI Datepicker ภาษาไทย
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

        // ฟังก์ชันสำหรับแปลงปี ค.ศ. เป็น พ.ศ.
        function toBuddhistYear(date) {
            var year = date.getFullYear();
            return new Date(year, date.getMonth(), date.getDate());
        }

        // ฟังก์ชันสำหรับแปลงปี พ.ศ. เป็น ค.ศ.
        function toChristianYear(date) {
            var year = date.getFullYear() - 543;
            return new Date(year, date.getMonth(), date.getDate());
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
                var buddhistYear = date.getFullYear();
                $(this).val($.datepicker.formatDate('dd/mm/', date) + buddhistYear);
            },
            yearRange: '-100:+0'
        });

        // ปรับแต่ง yearRange เพื่อแสดงช่วงปี พ.ศ.
        var currentYear = new Date().getFullYear() + 543; // แปลงเป็นปี พ.ศ.
        $('.datepicker').datepicker('option', 'yearRange', currentYear + ':' + currentYear);

        // Time picker initialization
        $('.timepicker').datetimepicker({
            datepicker: false,
            format: 'H:i',
            step: 5
        });

        // Calculate total exam time
        $('#exam_time_start, #exam_time_end').on('change', calculateTotalTime);

        function calculateTotalTime() {
            var startTime = $('#exam_time_start').val();
            var endTime = $('#exam_time_end').val();
            if (startTime && endTime) {
                var start = moment(startTime, 'HH:mm');
                var end = moment(endTime, 'HH:mm');
                var duration = moment.duration(end.diff(start));
                var hours = Math.floor(duration.asHours());
                var minutes = duration.minutes();
                $('#total_exam_time').val(hours + ' ชั่วโมง ' + minutes + ' นาที');
            }
        }


        // ส่วน exam methods and equipment
        $('.exam-method, .exam-equipment-checkbox').change(function() {
            var input = $(this).parent().find('.input-inline');
            input.toggleClass('d-none', !this.checked).prop('required', this.checked);
        });

        // Handle other forms
        $('#other_form').change(function() {
            if (this.checked) {
                $('#other_forms_container, #add_other_form').removeClass('d-none');
                if ($('.other-form-item').length === 0) {
                    addOtherForm();
                }
            } else {
                $('#other_forms_container, #add_other_form').addClass('d-none');
                $('#other_forms_container').empty();
            }
        });

        let otherFormCount = 0;
        $('#add_other_form').click(addOtherForm);

        function addOtherForm() {
        otherFormCount++;
        const newForm = `
        <tr class="other-form-item">
            <td>${otherFormCount}</td>
            <td><input type="text" class="form-control" name="other_form_desc[]" placeholder="ระบุชื่อแบบฟอร์ม" required></td>
            <td><input type="number" class="form-control numeric-input" name="other_form_count[]" placeholder="จำนวนแผ่น" min="0" required></td>
            <td><input type="file" class="form-control other-form-file" name="other_form_file[]" accept=".doc,.docx,.pdf" required></td>
            <td><button type="button" class="btn btn-danger remove-form"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        $('#other_forms_table tbody').append(newForm);
    }

    // แก้ไขการจัดการกับ checkbox "แบบฟอร์มอื่นๆ"
    $('#other_form').change(function() {
        if (this.checked) {
            $('#other_forms_container').removeClass('d-none');
            if ($('.other-form-item').length === 0) {
                addOtherForm();
            }
        } else {
            $('#other_forms_container').addClass('d-none');
            $('#other_forms_table tbody').empty();
            otherFormCount = 0;
        }
    });

    // แก้ไขฟังก์ชัน updateOtherFormNumbers
    function updateOtherFormNumbers() {
        $('.other-form-item').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
        otherFormCount = $('.other-form-item').length;
    }


        $(document).on('click', '.remove-form', function() {
            $(this).closest('.other-form-item').remove();
            updateOtherFormNumbers();
        });

       

        // ตรวจสอบค่าตัวเลขให้เป็นค่าไม่ติดลบ
        $(document).on('input', '.numeric-input', function() {
            var value = $(this).val();
            if (value !== '' && Number(value) < 0) {
                $(this).val(0);
            }
        });

        // Form submission
        $('#examForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            // แปลงจำนวนชุดที่ต้องการจัดพิมพ์เป็นข้อความ
            var numberofsets = $('input[name="numberofsets"]').val() + ' ชุด';
            formData.set('numberofsets', numberofsets);

            // เพิ่มเวลาสอบรวม
            formData.set('totalexam_time', $('#total_exam_time').val());

            // จัดการแบบฟอร์มอื่นๆ
            $('.other-form-item').each(function(index) {
                var desc = $(this).find('input[name="other_form_desc[]"]').val();
                var count = $(this).find('input[name="other_form_count[]"]').val();
                formData.append('other_form_desc[]', desc);
                formData.append('other_form_count[]', count);
                // ไฟล์จะถูกส่งโดยอัตโนมัติเนื่องจากเป็นส่วนหนึ่งของ form
            });

            // Add exam methods
            var examMethods = [];
            $('.exam-method:checked').each(function() {
                var method = $(this).val();
                var count = $(this).parent().find('.input-inline').val();
                examMethods.push(method + (count ? ': ' + count : ''));
            });
            formData.set('exam_methods', examMethods.join('/'));

            // Add allowed equipment
            var allowedEquipment = [];
            $('.exam-equipment-checkbox:checked').each(function() {
                var equipment = $(this).next('label').text();
                var detail = $(this).parent().find('.input-inline').val();
                allowedEquipment.push(equipment + (detail ? ': ' + detail : ''));
            });
            formData.set('allowed_equipment', allowedEquipment.join('/'));


            $.ajax({
                url: 'process.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('บันทึกข้อมูลสำเร็จ');
                        window.location.href = 'user_formrequests.php';
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
    });
    </script>
    <script>
    function goBack() {
        window.location.href = 'user_index.php';
    }
    </script>

</body>

</html>