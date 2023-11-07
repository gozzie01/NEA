<?php
//is_logged_in() checks if the user is logged in
//is_admin() checks if the user is an admin
//is_teacher() checks if the user is a teacher
//is_parent() checks if the user is a parent
session_start();
require_once 'db.php';
require_once 'classdefs.php';
function is_logged_in()
{
    return isset($_SESSION['user']);
}
function verify_login()
{
    //check if the users token is valid
    $token = $_SESSION['token'];
    $user = $_SESSION['user'];
    $hashed_token = "";
    $sql = "SELECT Token FROM Users WHERE User_ID=? LIMIT 1";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $user);
    $stmt->execute();
    $stmt->bind_result($hashed_token);
    $stmt->fetch();
    $stmt->close();
    return password_verify($token, $hashed_token);
}
function is_admin()
{
    if (isset($_SESSION['admin'])) {
        return $_SESSION['admin'];
    }
}
function is_teacher()
{
    if (isset($_SESSION['teacher'])) {
        return $_SESSION['teacher'];
    }
}
function is_parent()
{
    if (isset($_SESSION['parent'])) {
        return $_SESSION['parent'];
    }
}
function get_parent_students($parentid)
{
    $studentid = "";
    $sql = "SELECT Student FROM ParentStudent WHERE Parent=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $parentid);
    $stmt->execute();
    $stmt->bind_result($studentid);
    $students = array();
    while ($stmt->fetch()) {
        $students[] = $studentid;
    }
    $stmt->close();
    return $students;
}

function get_student_name($studentid)
{
    $name = "";
    $sql = "SELECT Name FROM Student WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $studentid);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();
    return $name;
}

function get_all_teachers()
{
    $teacherid = "";
    $name = "";
    $pastoral = "";
    $account = "";
    $sql = "SELECT ID,Name,Pastoral,UserID FROM Teacher ORDER BY ID";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($teacherid, $name, $pastoral, $account);
    $teachers = array();
    while ($stmt->fetch()) {
        $teachers[] = new Teacher($teacherid);
        //set the pastoral of the teacher
        $teachers[count($teachers) - 1]->set_pastoral($pastoral);
        //set the name of the teacher
        $teachers[count($teachers) - 1]->set_name($name);
        //set the account of the teacher
        $teachers[count($teachers) - 1]->set_account($account);
    }
    $stmt->close();
    //update the teachers in the same way as the students
    $Class = "";
    $Teacher = "0";
    $counter = -1;
    $OldTeacher = "-1";
    $sql = "SELECT Class,Teacher FROM TeacherClass ORDER BY Teacher";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($Class, $Teacher);
    while ($stmt->fetch()) {
        if ($Teacher != $OldTeacher) {
            $counter++;
            $OldTeacher = $Teacher;
        }
        $teachers[$counter]->add_class($Class);
    }
    $stmt->close();
    $Student = "";
    $sql = "SELECT sc.Student, tc.Teacher
    FROM StudentClass sc
    JOIN TeacherClass tc ON sc.Class = tc.Class
    ORDER BY tc.Teacher";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($Student, $Teacher);
    while ($stmt->fetch()) {
        $counter = 0;
        while ($Teacher != $teachers[$counter]->get_id()) {
            $counter++;
        }
        $teachers[$counter]->add_student($Student);
    }
    $stmt->close();
    $parent = "";
    "SELECT ps.Parent, tc.Teacher
    FROM ParentStudent ps
    JOIN TeacherClass tc ON ps.Student = tc.Class
    ORDER BY tc.Teacher";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($parent, $Teacher);
    while ($stmt->fetch()) {
        $counter = 0;
        while ($Teacher != $teachers[$counter]->get_id()) {
            $counter++;
        }
        $teachers[$counter]->add_parent($parent);
    }
    $stmt->close();
    return $teachers;
}

function get_all_classes()
{
    $classid = "";
    $sql = "SELECT ID FROM Class";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($classid);
    $classes = array();
    while ($stmt->fetch()) {
        $classes[] = new Class_($classid);
    }
    $stmt->close();
    //update the classes
    foreach ($classes as $class) {
        $class->update();
    }

    return $classes;
}

