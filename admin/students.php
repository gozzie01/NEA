<?php
require_once '../utils.php';
//check if the user is logged in
if (!is_logged_in()) {
    header("Location: /login.php");
    die();
}
//check if admin else send to index
if (!is_admin()) {
    header("Location: /index.php");
    die();
}
//get the student id from the request
//if its a post get the post id if not try to get it from get
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getstudentID'])) {
    $id = $_POST['id'];
    //get the student object from the database
    $student = new Student($id);
    $student->update();
    $student_id = $student->get_id();
    $student_name = $student->get_name();
    $student_year = $student->get_year();
    $student_parents = $student->get_parents();
    $student_classes = $student->get_classes();
    $student_teachers = $student->get_teachers();
    //format the json response
    $response = array(
        "id" => $student_id,
        "name" => $student_name,
        "year" => $student_year,
        "parents" => $student_parents,
        "classes" => $student_classes,
        "teachers" => $student_teachers
    );
    echo json_encode($response);
    die();
}
//if server request is a post and the table data flag is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['GetTableData'])) {
    $students = get_all_students();
    foreach ($students as $student) {
        echo "<tr id=studentRow", $student->get_id(), ">";
        echo "<td>", $student->get_id(), "</td>";
        echo "<td>", $student->get_name(), "</td>";
        echo "<td>", $student->get_year(), "</td>";
        echo "<td>", count($student->get_classes()), "</td>";
        echo "<td>", count($student->get_parents()), "</td>";
        echo "<td><button type='button' class='btn btn-danger' id='delete", $student->get_id(), "'>Delete</button></td>";
        echo "</tr>";
    }
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['GetParentSelector'])) {
    $parents = get_all_parents();
    foreach ($parents as $parent) {
        echo "<option value='", $parent->get_id(), "'>", $parent->get_name(), "</option>";
    }
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['GetClassSelector'])) {
    $classes = get_all_classes();
    foreach ($classes as $class) {
        echo "<option value='", $class->get_id(), "'>", $class->get_name(), "</option>";
    }
    die();
}

