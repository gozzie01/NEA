<?php
/*

require_once '../utils.php';
//check if the user is logged in
require_once './autils.php';
$events = get_all_events();
foreach ($events as $event) {
    echo $event->getName() . "<br>";
    echo format_date($event->getStartTime()) . "<br>";

}
?>
<!DOCTYPE html>
<html lang="en">*/
/*
//test pdf generation
require_once '../utils.php';
require_once '../fpdf.php';
//make a new pdf
$pdf = new FPDF();
//landscape A4
$pdf->AddPage('L', 'A4');
//set the font
$pdf->SetFont('Arial', 'B', 16);
//add some text
$pdf->Cell(40, 10, 'Hello World!');
//write a small  table
$pdf->Ln();
$pdf->Cell(40, 10, 'Table');
$pdf->Ln();
$pdf->Cell(40, 10, '1', 1);
$pdf->Cell(40, 10, '2', 1);
$pdf->Cell(40, 10, '3', 1);
$pdf->Ln();
$pdf->Cell(40, 10, '4', 1);
$pdf->Cell(40, 10, '5', 1);
$pdf->Cell(40, 10, '6', 1);
$pdf->Ln();
//output the pd
$pdf->Output();
*/
//test all the get all functions
require_once '../utils.php';

require_once './autils.php';
require_once '../tcpdf/tcpdf.php';
//add a button and a text box, the text box will contain a teacher id, print a pdf with a list of the classes
if(isset($_GET['teacherid'])){
    //dont send any data
    $teacherid = $_GET['teacherid'];
    $teacher = new Teacher($teacherid);
    $teacher->update();
    $classes = $teacher->getClasses();
    $dompdf = new TCPDF();
    //landscape A4
    //remove margins and footer
    $pdf->SetMargins(0, 0, 0);
    $pdf->setPrintFooter(false);
    $websiteContent = file_get_contents('https://www.samgosden.tech/test.php?teacher='.$teacherid);
    $pdf->AddPage('L', 'A4');
    $pdf->writeHTML($websiteContent);
    $pdf->Output();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test</title>
    <script>
        function test() {
            var teacherid = document.getElementById("teacherid").value;
            window.location.href = "test.php?teacherid=" + teacherid;
        }
        //overide button click
        document.getElementById("submit").addEventListener("click", function (e) {
            e.preventDefault();
            test();
        });
    </script>
</head>
<body>
<form action="test.php" method="get">
    <input type="text" name="teacherid">
    <input type="submit">
</form>
</body>
</html>
