<?php
class Student
{
    private $id;
    private $name;
    private $teachers;
    private $year;
    private $classes;
    private $parents;

    public function __construct(int $id)
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
    public function getName()
    {
        return $this->name;
    }
    public function getClasses()
    {
        return $this->classes;
    }
    public function getTeachers()
    {
        return $this->teachers;
    }
    public function getParents()
    {
        return $this->parents;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getYear()
    {
        return $this->year;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setClasses($classes)
    {
        $this->classes = $classes;
    }
    public function addClass($class)
    {
        $this->classes[] = $class;
    }
    public function addParent($parent)
    {
        $this->parents[] = $parent;
    }
    public function addTeacher($teacher)
    {
        $this->teachers[] = $teacher;
    }
    public function setTeachers($teachers)
    {
        $this->teachers = $teachers;
    }
    public function setParents($parents)
    {
        $this->parents = $parents;
    }
    public function setYear($year)
    {
        $this->year = $year;
    }
}
class Teacher{
    private $id;
    private $name;
    private $account;
    private $pastoral;
    private $classes;
    private $students;
    private $parents;

    public function __construct(int $id)
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
    public function getName()
    {
        return $this->name;
    }
    public function getPastoral()
    {
        return $this->pastoral;
    }
    public function getAccount()
    {
        return $this->account;
    }
    public function getClasses()
    {
        return $this->classes;
    }
    public function getStudents()
    {
        return $this->students;
    }
    public function getID()
    {
        return $this->id;
    }
    public function getParents()
    {
        return $this->parents;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setPastoral($pastoral)
    {
        $this->pastoral = $pastoral;
    }
    public function setClasses($classes)
    {
        $this->classes = $classes;
    }
    public function setStudents($students)
    {
        $this->students = $students;
    }
    public function setAccount($account)
    {
        $this->account = $account;
    }
    public function addClass($class)
    {
        $this->classes[] = $class;
    }
    public function addStudent($student)
    {
        $this->students[] = $student;
    }
    public function addParent($parent)
    {
        $this->parents[] = $parent;
    }
    public function setParents($parents)
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

    public function __construct(int $id)
    {
        $this->id = $id;
        //set students, classes, teachers to empty arrays
        $this->students = array();
        $this->classes = array();
        $this->teachers = array();
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
    public function getName()
    {
        return $this->name;
    }
    public function getStudents()
    {
        return $this->students;
    }
    public function getID()
    {
        return $this->id;
    }
    public function getClasses()
    {
        return $this->classes;
    }
    public function getTeachers()
    {
        return $this->teachers;
    }
    public function getAccount()
    {
        return $this->account;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setStudents($students)
    {
        $this->students = $students;
    }
    public function setClasses($classes)
    {
        $this->classes = $classes;
    }
    public function setTeachers($teachers)
    {
        $this->teachers = $teachers;
    }
    public function setAccount($account)
    {
        $this->account = $account;
    }
    public function addStudent($student)
    {
        $this->students[] = $student;
    }
    public function addClass($class)
    {
        $this->classes[] = $class;
    }
    public function addTeacher($teacher)
    {
        $this->teachers[] = $teacher;
    }
}
class Class_
{
    private $id;
    private $name;
    private $students;
    private $teachers;
    private $parents;

    public function __construct(int $id)
    {
        $this->id = $id;
        //set students, teachers, parents to empty arrays
        $this->students = array();
        $this->teachers = array();
        $this->parents = array();
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
    public function getName()
    {
        return $this->name;
    }
    public function getStudents()
    {
        return $this->students;
    }
    public function getTeachers()
    {
        return $this->teachers;
    }
    public function getID()
    {
        return $this->id;
    }
    public function getParents()
    {
        return $this->parents;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setStudents($students)
    {
        $this->students = $students;
    }
    public function setTeachers($teachers)
    {
        $this->teachers = $teachers;
    }
    public function setParents($parents)
    {
        $this->parents = $parents;
    }
    public function addStudent($student)
    {
        $this->students[] = $student;
    }
    public function addTeacher($teacher)
    {
        $this->teachers[] = $teacher;
    }
    public function addParent($parent)
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
    public function __construct(int $id)
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
    public function getName()
    {
        return $this->name;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getID()
    {
        return $this->id;
    }
    public function getTeacherID()
    {
        return $this->teacherid;
    }
    public function getParentID()
    {
        return $this->parentid;
    }
    public function getPhone()
    {
        return $this->phone;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function setPassword($password)
    {
        $this->password = $password;
    }
    public function setTeacherID($teacherid)
    {
        $this->teacherid = $teacherid;
    }
    public function setParentID($parentid)
    {
        $this->parentid = $parentid;
    }
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
}