//if the request is a post and the id is set, check if the student exists, if it does update it, if not create it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updatestudent'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $year = $_POST['year'];
    $parents = $_POST['parents'];
    $classes = $_POST['classes'];
    //check if the values passed are valid
    //format the variables properly
    $id = intval($id);
    $name = strval($name);
    $year = intval($year);
    //the arrays will be passed as strings so we need to convert them to arrays
    //check if there is a value
    if ($parents != "") {
        //if there is a value then convert it to an array, strip []
        $parents = str_replace("[", "", $parents);
        $parents = str_replace("]", "", $parents);
        $parents = explode(",", $parents);
        //convert the array to an array of ints
        foreach ($parents as $key => $value) {
            $parents[$key] = intval($value);
        }
    } else {
        //if there is no value then set the array to an empty array
        $parents = null;
    }
    //check if there is a value
    if ($classes != "") {
        //if there is a value then convert it to an array
        $classes = str_replace("[", "", $classes);
        $classes = str_replace("]", "", $classes);
        $classes = explode(",", $classes);
        //convert the array to an array of ints
        foreach ($classes as $key => $value) {
            $classes[$key] = intval($value);
        }
    } else {
        //if there is no value then set the array to an empty array
        $classes = null;
    }
    //check if the student exists
    if (student_exists($id)) {
        //update the student
        $respon = update_student($id, $name, $year, $parents, $classes);
    } else {
        //create the students
        $respon = create_student($id, $name, $year, $parents, $classes);
    }
    if ($respon) {
        $response = array(
            "success" => "Student updated successfully"
        );
        echo json_encode($response);
        die();
    } else {
        $response = array(
            "error" => "Student does not exist"
        );
        echo json_encode($response);
        die();
    }
}
//if the request is a post and the id is set, check if the student exists, if it does delete it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletestudent'])) {
    $id = $_POST['id'];
    //check if the student exists
    if (student_exists($id)) {
        //delete the student
        $respon = delete_student($id);
    } else {
        $response = array(
            "error" => "Student does not exist"
        );
        echo json_encode($response);
        die();
    }
    if ($respon) {
        $response = array(
            "success" => "Student deleted successfully"
        );
        echo json_encode($response);
        die();
    } else {
        $response = array(
            "error" => "Student does not exist"
        );
        echo json_encode($response);
        die();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <title>Admin</title>
    <script>
        function searchupdate() {
            // Declare variables
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("myInput");
            filter = input.value.toUpperCase();
            table = document.getElementsByTagName("table")[0];
            tr = table.getElementsByTagName("tr");
            // Loop through all table rows, and hide those who don't match the search query
            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) == -1) {
                        tr[i].style.display = "none";
                    } else {
                        tr[i].style.display = "";
                    }
                }
            }
        }
        //on document load
        $(document).ready(function() {
            //get the classes and parents from the database
            $.ajax({
                type: "POST",
                url: "/admin/students.php",
                data: {
                    GetParentSelector: true
                },
                success: function(data) {
                    //parse the json response
                    $('#ParentSelector').html(data);
                }
            });
            $.ajax({
                type: "POST",
                url: "/admin/students.php",
                data: {
                    GetClassSelector: true
                },
                success: function(data) {
                    //parse the json response
                    $('#ClassSelector').html(data);
                }
            });

            //make the multiselects searchable and 
            $('#ClassSelector').select2({
                theme: "bootstrap-5",
                width: '100%',
                multiple: true,
                placeholder: "Select Classes",
                allowClear: true
            });
            $('#ParentSelector').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "Select Parents",
                multiple: true,
                allowClear: true
            });

        });
        //when a student is selected from the table, update the edit form to show the student's details
        $(document).on('click', 'tr', function() {
            if ($(this).find("th").length > 0) {
                return;
            }
            //get the student id from the row
            var studentID = $(this).find("td:first").html();
            //print the id to the console
            console.log(studentID);
            $(this).addClass('table-danger').siblings().removeClass('table-danger');
            //get the student object from the database
            $.ajax({
                type: "POST",
                url: "/admin/students.php",
                data: {
                    getstudentID: true,
                    id: studentID
                },
                success: function(data) {
                    //parse the json response
                    var student = JSON.parse(data);
                    //set the values of the edit form to the student's details
                    $('#StudentID').val(student.id);
                    $('#StudentName').val(student.name);
                    $('#YearGroup').val(student.year);
                    $('#ClassSelector').val(student.classes);
                    $('#ParentSelector').val(student.parents);
                    //refresh the multiselects
                    $('#ClassSelector').trigger('change');
                    $('#ParentSelector').trigger('change');
                    //change the text in the submit button to update
                    $('#submitFormButton').text("Update");
                }
            });
        });
        //clear button to stop selecting a student
        $(document).on('click', '#clear', function() {
            //remove the selected class from the table
            $('tr').removeClass('table-danger');
            //clear the edit form
            $('#StudentID').val("");
            $('#StudentName').val("");
            $('#YearGroup').val("");
            $('#ClassSelector').val("");
            $('#ParentSelector').val("");
            //refresh the multiselects
            $('#ClassSelector').trigger('change');
            $('#ParentSelector').trigger('change');
            //change the text in the submit button to add
            $('#submitFormButton').text("Add");
        });
        //on ready clear the edit form
        $(document).ready(function() {
            $('#clear').click();
            var height = $(window).height() - 240;
            $('.table-scroll tbody').css('height', height);
            //get the table data
            $.ajax({
                type: "POST",
                url: "/admin/students.php",
                data: {
                    GetTableData: true
                },
                success: function(data) {
                    //parse the json response
                    $('#mainbody').html(data);
                }
            });
        });
        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - 240;
            //just tbody
            $('.table-scroll tbody').css('height', height);
        });
        //on submit
        $(document).on('submit', '#StudentUpdateForm', function(e) {
            //prevent the default action
            e.preventDefault();
        });
        //on click of the add/update button
        function updateTable() {
            $.ajax({
                type: "POST",
                url: "/admin/students.php",
                data: {
                    GetTableData: true
                },
                success: function(data) {
                    //parse the json response
                    $('#mainbody').html(data);
                }
            });
        }
        $(document).on('click', '#submitFormButton', function() {
            //if the button says add
            if ($(this).text() == "Add") {
                //submit the form using xhttprequest
                $id = $('#StudentID').val();
                $name = $('#StudentName').val();
                $year = $('#YearGroup').val();
                $classes = $('#ClassSelector').val();
                $parents = $('#ParentSelector').val();

                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    //if the request is complete
                    if (this.readyState == 4) {
                        //if the request was successful
                        if (this.status == 200) {
                            //if the request was successful then we can redirect the user to the index page
                            var response = JSON.parse(this.responseText);
                            if (response.error) {
                                $('#error').html(response.error);
                            } else {
                                if (response.success) {
                                    $('#error').html(response.success);
                                    $('#clear').click();
                                }
                            }
                        } else {
                            //if the request was not successful then we can display an error message
                            alert(this.responseText);
                        }
                    }
                };
                xhttp.open("POST", "/admin/students.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("updatestudent=true" + "&id=" + $id + "&name=" + $name + "&year=" + $year + "&classes=" + $classes + "&parents=" + $parents);
                updateTable();
            } else {
                //if the button says update
                //show the confirmation popup
                $('#confirm').show();
                //get the student id from the form
                var studentID = $('#StudentID').val();
                //print the id to the console
                console.log(studentID);
                //get the student object from the database
                $.ajax({
                    type: "POST",
                    url: "/admin/students.php",
                    data: {
                        getstudentID: true,
                        id: studentID
                    },
                    success: function(data) {
                        //parse the json response
                        var student = JSON.parse(data);
                        //set the text of the confirmation popup
                        $('#confirmText').text("Are you sure you want to update " + student.name + "?");
                    }
                });
            }
        });
        //on click of the confirm button
        $(document).on('click', '#confirmButton', function() {
            //submit the form
            $id = $('#StudentID').val();
            $name = $('#StudentName').val();
            $year = $('#YearGroup').val();
            $classes = $('#ClassSelector').val();
            $parents = $('#ParentSelector').val();
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                //if the request is complete
                if (this.readyState == 4) {
                    //if the request was successful
                    if (this.status == 200) {
                        //if the request was successful then we can redirect the user to the index page
                        var response = JSON.parse(this.responseText);
                        if (response.error) {
                            $('#error').html(response.error);
                        } else {
                            if (response.success) {
                                $('#error').html(response.success);
                                $('#clear').click();
                                $('#confirm').hide();
                            }
                        }
                    } else {
                        //if the request was not successful then we can display an error message
                        alert(this.responseText);
                    }
                }
            };
            xhttp.open("POST", "/admin/students.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("updatestudent=true" + "&id=" + $id + "&name=" + $name + "&year=" + $year + "&classes=" + $classes + "&parents=" + $parents);
            updateTable();
        });
        //on click of the cancel button
        $(document).on('click', '#cancelButton', function() {
            //hide the confirmation popup
            $('#confirm').hide();
        });
        $(document).on('click', 'button[id^="delete"]', function() {
            //get the teacher id from the button id
            var studentID = $(this).attr('id').replace('delete', '');
            //print the id to the console
            console.log(studentID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/students.php",
                data: {
                    getstudentID: true,
                    id: studentID
                },
                success: function(data) {
                    //parse the json response
                    var student = JSON.parse(data);
                    //set the text of the confirmation popup
                    $('#confirmDeletionText').text("Are you sure you want to delete " + student.name + "?");
                }
            });
            //show the confirmation popup
            $('#confirmDeletion').show();
        });
        //on click of the confirm button
        $(document).on('click', '#confirmDeletionButton', function() {
            //get the teacher id from the form
            var studentID = $('#StudentID').val();
            //print the id to the console
            console.log(studentID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/students.php",
                data: {
                    deletestudent: true,
                    id: studentID
                },
                success: function(data) {
                    //parse the json response
                    var response = JSON.parse(data);

                    //set the text of the confirmation popup
                    if (response.error) {
                        $('#error').html(response.error);
                    } else {
                        if (response.success) {
                            $('#error').html(response.success);
                            $('#clear').click();
                            $('#confirmDeletion').hide();
                            updateTable();
                        }
                    }
                }
            });
        });
        //on click of the cancel button
        $(document).on('click', '#cancelDeletionButton', function() {
            //hide the confirmation popup
            $('#confirmDeletion').hide();
        });
    </script>
    <style type="text/css">
        .well {
            background: none;
            height: 320px;
        }

        .table-scroll tbody {
            overflow-y: scroll;
            height: 200px;
        }

        .table-scroll tr {
            table-layout: fixed;
            display: inline-table;
        }

        .StudentForm input {
            margin: 2px;
        }
    </style>