function get_all_students()
{
    $studentid = "";
    $name = "";
    $year = "";
    $sql = "SELECT ID,YearGroup,Name FROM Student ORDER BY ID";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($studentid, $year, $name);
    $students = array();
    while ($stmt->fetch()) {
        $students[] = new Student($studentid);
        //set the yeargroup of the student
        $students[count($students) - 1]->set_year($year);
        //set the name of the student
        $students[count($students) - 1]->set_name($name);
    }
    $stmt->close();
    //mass add the classes
    $Class = "";
    $Student = "0";
    $counter = -1;
    $OldStudent = "-1";
    $sql = "SELECT Class,Student FROM StudentClass ORDER BY Student";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($Class, $Student);
    while ($stmt->fetch()) {
        if ($Student != $OldStudent) {
            $counter++;
            $OldStudent = $Student;
        }
        $students[$counter]->add_class($Class);
    }
    $stmt->close();
    //mass add the parents
    //not all students have parents, so we need to check if the student has parents
    $Parent = "";
    $Student = "0";
    $counter = 0;
    $OldStudent = "-1";
    $sql = "SELECT Parent,Student FROM ParentStudent ORDER BY Student";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($Parent, $Student);
    while ($stmt->fetch()) {
        while ($Student != $OldStudent) {
            $counter++;
            //set the parent of the student to an empty array
            $OldStudent = $students[$counter]->get_id();
        }
        $students[$counter]->add_parent($Parent);
    }
    $Teacher = "";
    $stmt->close();
    $sql = "SELECT sc.Student, tc.Teacher
        FROM StudentClass sc
        JOIN TeacherClass tc ON sc.Class = tc.Class
        ORDER BY sc.Student";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($Student, $Teacher);
    while ($stmt->fetch()) {
        $counter = 0;
        while ($Student != $students[$counter]->get_id()) {
            $counter++;
        }
        $students[$counter]->add_teacher($Teacher);
    }
    return $students;
}

function get_all_parents()
{
    $parentid = "";
    $sql = "SELECT ID FROM Parent";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($parentid);
    $parents = array();
    while ($stmt->fetch()) {
        $parents[] = new Parent_($parentid);
    }
    $stmt->close();
    //update the parents
    foreach ($parents as $parent) {
        $parent->update();
    }

    return $parents;
}

function student_exists($id)
{
    $sql = "SELECT ID FROM Student WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $numrows = $stmt->num_rows;
    $stmt->close();
    return $numrows > 0;
}
function class__exists($id)
{
    $sql = "SELECT ID FROM Class WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $numrows = $stmt->num_rows;
    $stmt->close();
    return $numrows > 0;
}
function parent_exists($id)
{
    $sql = "SELECT ID FROM Parent WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $numrows = $stmt->num_rows;
    $stmt->close();
    return $numrows > 0;
}
function update_student($id, $name, $year, $parents, $classes)
{
    if (!student_exists($id)) {
        return false;
    }
    //check if the values passed are valid
    if (!is_string($name) || !is_int($year)) {
        return false;
    }
    //check if the parents and classes exist
    if (!is_null($parents)) {
        foreach ($parents as $parent) {
            if (!parent_exists($parent)) {
                return false;
            }
        }
    }
    if (!is_null($classes)) {
        foreach ($classes as $class) {
            if (!class__exists($class)) {
                return false;
            }
        }
    }
    //update the student
    $sql = "UPDATE Student SET Name=?, YearGroup=? WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("sii", $name, $year, $id);
    $stmt->execute();
    $stmt->close();
    //update the parents
    $sql = "DELETE FROM ParentStudent WHERE Student=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    if (!is_null($parents)) {
        foreach ($parents as $parent) {
            $sql = "INSERT INTO ParentStudent (Parent, Student) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $parent, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    //update the classes
    $sql = "DELETE FROM StudentClass WHERE Student=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    if (!is_null($classes)) {
        foreach ($classes as $class) {
            $sql = "INSERT INTO StudentClass (Student, Class) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $id, $class);
            $stmt->execute();
            $stmt->close();
        }
    }
    return true;
}
function create_student($id, $name, $year, $parents, $classes)
{
    if (student_exists($id)) {
        echo "student exists";
        return false;
    }
    //check if the values passed are valid
    if (!is_string($name) || !is_int($year)) {
        echo "values invalid";
        return false;
    }
    //check if the parents and classes exist
    if (!is_null($parents)) {
        foreach ($parents as $parent) {
            if (!parent_exists($parent)) {
                echo "parent invalid";
                return false;
            }
        }
    }
    if (!is_null($classes)) {
        foreach ($classes as $class) {
            if (!class__exists($class)) {
                echo "class invalid";
                return false;
            }
        }
    }
    //create the student
    $sql = "INSERT INTO Student (ID, Name, YearGroup) VALUES (?, ?, ?)";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("isi", $id, $name, $year);
    $stmt->execute();
    $stmt->close();
    //add the parents
    if (!is_null($parents)) {
        foreach ($parents as $parent) {
            $sql = "INSERT INTO ParentStudent (Parent, Student) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $parent, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    if (!is_null($classes)) {
        foreach ($classes as $class) {
            $sql = "INSERT INTO StudentClass (Student, Class) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $id, $class);
            $stmt->execute();
            $stmt->close();
        }
    }
    return true;
}

function teacher_exists($id)
{
    $sql = "SELECT ID FROM Teacher WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $numrows = $stmt->num_rows;
    $stmt->close();
    return $numrows > 0;
}

function update_teacher($id, $name, $pastoral, $classes, $account)
{
    if (!teacher_exists($id)) {
        return false;
    }
    //check if the values passed are valid
    if (!is_string($name) || !is_int($pastoral)) {
        return false;
    }
    //allow account to be unset or null
    if (!is_null($account)) {
        if (!account_exists($account)) {
            return false;
        }
    }
    //check if the classes exist
    if (!is_null($classes)) {
        foreach ($classes as $class) {
            if (!class__exists($class)) {
                return false;
            }
        }
    }
    //update the teacher
    $sql = "UPDATE Teacher SET Name=?, Pastoral=?, UserID=? WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("siii", $name, $pastoral, $account, $id);
    $stmt->execute();
    $stmt->close();

    //update the classes
    $sql = "DELETE FROM TeacherClass WHERE Teacher=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    if (!is_null($classes)) {
        foreach ($classes as $class) {
            $sql = "INSERT INTO TeacherClass (Teacher, Class) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $id, $class);
            $stmt->execute();
            $stmt->close();
        }
    }
    return true;
}

