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
//get the Class id from the request
//if its a post get the post id if not try to get it from get
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getClassID'])) {
    $id = $_POST['id'];
    //get the Class object from the database
    $class_ = new class_($id);
    $class_->update();
    $class_id = $class_->get_id();
    $class_name = $class_->get_name();
    $class_students = $class_->get_students();
    $class_teachers = $class_->get_teachers();
    //format the json response
    $response = array(
        "id" => $class_id,
        "name" => $class_name,
        "students" => $class_students,
        "teachers" => $class_teachers
    );
    echo json_encode($response);
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gettabledata'])) {
    $classes = get_all_classes();
    foreach ($classes as $class_) {
        echo "<tr id=ClassRow", $class_->get_id(), ">";
        echo "<td>", $class_->get_id(), "</td>";
        echo "<td>", $class_->get_name(), "</td>";
        echo "<td>";
        if (count($class_->get_teachers()) == 0) {
            echo "";
        } else {
            foreach ($class_->get_teachers() as $teacher) {
                $teacher_ = new teacher($teacher);
                $teacher_->update();
                echo $teacher_->get_name(), "<br> ";
            }
        }
        echo "</td>";
        echo "<td>", count($class_->get_students()), "</td>";
        echo "<td><button type='button' class='btn btn-danger' id='delete", $class_->get_id(), "'>Delete</button></td>";
        echo "</tr>";
    }
    die();
}
//if the request is a post and the id is set, check if the Class exists, if it does update it, if not create it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateClass'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $students = $_POST['students'];
    $teachers = $_POST['teachers'];
    //check if the values passed are valid
    //format the variables properly
    $id = intval($id);
    $name = strval($name);
    //the arrays will be passed as strings so we need to convert them to arrays
    //check if there is a value
    if ($students != "") {
        //if there is a value then convert it to an array, strip []
        $students = str_replace("[", "", $students);
        $students = str_replace("]", "", $students);
        $students = explode(",", $students);
        //convert the array to an array of ints
        foreach ($students as $key => $value) {
            $students[$key] = intval($value);
        }
    } else {
        //if there is no value then set the array to an empty array
        $students = null;
    }
    //check if there is a value
    if ($teachers != "") {
        //if there is a value then convert it to an array
        $teachers = str_replace("[", "", $teachers);
        $teachers = str_replace("]", "", $teachers);
        $teachers = explode(",", $teachers);
        //convert the array to an array of ints
        foreach ($teachers as $key => $value) {
            $teachers[$key] = intval($value);
        }
    } else {
        //if there is no value then set the array to an empty array
        $classes = null;
    }
    //check if the Class exists
    if (Class__exists($id)) {
        //update the Class
        $respon = update_Class($id, $name, $students, $teachers);
    } else {
        //create the classes
        $respon = create_Class($id, $name, $students, $teachers);
    }
    if ($respon) {
        $response = array(
            "success" => "Class updated successfully"
        );
        echo json_encode($response);
        die();
    } else {
        $response = array(
            "error" => "Class does not exist"
        );
        echo json_encode($response);
        die();
    }
}
//if the request is a post and the id is set, check if the Class exists, if it does delete it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteClass'])) {
    $id = $_POST['id'];
    //check if the Class exists
    if (Class__exists($id)) {
        //delete the Class
        $respon = delete_Class($id);
    } else {
        $response = array(
            "error" => "Class does not exist"
        );
        echo json_encode($response);
        die();
    }
    if ($respon) {
        $response = array(
            "success" => "Class deleted successfully"
        );
        echo json_encode($response);
        die();
    } else {
        $response = array(
            "error" => "Class does not exist"
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

        function updateTable() {
            $.ajax({
                type: "POST",
                url: "/admin/classes.php",
                data: {
                    gettabledata: true
                },
                success: function(data) {
                    $('#mainbody').html(data);
                }
            });
        }
        //on document load
        $(document).ready(function() {
            //make the multiselects searchable and 
            $('#StudentSelector').select2({
                theme: "bootstrap-5",
                width: '100%',
                multiple: true,
                placeholder: "Select Students",
                allowClear: true
            });
            $('#TeacherSelector').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "Select Teachers",
                multiple: true,
                allowClear: true
            });
            updateTable();
        });
        //when a Class is selected from the table, update the edit form to show the Class's details
        $(document).on('click', 'tr', function() {
            //check if the row is the header row
            if ($(this).find("th").length > 0) {
                return;
            }
            //get the Class id from the row
            var ClassID = $(this).find("td:first").html();
            //print the id to the console
            console.log(ClassID);
            $(this).addClass('table-danger').siblings().removeClass('table-danger');
            //get the Class object from the database
            $.ajax({
                type: "POST",
                url: "/admin/classes.php",
                data: {
                    getClassID: true,
                    id: ClassID
                },
                success: function(data) {
                    //parse the json response
                    var Class = JSON.parse(data);
                    //set the values of the edit form to the Class's details
                    $('#ClassID').val(Class.id);
                    $('#ClassName').val(Class.name);
                    $('#StudentSelector').val(Class.students);
                    $('#TeacherSelector').val(Class.teachers);
                    //refresh the multiselects
                    $('#StudentSelector').trigger('change');
                    $('#TeacherSelector').trigger('change');
                    //change the text in the submit button to update
                    $('#submitFormButton').text("Update");
                }
            });
        });
        //clear button to stop selecting a Class
        $(document).on('click', '#clear', function() {
            //remove the selected class from the table
            $('tr').removeClass('table-danger');
            //clear the edit form
            $('#ClassID').val("");
            $('#ClassName').val("");
            $('#StudentSelector').val("");
            $('#TeacherSelector').val("");
            //refresh the multiselects
            $('#StudentSelector').trigger('change');
            $('#TeacherSelector').trigger('change');
            //change the text in the submit button to add
            $('#submitFormButton').text("Add");
        });
        //on ready clear the edit form
        $(document).ready(function() {
            $('#clear').click();
            var height = $(window).height() - 240;
            $('.table-scroll tbody').css('height', height);
        });
        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - 240;
            //just tbody
            $('.table-scroll tbody').css('height', height);
        });
        //on submit
        $(document).on('submit', '#ClassUpdateForm', function(e) {
            //prevent the default action
            e.preventDefault();
        });
        //on click of the add/update button
        $(document).on('click', '#submitFormButton', function() {
            //if the button says add
            if ($(this).text() == "Add") {
                //submit the form using xhttprequest
                $id = $('#ClassID').val();
                $name = $('#ClassName').val();
                $students = $('#StudentSelector').val();
                $teachers = $('#TeacherSelector').val();

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
                                    updateTable();
                                }
                            }
                        } else {
                            //if the request was not successful then we can display an error message
                            alert(this.responseText);
                        }
                    }
                };
                xhttp.open("POST", "/admin/classes.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("updateClass=true" + "&id=" + $id + "&name=" + $name + "&students=" + $students + "&teachers=" + $teachers);
            } else {
                //if the button says update
                //show the confirmation popup
                $('#confirm').show();
                //get the Class id from the form
                var ClassID = $('#ClassID').val();
                //print the id to the console
                console.log(ClassID);
                $.ajax({
                    type: "POST",
                    url: "/admin/classes.php",
                    data: {
                        getClassID: true,
                        id: ClassID
                    },
                    success: function(data) {
                        var Class = JSON.parse(data);
                        $('#confirmText').text("Are you sure you want to update " + Class.name + "?");
                    }
                });
            }
        });
        //on click of the confirm button
        $(document).on('click', '#confirmButton', function() {
            //submit the form
            $id = $('#ClassID').val();
            $name = $('#ClassName').val();
            $students = $('#StudentSelector').val();
            $teachers = $('#TeacherSelector').val();
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4) {
                    if (this.status == 200) {
                        var response = JSON.parse(this.responseText);
                        if (response.error) {
                            $('#error').html(response.error);
                        } else {
                            if (response.success) {
                                $('#error').html(response.success);
                                $('#clear').click();
                                $('#confirm').hide();
                                updateTable();
                            }
                        }
                    } else {
                        alert(this.responseText);
                    }
                }
            };
            xhttp.open("POST", "/admin/classes.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("updateClass=true" + "&id=" + $id + "&name=" + $name + "&students=" + $students + "&teachers=" + $teachers);
        });
        //on click of the cancel button
        $(document).on('click', '#cancelButton', function() {
            //hide the confirmation popup
            $('#confirm').hide();
        });
        $(document).on('click', 'button[id^="delete"]', function() {
            //get the teacher id from the button id
            var ClassID = $(this).attr('id').replace('delete', '');
            //print the id to the console
            console.log(ClassID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/classes.php",
                data: {
                    getClassID: true,
                    id: ClassID
                },
                success: function(data) {
                    var Class = JSON.parse(data);
                    $('#confirmDeletionText').text("Are you sure you want to delete " + Class.name + "?");
                }
            });
            //show the confirmation popup
            $('#confirmDeletion').show();
        });
        //on click of the confirm button
        $(document).on('click', '#confirmDeletionButton', function() {
            var ClassID = $('#ClassID').val();
            console.log(ClassID);
            $.ajax({
                type: "POST",
                url: "/admin/classes.php",
                data: {
                    deleteClass: true,
                    id: ClassID
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

        .ClassForm input {
            margin: 2px;
        }
    </style>
</head>
<?php include_once '../admin/nav.php'; ?>
<!--searchable table of classes-->

<body>
    <div style="display: flex; margin-top: 2pt;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>Classes</h1>
                    <p>Here you can view all the classes in the school</p>
                    <input class="form-control" id="myInput" type="text" onkeyup="searchupdate()" placeholder="Search..">
                    <div class="well">
                        <table class="table table-striped table-scroll table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Class ID</th>
                                    <th scope="col">Class Name</th>
                                    <th scope="col">Teachers</th>
                                    <th scope="col">Students</th>
                                    <th scope="col">Delete</th>
                                </tr>
                            </thead>
                            <tbody id="mainbody">
                                <?php
                                ?>
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
                <form id="ClassUpdateForm" class="ClassForm">
                    <!--Class id, name, yeargroup, select multiclasses from a list of all classes, select parents from a list of multi parents-->
                    <input type="text" class="form-control" placeholder="Class ID" id="ClassID" style="margin-bottom: 6px">
                    <input type="text" class="form-control" placeholder="Class Name" id="ClassName" style="margin-bottom: 6px;">
                    <select class="form-select" id="StudentSelector">
                        <?php
                        $students = get_all_students();
                        foreach ($students as $student) {
                            echo "<option value=", $student->get_id(), ">", $student->get_name(), "</option>";
                        }
                        ?>
                    </select>
                    <div style="margin-top: 6px;">
                        <select class="form-select" id="TeacherSelector">
                            <?php
                            $teachers = get_all_teachers();
                            foreach ($teachers as $teacher) {
                                echo "<option value=", $teacher->get_id(), ">", $teacher->get_name(), "</option>";
                            }
                            ?>
                        </select>
                    </div>
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
            <p id="confirmText">Are you sure you want to update this Class?</p>
            <button type="button" id="confirmButton" class="btn btn-primary">Confirm</button>
            <button type="button" id="cancelButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
    <div id="confirmDeletion" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
        <!--confirmation box-->
        <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
            <p id="confirmDeletionText">Are you sure you want to delete this Class?</p>
            <button type="button" id="confirmDeletionButton" class="btn btn-danger">Confirm</button>
            <button type="button" id="cancelDeletionButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
</body>