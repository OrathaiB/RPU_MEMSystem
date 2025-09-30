<?php

include 'config.php';

session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'user_nav.php';


?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - ระบบจัดการข้อสอบกลางภาค</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap"
        rel="stylesheet">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Sarabun', sans-serif;
        background-color: #f4f6f9;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .dashboard-title {
        text-align: center;
        margin: 20px 0;
        color: #333;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .menu-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .menu-card:hover {
        transform: translateY(-5px);
    }

    .menu-card i {
        font-size: 40px;
        margin-bottom: 15px;
        color: #4a90e2;
    }

    .menu-card h4 {
        margin-bottom: 10px;
        color: #333;
    }

    .menu-card p {
        color: #666;
        font-size: 14px;
    }

    .menu-card a {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .user-info {
        text-align: right;
        padding: 10px 20px;
        background: #fff;
        margin-bottom: 20px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    </style>
</head>

<body>
    <div class="container">

        <h1 class="dashboard-title">ระบบจัดการข้อสอบกลางภาค</h1>

        <div class="menu-grid">

            <!-- ข้อมูลหลักสูตร -->
            <div class="menu-card">
                <a href="user_courses.php">
                    <i class="fas fa-book"></i>
                    <h4>ข้อมูลหลักสูตร</h4>
                    <p>ดูรายละเอียดข้อมูลหลักสูตรต่างๆ</p>
                </a>
            </div>

            <!-- รายการคำขอ -->
            <div class="menu-card">
                <a href="user_formrequests.php">
                    <i class="fas fa-clipboard-list"></i>
                    <h4>สร้างแบบฟอร์ม</h4>
                    <p>สร้างแบบฟอร์มข้ออนุมัติข้อสอบ</p>
                </a>
            </div>

            
            <!-- เอกสารที่อนุมัติแล้ว -->
            <div class="menu-card">
                <a href="user_doc.php">
                    <i class="fas fa-check-circle"></i>
                    <h4>สถานะเอกสาร</h4>
                    <p>ตรวจสอบรายการเอกสาร</p>
                </a>
            </div>
            

           



   



            <!-- เอกสารที่รออนุมัติแล้ว -->
            <!-- <div class="menu-card">
                <a href="user_waitapproved_list.php">
                    <i class="fas fa-solid fa-clock"></i>
                    <h4>เอกสารที่รออนุมัติ</h4>
                    <p>ตรวจสอบรายการเอกสารรออนุมัติ</p>
                </a>
            </div> -->

            <!-- เอกสารที่ไม่อนุมัติ -->
            <!-- <div class="menu-card">
                <a href="user_unapproved_list.php">
                    <i class="fas  fa-xmark"></i>
                    <h4>เอกสารที่ไม่อนุมัติ</h4>
                    <p>ตรวจสอบรายการเอกสารไม่อนุมัติ</p>
                </a>
            </div> -->

            <!-- เอกสารที่จัดพิมพ์แล้ว -->
            <!-- <div class="menu-card">
                <a href="user_printed.php">
                    <i class="fas fa-solid fa-print"></i>
                    <h4>เอกสารที่จัดพิมพ์แล้ว</h4>
                    <p>ตรวจสอบรายการเอกสารที่จัดพิมพ์แล้ว</p>
                </a>
            </div> -->

        </div>
    </div>
</body>

</html>