function create_teacher($id, $name, $pastoral, $classes, $account)
{
    if (teacher_exists($id)) {
        return false;
    }
    //check if the values passed are valid
    if (!is_string($name) || !is_int($pastoral)) {
        return false;
    }
    //allow account to be unset or null
    if (!is_null($account)) {
        if (!account_exists($account)) {
            return false;
        }
    }
    //check if the classes exist
    if (!is_null($classes)) {
        foreach ($classes as $class) {
            if (!class__exists($class)) {
                return false;
            }
        }
    }
    //create the teacher
    $sql = "INSERT INTO Teacher (ID, Name, Pastoral, UserID) VALUES (?, ?, ?, ?)";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("isii", $id, $name, $pastoral, $account);
    $stmt->execute();
    $stmt->close();
    //add the classes
    if (!is_null($classes)) {
        foreach ($classes as $class) {
            $sql = "INSERT INTO TeacherClass (Teacher, Class) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $id, $class);
            $stmt->execute();
            $stmt->close();
        }
    }
    return true;
}

function delete_teacher($id)
{
    if (!teacher_exists($id)) {
        return false;
    }
    //delete the teacher
    $sql = "DELETE FROM Teacher WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    //delete the classes
    $sql = "DELETE FROM TeacherClass WHERE Teacher=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    return true;
}

function delete_student($id)
{
    if (!student_exists($id)) {
        return false;
    }
    //delete the student
    $sql = "DELETE FROM Student WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    //delete the parents
    $sql = "DELETE FROM ParentStudent WHERE Student=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    //delete the classes
    $sql = "DELETE FROM StudentClass WHERE Student=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    return true;
}

