<?php
require_once '../utils.php';
/*$classteachstudent = fopen("classteachstudent.csv", "r");
$currentclassid = 0;
//layour is class id teacher id student id
while (($line = fgetcsv($classteachstudent)) !== false) {
    if($line[0]!=$currentclassid){
        $currentclassid = $line[0];
        $sql = "INSERT IGNORE INTO TeacherClass (Class, Teacher) VALUES ($line[0], $line[1])";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $stmt->close();
    }
    $sql = "INSERT IGNORE INTO StudentClass (Class, Student) VALUES ($line[0], $line[2])";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stmt->close();
}
*/
/*
$students = get_all_students();
foreach ($students as $student)
{
    //fix the student's YearGroup
    $classes = $student->get_classes();
    $yeargroup = 0;
    foreach ($classes as $class)
    {
        $classname = "";
        $sql = "SELECT Name FROM Class WHERE ID = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $class);
        $stmt->execute();
        $stmt->bind_result($classname);
        $stmt->fetch();
        $stmt->close();
        if (strpos($classname,  "07") !== false)
        {
            $yeargroup = 7;
        }
        else if (strpos($classname, "08") !== false)
        {
            $yeargroup = 8;
        }
        else if (strpos($classname, "09") !== false)
        {
            $yeargroup = 9;
        }
        else if (strpos($classname, "10") !== false)
        {
            $yeargroup = 10;
        }
        else if (strpos($classname, "11") !== false)
        {
            $yeargroup = 11;
        }
        else if (strpos($classname, "12") !== false)
        {
            $yeargroup = 12;
        }
        else if (strpos($classname, "13") !== false)
        {
            $yeargroup = 13;
        }
    }
    $studid = $student->get_id();
    $sql = "UPDATE Student SET YearGroup = ? WHERE ID = ?";#
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ii", $yeargroup, $studid);
    $stmt->execute();
    $stmt->close();


}
*/
