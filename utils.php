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
    //if user session is set, return true
    if (isset($_SESSION['user'])) {
        verify_login();
        return true;
    }
    return false;
}
function destroy_token()
{
    if (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
        $sql = "UPDATE User SET Token=NULL WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $user);
        $stmt->execute();
        $stmt->close();
    }
}
function verify_login()
{
    //check if the users token is valid
    $token = $_SESSION['token'];
    $user = $_SESSION['user'];
    $hashed_token = "";
    $sql = "SELECT Token FROM User WHERE ID=? LIMIT 1";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $user);
    $stmt->execute();
    $stmt->bind_result($hashed_token);
    $stmt->fetch();
    $stmt->close();
    if ($hashed_token == NULL) {
        //the token is invalid, so log the user out
        //unset all the session variables
        $_SESSION = array();
        //delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        //destroy the session
        session_destroy();
        //redirect the user to the login page
        header("Location: ../login.php");
        exit();
    } elseif (!password_verify($token, $hashed_token)) {
        //the token is invalid, so log the user out
        //unset all the session variables
        $_SESSION = array();
        //delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        //destroy the session
        session_destroy();
        //redirect the user to the login page
        header("Location: ../login.php");
        exit();
    }
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
    $name = "";
    $sql = "SELECT ID, Name FROM Class ORDER BY ID";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($classid, $name);
    $classes = array();
    while ($stmt->fetch()) {
        $classes[] = new Class_($classid);
        //set the name of the class
        $classes[count($classes) - 1]->set_name($name);
    }
    $stmt->close();
    //get the students
    $Student = "";
    $Class = "0";
    $counter = 0;
    $OldClass = "-1";
    $sql = "SELECT Student,Class FROM StudentClass ORDER BY Class";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($Student, $Class);
    while ($stmt->fetch()) {
        while ($Class != $classes[$counter]->get_id()) {
            $counter++;
        }
        $classes[$counter]->add_student($Student);
    }
    $stmt->close();
    //get the teachers
    $Teacher = "";
    $Class = "0";
    $counter = 0;
    $OldClass = "-1";
    $sql = "SELECT Teacher,Class FROM TeacherClass ORDER BY Class";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($Teacher, $Class);
    while ($stmt->fetch()) {
        while ($Class != $classes[$counter]->get_id()) {
            $counter++;
        }
        $classes[$counter]->add_teacher($Teacher);
    }
    $stmt->close();
    $counter = 0;
    $parent = "";
    $sql = "SELECT ps.Parent, sc.Class
    FROM ParentStudent ps
    JOIN StudentClass sc ON ps.Student = sc.Student
    ORDER BY sc.Class";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($parent, $Class);
    while ($stmt->fetch()) {
        //if the counter is too large try the next value
        if ($counter >= count($classes)) {
            $counter = 0;
            continue;
        }
        while ($Class != $classes[$counter]->get_id()) {
            $counter++;
        }
        //if the counter is too large try the next value
        $classes[$counter]->add_parent($parent);
    }
    $stmt->close();

    return $classes;
}

function get_all_classes_of_teacher($teacherid)
{
    $classid = "";
    $name = "";
    $sql = "SELECT tc.Class, c.Name
    FROM TeacherClass tc
    JOIN Class c ON tc.Class = c.ID
    WHERE tc.Teacher = ?
    ORDER BY tc.Class";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $teacherid);
    $stmt->execute();
    $stmt->bind_result($classid, $name);
    $classes = array();
    while ($stmt->fetch()) {
        $classes[] = new Class_($classid);
        //set the name of the class
        $classes[count($classes) - 1]->set_name($name);
    }
    $stmt->close();
    //get the students
    $Student = "";
    $Class = "0";
    $counter = 0;
    $OldClass = "-1";
    $sql = "SELECT Student,Class FROM StudentClass ORDER BY Class";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($Student, $Class);
    while ($stmt->fetch()) {
        while ($Class != $classes[$counter]->get_id()) {
            $counter++;
            //if counter is too large then break out of the 2 loops
            if ($counter >= count($classes)) {
                break;
            }
        }
        if ($counter >= count($classes)) {
            $counter = 0;
            continue;
        }
        //if the counter is too large try the next value
        $classes[$counter]->add_student($Student);
    }
    $stmt->close();
    //get the teachers
    $Teacher = "";
    $Class = "0";
    $counter = 0;
    $OldClass = "-1";
    $sql = "SELECT Teacher,Class FROM TeacherClass ORDER BY Class";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($Teacher, $Class);
    while ($stmt->fetch()) {
        while ($Class != $classes[$counter]->get_id()) {
            $counter++;
            //if counter is too large then break out of the 2 loops
            if ($counter >= count($classes)) {
                break;
            }
        }
        if ($counter >= count($classes)) {
            $counter = 0;
            continue;
        }
        //if the counter is too large try the next value
        $classes[$counter]->add_teacher($Teacher);
    }
    $stmt->close();
    $counter = 0;
    $parent = "";
    $sql = "SELECT ps.Parent, sc.Class
    FROM ParentStudent ps
    JOIN StudentClass sc ON ps.Student = sc.Student
    ORDER BY sc.Class";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($parent, $Class);
    while ($stmt->fetch()) {
        if ($counter >= count($classes)) {
            $counter = 0;
            continue;
        }
        while ($Class != $classes[$counter]->get_id()) {
            $counter++;
            //if counter is too large then break out of the 2 loops
            if ($counter >= count($classes)) {
                break;
            }
        }
        if ($counter >= count($classes)) {
            $counter = 0;
            continue;
        }
        //if the counter is too large try the next value
        $classes[$counter]->add_parent($parent);
    }
    $stmt->close();
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
    $counter = -1;
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
    $counter = 0;
    $stmt->close();
    $sql = "SELECT sc.Student, tc.Teacher
        FROM StudentClass sc
        JOIN TeacherClass tc ON sc.Class = tc.Class
        ORDER BY sc.Student";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($Student, $Teacher);
    while ($stmt->fetch()) {
        while ($Student != $students[$counter]->get_id()) {
            $counter++;
        }
        $students[$counter]->add_teacher($Teacher);
    }
    return $students;
}