function delete_class($id)
{
    if (!class__exists($id)) {
        return false;
    }
    //delete the class
    $sql = "DELETE FROM Class WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    //delete the students
    $sql = "DELETE FROM StudentClass WHERE Class=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    //delete the teachers
    $sql = "DELETE FROM TeacherClass WHERE Class=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    return true;
}
function update_class($id, $name, $students, $teachers)
{
    if (!class__exists($id)) {
        return false;
    }
    //check if the values passed are valid
    if (!is_string($name)) {
        return false;
    }
    //check if the students and teachers exist
    if (!is_null($students)) {
        foreach ($students as $student) {
            if (!student_exists($student)) {
                return false;
            }
        }
    }

    if (!is_null($teachers)) {
        foreach ($teachers as $teacher) {
            if (!teacher_exists($teacher)) {
                return false;
            }
        }
    }
    //update the class
    $sql = "UPDATE Class SET Name=? WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    $stmt->close();
    //update the parents
    $sql = "DELETE FROM StudentClass WHERE Class=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    if (!is_null($students)) {
        foreach ($students as $student) {
            $sql = "INSERT INTO StudentClass (Student, Class) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $student, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    //update the teachers
    $sql = "DELETE FROM TeacherClass WHERE Class=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    if (!is_null($teachers)) {
        foreach ($teachers as $teacher) {
            $sql = "INSERT INTO TeacherClass (Teacher, Class) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $teacher, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    return true;
}
function create_class($id, $name, $students, $teachers)
{
    if (class__exists($id)) {
        return false;
    }
    //check if the values passed are valid
    if (!is_string($name)) {
        return false;
    }
    //check if the students and teachers exist
    if (!is_null($students)) {
        foreach ($students as $student) {
            if (!student_exists($student)) {
                return false;
            }
        }
    }

    if (!is_null($teachers)) {
        foreach ($teachers as $teacher) {
            if (!teacher_exists($teacher)) {
                return false;
            }
        }
    }
    //create the class
    $sql = "INSERT INTO Class (ID, Name) VALUES (?, ?)";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("is", $id, $name);
    $stmt->execute();
    $stmt->close();
    //add the parents
    if (!is_null($students)) {
        foreach ($students as $student) {
            $sql = "INSERT INTO StudentClass (Student, Class) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $student, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    //add the teachers
    if (!is_null($teachers)) {
        foreach ($teachers as $teacher) {
            $sql = "INSERT INTO TeacherClass (Teacher, Class) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $teacher, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    return true;
}

function get_all_accounts()
{
    $accountid = "";
    $sql = "SELECT ID FROM User";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($accountid);
    $accounts = array();
    while ($stmt->fetch()) {
        $accounts[] = new Account($accountid);
    }
    $stmt->close();
    //update the accounts
    foreach ($accounts as $account) {
        $account->update();
    }
    return $accounts;
}

function account_exists($id)
{
    $sql = "SELECT ID FROM User WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $numrows = $stmt->num_rows;
    $stmt->close();
    return $numrows > 0;
}

function create_parent($id, $name, $account, $students)
{
    if (parent_exists($id)) {
        return false;
    }
    //check if the values passed are valid
    if (!is_string($name)) {
        return false;
    }
    //check if the students exist
    if (!is_null($students)) {
        foreach ($students as $student) {
            if (!student_exists($student)) {
                return false;
            }
        }
    }
    //check if the account exists
    if (!is_null($account)) {
        if (!account_exists($account)) {
            return false;
        }
    }
    //create the parent
    $sql = "INSERT INTO Parent (ID, Name, UserID) VALUES (?, ?, ?)";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("isi", $id, $name, $account);
    $stmt->execute();
    $stmt->close();
    //add the students
    if (!is_null($students)) {
        foreach ($students as $student) {
            $sql = "INSERT INTO ParentStudent (Parent, Student) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $id, $student);
            $stmt->execute();
            $stmt->close();
        }
    }
    return true;
}
function update_parent($id, $name, $account, $students)
{
    if (!parent_exists($id)) {
        return false;
    }
    //check if the values passed are valid
    if (!is_string($name)) {
        return false;
    }
    //check if the students exist
    if (!is_null($students)) {
        foreach ($students as $student) {
            if (!student_exists($student)) {
                return false;
            }
        }
    }
    //check if the account exists
    if (!is_null($account)) {
        if (!account_exists($account)) {
            return false;
        }
    }
    //update the parent
    $sql = "UPDATE Parent SET Name=?, UserID=? WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("sii", $name, $account, $id);
    $stmt->execute();
    $stmt->close();
    //update the students
    $sql = "DELETE FROM ParentStudent WHERE Parent=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    if (!is_null($students)) {
        foreach ($students as $student) {
            $sql = "INSERT INTO ParentStudent (Parent, Student) VALUES (?, ?)";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("ii", $id, $student);
            $stmt->execute();
            $stmt->close();
        }
    }
    return true;
}
function delete_parent($id)
{
    if (!parent_exists($id)) {
        return false;
    }
    //delete the parent
    $sql = "DELETE FROM Parent WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    //delete the students
    $sql = "DELETE FROM ParentStudent WHERE Parent=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    return true;
}

function update_account($id, $name, $email, $phone, $parentid, $teacherid)
{
    if (!account_exists($id)) {
        return false;
    }
    //check if the values passed are valid
    if (!is_string($name) || !is_string($email) || !is_string($phone)) {
        return false;
    }
    //check if the parent and teacher exist
    if (!is_null($parentid)) {
        if (!parent_exists($parentid)) {
            return false;
        }
    }
    if (!is_null($teacherid)) {
        if (!teacher_exists($teacherid)) {
            return false;
        }
    }
    //update the account
    $sql = "UPDATE User SET Name=?, Email=?, Phone=? WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $phone, $id);
    $stmt->execute();
    $stmt->close();
    //update the parent
    $sql = "UPDATE Parent SET UserID=? WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("ii", $id, $parentid);
    $stmt->execute();
    $stmt->close();
    //update the teacher
    $sql = "UPDATE Teacher SET UserID=? WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("ii", $id, $teacherid);
    $stmt->execute();
    $stmt->close();
    return true;
}

function update_accountDetails($id, $name, $email, $phone)
{
    if (!account_exists($id)) {
        return false;
    }
    //check if the values passed are valid
    if (!is_string($name) || !is_string($email) || !is_string($phone)) {
        return false;
    }
    //update the account
    $sql = "UPDATE User SET Name=?, Email=?, Phone=? WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $phone, $id);
    $stmt->execute();
    $stmt->close();
}
function delete_account($id)
{
    if (!account_exists($id)) {
        return false;
    }
    //delete the account
    $sql = "DELETE FROM User WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    return true;
}