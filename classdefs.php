<?php
class Student
{
    private $id;
    private $name;
    private $teachers;
    private $year;
    private $classes;
    private $parents;

    public function __construct($id)
    {
        $this->id = $id;
        //set teachers, year, classes, parents to empty arrays 
        $this->teachers = array();
        $this->classes = array();
        $this->parents = array();
    }
    public function update()
    {
        $id = $this->id;
        $classid = "";
        $teacherid = "";
        $parentid = "";
        //sql for name
        $sql = "SELECT Name FROM Student WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->name);
        $stmt->fetch();
        $stmt->close();

        //sql for classes
        $sql = "SELECT Class FROM StudentClass WHERE Student=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($classid);
        $this->classes = array();
        while ($stmt->fetch()) {
            $this->classes[] = $classid;
        }
        $stmt->close();
        //sql for teachers, where the teacher is teaching a class the student is in
        $sql = "SELECT Teacher FROM TeacherClass WHERE Class IN (SELECT Class FROM StudentClass WHERE Student=?)";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($teacherid);
        $this->teachers = array();
        while ($stmt->fetch()) {
            $this->teachers[] = $teacherid;
        }
        $stmt->close();
        //sql for parents
        $sql = "SELECT Parent FROM ParentStudent WHERE Student=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($parentid);
        $this->parents = array();
        while ($stmt->fetch()) {
            $this->parents[] = $parentid;
        }
        $stmt->close();
        //sql for year
        $sql = "SELECT YearGroup FROM Student WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->year);
        $stmt->fetch();
        $stmt->close();
    }
    public function get_name()
    {
        return $this->name;
    }
    public function get_classes()
    {
        return $this->classes;
    }
    public function get_teachers()
    {
        return $this->teachers;
    }
    public function get_parents()
    {
        return $this->parents;
    }
    public function get_id():int
    {
        return $this->id;
    }
    public function get_year()
    {
        return $this->year;
    }
    public function set_name($name)
    {
        $this->name = $name;
    }
    public function set_classes($classes)
    {
        $this->classes = $classes;
    }
    public function add_class($class)
    {
        $this->classes[] = $class;
    }
    public function add_parent($parent)
    {
        $this->parents[] = $parent;
    }
    public function add_teacher($teacher)
    {
        $this->teachers[] = $teacher;
    }
    public function set_teachers($teachers)
    {
        $this->teachers = $teachers;
    }
    public function set_parents($parents)
    {
        $this->parents = $parents;
    }
    public function set_year($year)
    {
        $this->year = $year;
    }
}
class Teacher
{
    private $id;
    private $name;
    private $account;
    private $pastoral;
    private $classes;
    private $students;
    private $parents;

    public function __construct($id)
    {
        $this->id = $id;
        //set classes, students, parents to empty arrays
        $this->classes = array();
        $this->students = array();
        $this->parents = array();
    }
    public function update()
    {
        $id = $this->id;
        $classid = "";
        $studentid = "";
        $parentid = "";

        //sql for name
        $sql = "SELECT Name, Pastoral FROM Teacher WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->name, $this->pastoral);
        $stmt->fetch();
        $stmt->close();

        //sql for classes
        $sql = "SELECT Class FROM TeacherClass WHERE Teacher=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($classid);
        $this->classes = array();
        while ($stmt->fetch()) {
            $this->classes[] = $classid;
        }
        $stmt->close();
        //sql for students, where the student is in a class the teacher is teaching
        $sql = "SELECT Student FROM StudentClass WHERE Class IN (SELECT Class FROM TeacherClass WHERE Teacher=?)";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($studentid);
        $this->students = array();
        while ($stmt->fetch()) {
            $this->students[] = $studentid;
        }
        $stmt->close();
        //sql for parents
        $sql = "SELECT Parent FROM ParentStudent WHERE Student IN (SELECT Student FROM StudentClass WHERE Class IN (SELECT Class FROM TeacherClass WHERE Teacher=?))";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($parentid);
        $this->parents = array();
        while ($stmt->fetch()) {
            $this->parents[] = $parentid;
        }
        $stmt->close();
        //sql for accountid
        $sql = "SELECT UserID FROM Teacher WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->account);
        $stmt->fetch();
        $stmt->close();
    }
    public function get_name()
    {
        return $this->name;
    }
    public function get_pastoral()
    {
        return $this->pastoral;
    }
    public function get_account()
    {
        return $this->account;
    }
    public function get_classes()
    {
        return $this->classes;
    }
    public function get_students()
    {
        return $this->students;
    }
    public function get_id()
    {
        return $this->id;
    }
    public function get_parents()
    {
        return $this->parents;
    }
    public function set_name($name)
    {
        $this->name = $name;
    }
    public function set_pastoral($pastoral)
    {
        $this->pastoral = $pastoral;
    }
    public function set_classes($classes)
    {
        $this->classes = $classes;
    }
    public function set_students($students)
    {
        $this->students = $students;
    }
    public function set_account($account)
    {
        $this->account = $account;
    }
    public function add_class($class)
    {
        $this->classes[] = $class;
    }
    public function add_student($student)
    {
        $this->students[] = $student;
    }
    public function add_parent($parent)
    {
        $this->parents[] = $parent;
    }
    public function set_parents($parents)
    {
        $this->parents = $parents;
    }
}
class Parent_
{
    private $id;
    private $name;
    private $students;
    private $classes;
    private $account;
    private $teachers;

