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
//test pdf generation
require_once '../utils.php';
require_once '../fpdf.php';
//make a new pdf
$pdf = new FPDF();
//landscape A4
$pdf->AddPage('L','A4');
//set the font
$pdf->SetFont('Arial','B',16);
//add some text
$pdf->Cell(40,10,'Hello World!');
//write a small  table
$pdf->Ln();
$pdf->Cell(40,10,'Table');
$pdf->Ln();
$pdf->Cell(40,10,'1',1);
$pdf->Cell(40,10,'2',1);
$pdf->Cell(40,10,'3',1);
$pdf->Ln();
$pdf->Cell(40,10,'4',1);
$pdf->Cell(40,10,'5',1);
$pdf->Cell(40,10,'6',1);
//output the pd
$pdf->Output();
?>