</head>
<?php include_once '../admin/nav.php'; ?>
<!--searchable table of students-->

<body>
    <div style="display: flex; margin-top: 2pt;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>Students</h1>
                    <p>Here you can view all the students in the school</p>
                    <input class="form-control" id="myInput" type="text" onkeyup="searchupdate()" placeholder="Search..">
                    <div class="well">
                        <table class="table table-striped table-scroll table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Student ID</th>
                                    <th scope="col">Student Name</th>
                                    <th scope="col">Year Group</th>
                                    <th scope="col">Classes</th>
                                    <th scope="col">Parent(s)</th>
                                    <th scope="col">Delete</th>
                                </tr>
                            </thead>
                            <tbody id="mainbody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!--add a edit page on the right, non-functional for now-->
        <div class="container" style="max-width: 25%;">
            <div class="col">
                <h1>Edit</h1>
                <form id="StudentUpdateForm" class="StudentForm">
                    <!--student id, name, yeargroup, select multiclasses from a list of all classes, select parents from a list of multi parents-->
                    <input type="text" class="form-control" placeholder="Student ID" id="StudentID">
                    <input type="text" class="form-control" placeholder="Student Name" id="StudentName">
                    <input type="text" class="form-control" placeholder="Year Group" id="YearGroup">
                    <select class="form-select" id="ClassSelector">

                    </select>
                    <select class="form-select" id="ParentSelector">

                    </select>
                    <button type="submit" id="submitFormButton" class="btn btn-primary">Add</button>
                    <!--clear button-->
                    <button type="button" id="clear" class="btn btn-primary">Clear</button>
                    <div id="error"></div>
                </form>
            </div>
        </div>
    </div>
    <!--hidden confirmation popup make it in the middle of the screen grey everything behind-->
    <div id="confirm" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
        <!--confirmation box-->
        <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
            <p id="confirmText">Are you sure you want to update this student?</p>
            <button type="button" id="confirmButton" class="btn btn-primary">Confirm</button>
            <button type="button" id="cancelButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
    <div id="confirmDeletion" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
        <!--confirmation box-->
        <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
            <p id="confirmDeletionText">Are you sure you want to delete this student?</p>
            <button type="button" id="confirmDeletionButton" class="btn btn-danger">Confirm</button>
            <button type="button" id="cancelDeletionButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
</body>