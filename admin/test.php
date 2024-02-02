<?
require_once '../email.php';
require_once '../utils.php';

/*
//send an email to gosdens025012@tbshs.org
sendEmail("andersonr025361@tbshs.org", "email", "https://www.samgosden.tech/registration.php?token=a");
//get all register, for each send an email with a link to https://www.samgosden.tech/register.php?token=token
$registers = get_all_register();
foreach ($registers as $register) {
    $token = $register->getToken();
    $email = $register->getEmail();
    $name = $register->getName();
    $subject = "Register for parents evening";
    $message = "Dear $name, <br> Please click the link below to register for the parents evening system. https://www.samgosden.tech/registration.php?token=$token";
    sendEmail($email, $subject, $message);
}*/

/*
*/
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
//test all the get all functions#

/*
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
*/
//if its a post with the to subject and body, send the email
if (isset($_POST['to']) && isset($_POST['subject']) && isset($_POST['body'])) {
    $to = $_POST['to'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    if (isset($_POST['html'])) {
        $html = $_POST['html'];
    } else {
        $html = false;
    }
    echo sendEmail($to, $subject, $body, $html);
    echo "sent";
    die();
}
//if its a post with sendAll, send all the emails
if (isset($_POST['sendAll'])) {
    $registers = get_all_toreset();
    foreach ($registers as $register) {
        $token = $register->getResetToken();
        $email = $register->getEmail();
        $name = $register->getName();
        $subject = "Register for parents evening";
        $message = " <head>
        <style>
            .register-link {
                color: #ffffff;
                background-color: #007BFF;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
            }
    
            .register-link:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    Dear $name, 
    <br> 
    Please click the link below to register for the parents evening system. <br> <br> <a href=https://www.samgosden.tech/registration.php?token=$token class='register-link'>Register</a> <br> <br> If the link does not work, please copy and paste the following link into your browser: <br> https://www.samgosden.tech/registration.php?token=$token";
        echo sendEmail($email, $subject, $message, true);
        echo $token . "<br>";
    }
    echo "sent " . count($registers) . " emails";
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- email send testing -->

<head>
    <? include_once '../includes.php'; ?>
    <meta charset="UTF-8">
    <title>Test</title>

    <script>
        //preventDefault send a post to the same page with the relevent data
        function test() {
            var to = document.getElementById("to").value;
            var subject = document.getElementById("subject").value;
            var body = document.getElementById("body").value;
            var html = document.getElementById("html").checked;
            $.post("test.php", {
                to: to,
                subject: subject,
                body: body,
                html: html
            }, function(data) {
                alert(data);
            });
        }
        //overide button click
    </script>
</head>
<!-- add a to subject and body-->

<body>
    <form>
        <input type="text" name="to" id="to">
        <input type="text" name="subject" id="subject">
        <input type="text" name="body" id="body">
        <input type="checkbox" name="html" id="html">
    </form>
    <button id="submit">Send</button>
    <button id="sendAll">Send all</button>
</body>
<script>
    window.onload = function() {
        document.getElementById("submit").addEventListener("click", function(e) {
            e.preventDefault();
            test();
        });
        document.getElementById("sendAll").addEventListener("click", function(e) {
            e.preventDefault();
            $.post("test.php", {
                sendAll: true
            }, function(data) {
                alert(data);
            });
        });
    }
</script>

</html>