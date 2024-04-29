<?
require_once '../email.php';
require_once '../utils.php';
require_once './autils.php';
//stress test the get classes function of the classes page
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Test</title>
    <script>
        //post get table data to classes.php
        function test() {
            fetch("www.samgosden.tech/admin/classes.php", {
                method: "POST",
                body
            }).then(response => response.text()).then(data => {
                document.getElementById("table").innerHTML = data;
            });
        }
    </script>
</head>

<body>
<?php
//write the json to the page of the prefslot
?>
</body>
<?php
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

//get all the year 13 students
/*{
    "start": "17:00",
    "end": "19:00",
    "duration": 30,
    "teachers": [
        "1",
        "2",
        "3"
    ],
    "children": [
        "1",
        "2",
        "3"
    ],
    "appointments": [
        {
            "child": "1",
            "teacher": "1",
            "wanted": "0",
            "available": [
                "17:00",
                "17:30"
            ]
        },
        {
            "child": "1",
            "teacher": "2",
            "wanted": "0",
            "available": [
                "17:00",
                "17:30"
            ]
        },
        {
            "child": "1",
            "teacher": "3",
            "wanted": "0",
            "available": [
                "17:00",
                "17:30"
            ]
        },
        {
            "child": "2",
            "teacher": "1",
            "wanted": "0",
            "available": [
                "17:00",
                "17:30"
            ]
        },
        {
            "child": "2",
            "teacher": "2",
            "wanted": "0",
            "available": [
                "17:00",
                "17:30"
            ]
        },
        {
            "child": "2",
            "teacher": "3",
            "wanted": "0",
            "available": [
                "17:00",
                "17:30"
            ]
        },
        {
            "child": "3",
            "teacher": "1",
            "wanted": "0",
            "available": [
                "17:00",
                "17:30"
            ]
        },
        {
            "child": "3",
            "teacher": "2",
            "wanted": "0",
            "available": [
                "17:00",
                "17:30"
            ]
        },
        {
            "child": "3",
            "teacher": "3",
            "wanted": "0",
            "available": [
                "17:00",
                "17:30"
            ]
        },
        {
            "child": "1",
            "teacher": "1",
            "wanted": "1",
            "available": [
                "17:30",
                "18:00"
            ]
        },
        {
            "child": "1",
            "teacher": "2",
            "wanted": "1",
            "available": [
                "17:30",
                "18:00"
            ]
        },
        {
            "child": "1",
            "teacher": "3",
            "wanted": "1",
            "available": [
                "17:30",
                "18:00"
            ]
        },
        {
            "child": "2",
            "teacher": "1",
            "wanted": "1",
            "available": [
                "17:30",
                "18:00"
            ]
        },
        {
            "child": "2",
            "teacher": "2",
            "wanted": "1",
            "available": [
                "17:30",
                "18:00"
            ]
        },
        {
            "child": "2",
            "teacher": "3",
            "wanted": "1",
            "available": [
                "17:30",
                "18:00"
            ]
        },
        {
            "child": "3",
            "teacher": "1",
            "wanted": "1",
            "available": [
                "17:30",
                "18:00"
            ]
        },
        {
            "child": "3",
            "teacher": "2",
            "wanted": "1",
            "available": [
                "17:30",
                "18:00"
            ]
        },
        {
            "child": "3",
            "teacher": "3",
            "wanted": "1",
            "available": [
                "17:30",
                "18:00"
            ]
        },
        {
            "child": "1",
            "teacher": "1",
            "wanted": "0",
            "available": [
                "18:00",
                "18:30"
            ]
        },
        {
            "child": "1",
            "teacher": "2",
            "wanted": "0",
            "available": [
                "18:00",
                "18:30"
            ]
        },
        {
            "child": "1",
            "teacher": "3",
            "wanted": "0",
            "available": [
                "18:00",
                "18:30"
            ]
        },
        {
            "child": "2",
            "teacher": "1",
            "wanted": "0",
            "available": [
                "18:00",
                "18:30"
            ]
        },
        {
            "child": "2",
            "teacher": "2",
            "wanted": "0",
            "available": [
                "18:00",
                "18:30"
            ]
        },
        {
            "child": "2",
            "teacher": "3",
            "wanted": "0",
            "available": [
                "18:00",
                "18:30"
            ]
        },
        {
            "child": "3",
            "teacher": "1",
            "wanted": "0",
            "available": [
                "18:00",
                "18:30"
            ]
        }
    ],
    "unavailable": [
        {
            "teacher": "1",
            "unavailable": [
                "17:00",
                "17:30"
            ]
        },
        {
            "teacher": "1",
            "unavailable": [
                "18:00",
                "18:30"
            ]
        },
        {
            "teacher": "2",
            "unavailable": [
                "17:00",
                "17:30"
            ]
        },
        {
            "teacher": "2",
            "unavailable": [
                "18:00",
                "18:30"
            ]
        },
        {
            "teacher": "3",
            "unavailable": [
                "17:00",
                "17:30"
            ]
        },
        {
            "teacher": "3",
            "unavailable": [
                "18:00",
                "18:30"
            ]
        }
    ]
} */
$students = get_all_students_in_year(13);
//for each student generate a test timetabling json
//do not put any classes with Zz, Zy, 13T or Sz in the name
//for each student, get all the classes
//for each class, get all the teachers
//get the teacher of the class

//use 5 and 7:30 as the start and end times
//use 5 as the duration

//generate json
//generate a json object with the following structure
//start, end, duration, teachers, children, appointments, unavailable
//start and end are the start and end times of the evening
//duration is the duration of each appointment
//teachers is an array of all the teachers
//children is an array of all the children
//appointments is an array of all the appointments
//unavailable is an array of all the unavailable times of teachers
/*
$arr = array(
    "start" => "17:00",
    "end" => "19:30",
    "duration" => 5,
    "teachers" => array(),
    "children" => array(),
    "appointments" => array(),
    "unavailable" => array()
);
$studentTeachers = array();
//get all the teachers of the classes
foreach ($students as $student) {
    $classes = get_all_classes_of_student($student->getId());
    $studentTeachers[$student->getId()] = array();
    foreach ($classes as $class) {
        if (!str_contains($class->getName(), "Zz") && !str_contains($class->getName(), "Zy") && !str_contains($class->getName(), "13T") && !str_contains($class->getName(), "SZ")) {
            $teachers = $class->getTeachers();
            //add the teacher to array in the studentTeacher array
            array_push($studentTeachers[$student->getId()], $teachers[0]);
            foreach ($teachers as $teacher) {
                if (!in_array($teacher, $arr["teachers"])) {
                    array_push($arr["teachers"], (string)$teacher);
                }
            }
        }
    }
    array_push($arr["children"], (string)$student->getId());
}
//generate all the appointments
foreach ($arr["children"] as $child) {
    $studentTeacher = $studentTeachers[$child];
    foreach ($studentTeacher as $teacher) {
        $wanted = 0;
        $available = array();
        //just add 17:00, 19:30
        array_push($available, "17:00");
        array_push($available, "19:30");
        $appointment = array(
            "child" => (string)$child,
            "teacher" => (string)$teacher,
            "wanted" => (string)$wanted,
            "available" => $available
        );
        array_push($arr["appointments"], $appointment);
    }
}
//generate the json
$json = json_encode($arr);
echo $json;
*/