function get_all_teachers_of_classes($classes)
{
    //format classes like (1,2,3)
    $classes = "(" . implode(",", $classes) . ")";
    $teacherid = "";
    $name = "";
    $pastoral = "";
    $account = "";
    $sql = "SELECT t.ID,t.Name,t.Pastoral,t.UserID
    FROM Teacher t
    JOIN TeacherClass tc ON t.ID = tc.Teacher
    WHERE tc.Class IN $classes
    ORDER BY t.ID";
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

function get_all_parents()
{
    $parentid = "";
    $name = "";
    $userid = "";
    $sql = "SELECT ID, Name, UserID FROM Parent ORDER BY ID";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($parentid, $name, $userid);
    $parents = array();
    while ($stmt->fetch()) {
        $parents[] = new Parent_($parentid);
        //set the name of the parent
        $parents[count($parents) - 1]->set_name($name);
        //set the account of the parent
        $parents[count($parents) - 1]->set_account($userid);
    }
    $stmt->close();
    //update the parents
    $parentid = "";
    $studentid = "";
    $counter = 0;
    $sql = "SELECT Student, Parent FROM ParentStudent ORDER BY Parent";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($studentid, $parentid);
    while ($stmt->fetch()) {
        while ($parentid != $parents[$counter]->get_id() && $counter < count($parents)) {
            $counter++;
        }
        $parents[$counter]->add_student($studentid);
    }
    $stmt->close();
    $parentid = "";
    $classid = "";
    $counter = 0;
    $sql = "SELECT sc.Class, ps.Parent
    FROM StudentClass sc
    JOIN ParentStudent ps ON sc.Student = ps.Student
    ORDER BY ps.Parent";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($classid, $parentid);
    while ($stmt->fetch()) {
        while ($parentid != $parents[$counter]->get_id() && $counter < count($parents)) {
            $counter++;
        }
        $parents[$counter]->add_class($classid);
    }
    $stmt->close();
    $teacherid = "";
    $parentid = "";
    $counter = 0;
    $sql = "SELECT tc.Teacher, ps.Parent
    FROM TeacherClass tc
    JOIN StudentClass sc ON tc.Class = sc.Class
    JOIN ParentStudent ps ON  ps.Student = sc.Student
    ORDER BY ps.Parent";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($teacherid, $parentid);
    while ($stmt->fetch()) {
        while ($parentid != $parents[$counter]->get_id() && $counter < count($parents)) {
            $counter++;
        }
        $parents[$counter]->add_teacher($teacherid);
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
    $name = "";
    $password = "";
    $email = "";
    $phone = "";
    $sql = "SELECT ID, Name, Password, Email, Phone FROM User ORDER BY ID";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($accountid, $name, $password, $email, $phone);
    $accounts = array();
    while ($stmt->fetch()) {
        $accounts[] = new Account($accountid);
        //set the name of the account
        $accounts[count($accounts) - 1]->set_name($name);
        //set the password of the account
        $accounts[count($accounts) - 1]->set_password($password);
        //set the email of the account
        $accounts[count($accounts) - 1]->set_email($email);
        //set the phone of the account
        $accounts[count($accounts) - 1]->set_phone($phone);
    }
    $stmt->close();
    //update the accounts
    $parentid = "";
    $id = "";
    $counter = 0;
    $sql = "SELECT ID, UserID FROM Parent WHERE UserID IS NOT NULL ORDER BY UserID";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($parentid, $id);
    while ($stmt->fetch()) {
        while ($id != $accounts[$counter]->get_id()) {
            $counter++;
        }
        $accounts[$counter]->set_parentid($parentid);
    }
    $stmt->close();
    $teacherid = "";
    $counter = 0;
    $sql = "SELECT ID, UserID FROM Teacher WHERE UserID IS NOT NULL ORDER BY UserID";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($teacherid, $id);
    while ($stmt->fetch()) {
        while ($id != $accounts[$counter]->get_id()) {
            $counter++;
        }
        $accounts[$counter]->set_teacherid($teacherid);
    }
    $stmt->close();
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

function create_account($id, $name, $email, $phone, $parentid, $teacherid)
{
    if (account_exists($id)) {
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
    //create the account
    $sql = "INSERT INTO User (ID, Name, Email, Phone) VALUES (?, ?, ?, ?)";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("isss", $id, $name, $email, $phone);
    $stmt->execute();
    $stmt->close();
    //create the parent
    $sql = "INSERT INTO Parent (UserID) VALUES (?)";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close(); //1000
    //create the teacher
    $sql = "INSERT INTO Teacher (UserID) VALUES (?)";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
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

function is_pastoral()
{
    return isset($_SESSION['pastoral']);
}