    public function __construct($id)
    {
        $this->id = $id;
    }
    public function update()
    {
        $id = $this->id;
        $studentid = "";
        $classid = "";
        $teacherid = "";
        //sql for name
        $sql = "SELECT Name FROM Parent WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->name);
        $stmt->fetch();
        $stmt->close();
        //sql for accountid
        $sql = "SELECT UserID FROM Parent WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->account);
        $stmt->fetch();
        $stmt->close();

        //sql for students
        $sql = "SELECT Student FROM ParentStudent WHERE Parent=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($studentid);
        $this->students = array();
        while ($stmt->fetch()) {
            $this->students[] = $studentid;
        }
        $stmt->close();
        //sql for classes
        $sql = "SELECT Class FROM StudentClass WHERE Student IN (SELECT Student FROM ParentStudent WHERE Parent=?)";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($classid);
        $this->classes = array();
        while ($stmt->fetch()) {
            $this->classes[] = $classid;
        }
        $stmt->close();
        //sql for teachers
        //some unholy sql, idk how to do it better
        $sql = "SELECT Teacher FROM TeacherClass WHERE Class IN (SELECT Class FROM StudentClass WHERE Student IN (SELECT Student FROM ParentStudent WHERE Parent=?))";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($teacherid);
        $this->teachers = array();
        while ($stmt->fetch()) {
            $this->teachers[] = $teacherid;
        }
        $stmt->close();
    }
    public function get_name()
    {
        return $this->name;
    }
    public function get_students()
    {
        return $this->students;
    }
    public function get_id()
    {
        return $this->id;
    }
    public function get_classes()
    {
        return $this->classes;
    }
    public function get_teachers()
    {
        return $this->teachers;
    }
    public function get_account()
    {
        return $this->account;
    }
}
class Class_
{
    private $id;
    private $name;
    private $students;
    private $teachers;
    private $parents;

    public function __construct($id)
    {
        $this->id = $id;
    }
    public function update()
    {
        $id = $this->id;
        $studentid = "";
        $teacherid = "";
        $parentid = "";
        //sql for name
        $sql = "SELECT Name FROM Class WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->name);
        $stmt->fetch();
        $stmt->close();

        //sql for students
        $sql = "SELECT Student FROM StudentClass WHERE Class=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($studentid);
        $this->students = array();
        while ($stmt->fetch()) {
            $this->students[] = $studentid;
        }
        $stmt->close();
        //sql for teachers
        $sql = "SELECT Teacher FROM TeacherClass WHERE Class=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($teacherid);
        $this->teachers = array();
        while ($stmt->fetch()) {
            $this->teachers[] = $teacherid;
        }
        $stmt->close();
        //sql for parents
        $sql = "SELECT Parent FROM ParentStudent WHERE Student IN (SELECT Student FROM StudentClass WHERE Class=?)";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($parentid);
        $this->parents = array();
        while ($stmt->fetch()) {
            $this->parents[] = $parentid;
        }
        $stmt->close();
    }
    public function get_name()
    {
        return $this->name;
    }
    public function get_students()
    {
        return $this->students;
    }
    public function get_teachers()
    {
        return $this->teachers;
    }
    public function get_id()
    {
        return $this->id;
    }
    public function get_parents()
    {
        return $this->parents;
    }
    public function set_name($name)
    {
        $this->name = $name;
    }
    public function set_students($students)
    {
        $this->students = $students;
    }
    public function set_teachers($teachers)
    {
        $this->teachers = $teachers;
    }
    public function set_parents($parents)
    {
        $this->parents = $parents;
    }
    public function add_student($student)
    {
        $this->students[] = $student;
    }
    public function add_teacher($teacher)
    {
        $this->teachers[] = $teacher;
    }
    public function add_parent($parent)
    {
        $this->parents[] = $parent;
    }
}
class Account
{
    private $id;
    private $name;
    private $email;
    private $phone;
    private $password;
    private $teacherid;
    private $parentid;
    public function __construct($id)
    {
        $this->id = $id;
    }
    public function update()
    {
        $id = $this->id;
        //sql for username
        $sql = "SELECT Email, Password FROM User WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->email, $this->password);
        $stmt->fetch();
        $stmt->close();
        //sql for teacherid
        $sql = "SELECT ID FROM Teacher WHERE UserID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->teacherid);
        $stmt->fetch();
        $stmt->close();
        //sql for parentid
        $sql = "SELECT ID FROM Parent WHERE UserID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->parentid);
        $stmt->fetch();
        $stmt->close();
        //sql for phone
        $sql = "SELECT Phone FROM User WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->phone);
        $stmt->fetch();
        $stmt->close();
        //sql for name
        $sql = "SELECT Name FROM User WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->name);
        $stmt->fetch();
        $stmt->close();
    }
    public function get_name()
    {
        return $this->name;
    }
    public function get_email()
    {
        return $this->email;
    }
    public function get_password()
    {
        return $this->password;
    }
    public function get_id()
    {
        return $this->id;
    }
    public function get_teacherid()
    {
        return $this->teacherid;
    }
    public function get_parentid()
    {
        return $this->parentid;
    }
    public function get_phone()
    {
        return $this->phone;
    }
}
