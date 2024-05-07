<?php
//db details

//read details from db.details
global $rootPath;
$rootPath = "E:/projects/php/php/src";

$file = fopen($rootPath . "\db.details", "r");
$dbHost = trim(fgets($file));
$dbUsername = trim(fgets($file));
$dbpassword = trim(fgets($file));
$dbName = trim(fgets($file));
fclose($file);



//Connect and select the database
/*db layout:
Class (ID, Name)
Teacher (ID, UserID, Name, Pastoral)
User (ID, Password, Admin, Token, Phone, Email)
Parent (ID, UserID, Name)
Student (ID, Name, YearGroup)
Event (ID, Name, StartTime, EndTime, SlotDuration, YearGroup)
Timeslot (ID, EventID, Parent, Student, Teacher, Class, StartTime, Duration)
PrefferedTime (ID, EventID, Parent, Student, Teacher, Class, StartTime, EndTime)
StudentClass (Student, Class)
TeacherClass (Teacher, Class)
ParentStudent (Parent, Student)
*/
global $db;
$db = new mysqli($dbHost, $dbUsername, $dbpassword, $dbName);
if ($db->connect_error) {
    die("Unable to connect database: " . $db->connect_error);
}
