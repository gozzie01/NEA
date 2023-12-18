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
        //check the first two letters of the class name
        $yeargroup = intval(substr($classname, 0, 2));
        if ($yeargroup != 0)
        {
            break;
        }
    }
    $studid = $student->get_id();
    $sql = "UPDATE Student SET YearGroup = ? WHERE ID = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ii", $yeargroup, $studid);
    $stmt->execute();
    $stmt->close();


}
/*
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['PAR'])){
$names = array();
$file = fopen("names.txt", "r");
while (($line = fgets($file)) !== false) {
    $names[] = $line;
}
fclose($file);

//generate parents for all students, 1 in 100 chance of being a sibling of another student, if this happends get the parents of the other student
$counter = 0;
$students = get_all_students();
foreach ($students as $student) {
    //get sibiling
    $issibling = rand(1, 100);
    if ($issibling == 1 && $counter > 0) {
        $sibling = $students[rand(0, $counter - 1)];
        $studentid = $student->get_id();
        $siblingid = $sibling->get_id();
        $parentids = array();
        $sql = "SELECT Parent FROM ParentStudent WHERE Student = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $siblingid);
        $stmt->execute();
        $stmt->bind_result($parentid);
        //foreach parent of the sibling add them to the student
        while ($stmt->fetch()) {
            $parentids[] = $parentid;
        }
        $stmt->close();
        foreach ($parentids as $parentid) {
            $sql = "INSERT IGNORE INTO ParentStudent (Parent, Student) VALUES (?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ii", $parentid, $studentid);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        $numofparents = rand(1, 2);
        //for until the number of parents
        for ($i = 0; $i < $numofparents; $i++) {
            $babynames = fopen("names.txt", "r");
            $studentid = $student->get_id();
            //get random name from baby-names.csv
            $name = "";
            //get a random name
            $name = $names[rand(0, 999)];
            //if name is not null
            $sql = 'INSERT IGNORE INTO Parent (Name) VALUES (?)';
            $stmt = $db->prepare($sql);
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->close();
            $parentid = $db->insert_id;
            $sql = "INSERT IGNORE INTO ParentStudent (Parent, Student) VALUES (?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ii", $parentid, $studentid);
            $stmt->execute();
            $stmt->close();
            fclose($babynames);
        }
    }
    $counter++;
}
//parents have been generated but names are all blank
//for each parent get a random name from names.txt which has a list of names on each line and no other data
$parents = get_all_parents();
//read all the names from names.txt

foreach ($parents as $parent) {
    $parentid = $parent->get_id();
    //get random name from baby-names.csv
    $name = "";
    //get a random name
    $name = $names[rand(0, 999)];
    //if name is not null
    $sql = 'UPDATE Parent SET Name = ? WHERE ID = ?';
    $stmt = $db->prepare($sql);
    $stmt->bind_param("si", $name, $parentid);
    $stmt->execute();
    $stmt->close();
}
}
?>
<form method="post">
    <input type="submit" name="PAR" value="Generate Parents">
</form>
*/
