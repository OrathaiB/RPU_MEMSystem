<?php
require_once 'config.php';
include 'nav.php';
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
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col col-sm-12">
            <div class="alert alert-primary" role="alert">
                <center><h4>ระบบจัดการข้อสอบกลางภาค มหาวิทยาลัยราชพฤกษ์</h4></center>
            </div>
        </div>
    </div>
    
    <div class="row">
    <?php
    $cards = [
        ['bg-primary', 'file-tray-full', 'ข้อสอบทั้งหมด', 'จำนวน 5,000 คน' ],
        ['bg-warning', 'sync', 'รออนุมัติ', 'จำนวน 900,000 ออเดอร์' ],
        ['bg-success', 'checkmark', 'อนุมัติแล้ว', 'จำนวน 9,999 รายการ'],
        ['bg-danger', 'create', 'รอแก้ไขเอกสาร', '11,500,000 บาท']
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


    <div class="row">
        <div class="col-sm-12">
            <canvas id="myChart" height="100px"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById("myChart").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['2022', '2021', '2020', '2019'],
            datasets: [{
                label: 'รายงานภาพรวม แยกตามปี (บาท)',
                data: [1000000, 2500000, 5000000, 3000000],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255,99,132,1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>