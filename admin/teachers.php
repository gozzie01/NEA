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
//get the teacher id from the request
//if its a post get the post id if not try to get it from get
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getteacherID'])) {
    try {
        $id = $_POST['id'];
        //get the teacher object from the database
        $teacher = new teacher($id);
        $teacher->update();
        $teacher_id = $teacher->get_id();
        $teacher_name = $teacher->get_name();
        $teacher_account = $teacher->get_account();
        $teacher_classes = $teacher->get_classes();
        $teacher_pastoral = $teacher->get_pastoral();
        $teacher_students = $teacher->get_students();
        //format the json response
        $response = array(
            "id" => $teacher_id,
            "name" => $teacher_name,
            "account" => $teacher_account,
            "classes" => $teacher_classes,
            "pastoral" => $teacher_pastoral,
            "students" => $teacher_students
        );
        echo json_encode($response);
        die();
    } catch (Exception $e) {
        $response = array(
            "error" => "teacher does not exist"
        );
        echo json_encode($response);
        die();
    }
}
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['gettabledata']))
{
    $teachers = get_all_teachers();
    foreach ($teachers as $teacher) {
        echo "<tr id=teacherRow", $teacher->get_id(), ">";
        echo "<td>", $teacher->get_id(), "</td>";
        echo "<td>", $teacher->get_name(), "</td>";
        echo "<td>", $teacher->get_account(), "</td>";
        echo "<td>", $teacher->get_pastoral() ? "yes" : "no", "</td>";
        echo "<td>", count($teacher->get_classes()), "</td>";
        echo "<td>", count($teacher->get_students()), "</td>";
        echo "<td><button type='button' class='btn btn-danger' id='delete", $teacher->get_id(), "'>Delete</button></td>";
        echo "</tr>";
    }
    die();
}
//if the request is a post and the id is set, check if the teacher exists, if it does update it, if not create it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateteacher'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $account = $_POST['account'];
    $classes = $_POST['classes'];
    $pastoral = $_POST['pastoral'];
    //check if the values passed are valid
    //format the variables properly
    $id = intval($id);
    $name = strval($name);
    $account = intval($account);
    $pastoral = intval($pastoral);
    //the arrays will be passed as strings so we need to convert them to arrays
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
    //check if the teacher exists
    if (teacher_exists($id)) {
        //update the teacher
        $respon = update_teacher($id, $name, $pastoral, $classes, $account);
    } else {
        //create the teachers
        $respon = create_teacher($id, $name, $pastoral, $classes, $account);
    }
    if ($respon) {
        $response = array(
            "success" => "teacher updated successfully"
        );
        echo json_encode($response);
        die();
    } else {
        $response = array(
            "error" => "teacher does not exist"
        );
        echo json_encode($response);
        die();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteteacher'])) {
    $id = $_POST['id'];
    //check if the values passed are valid
    //format the variables properly
    $id = intval($id);
    //check if the teacher exists
    if (teacher_exists($id)) {
        //delete the teacher
        $respon = delete_teacher($id);
    } else {
        $response = array(
            "error" => "teacher does not exist"
        );
        echo json_encode($response);
        die();
    }
    if ($respon) {
        $response = array(
            "success" => "teacher deleted successfully"
        );
        echo json_encode($response);
        die();
    } else {
        $response = array(
            "error" => "teacher does not exist"
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
            //make the multiselects searchable and 
            $('#ClassSelector').select2({
                theme: "bootstrap-5",
                width: '100%',
                multiple: true,
                placeholder: "Select Classes",
                allowClear: true
            });
            $('#AccountSelector').select2({
                theme: "bootstrap-5",
                width: '100%',
                multiple: false,
                placeholder: "Select Account",
                allowClear: true
            });
            var height = $(window).height() - 240;
            //just tbody
            $('.table-scroll tbody').css('height', height);
        });
        //on resize
        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - 240;
            //just tbody
            $('.table-scroll tbody').css('height', height);

        });
        //when a teacher is selected from the table, update the edit form to show the teacher's details
        $(document).on('click', 'tr', function() {
            //get the teacher id from the row
            var teacherID = $(this).find("td:first").html();
            console.log(teacherID);
            //remove all other selected classes from the table
            $('tr').removeClass('table-danger');
            $(this).addClass('table-danger');
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/teachers.php",
                data: {
                    getteacherID: true,
                    id: teacherID
                },
                success: function(data) {
                    var teacher = JSON.parse(data);
                    $('#teacherID').val(teacher.id);
                    $('#teacherName').val(teacher.name);
                    $('#AccountSelector').val(teacher.account);
                    $('#ClassSelector').val(teacher.classes);
                    document.getElementById('PastoralCheckbox').checked = teacher.pastoral;
                    $('#ClassSelector').trigger('change');
                    $('#AccountSelector').trigger('change');
                    $('#submitFormButton').text("Update");
                }
            });
        });
        //clear button to stop selecting a teacher
        $(document).on('click', '#clear', function() {
            //remove the selected class from the table
            $('tr').removeClass('table-danger');
            //clear the edit form
            $('#teacherID').val("");
            $('#teacherName').val("");
            document.getElementById('PastoralCheckbox').checked = false;
            $('#ClassSelector').val("");
            //refresh the multiselects
            $('#ClassSelector').trigger('change');
            //change the text in the submit button to add
            $('#submitFormButton').text("Add");
        });
        //on ready clear the edit form
        $(document).ready(function() {
            $('#clear').click();
            updateTable();
        });
        //on submit
        $(document).on('submit', '#teacherUpdateForm', function(e) {
            e.preventDefault();
        });
        //on click of the add/update button
        $(document).on('click', '#submitFormButton', function() {
            //if the button says add
            if ($(this).text() == "Add") {
                //submit the form using xhttprequest
                $id = $('#teacherID').val();
                $name = $('#teacherName').val();
                $account = $('#AccountSelector').val();
                $pastoral = document.getElementById('PastoralCheckbox').checked ? 1 : 0;
                $classes = $('#ClassSelector').val();

                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4) {
                        //if the request was successful
                        if (this.status == 200) {
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
                xhttp.open("POST", "/admin/teachers.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("updateteacher=true" + "&id=" + $id + "&name=" + $name + "&pastoral=" + $pastoral + "&classes=" + $classes + "&account=" + $account);
            } else {
                //if the button says update
                //show the confirmation popup
                $('#confirm').show();
                //get the teacher id from the form
                var teacherID = $('#teacherID').val();
                //print the id to the console
                console.log(teacherID);
                //get the teacher object from the database
                $.ajax({
                    type: "POST",
                    url: "/admin/teachers.php",
                    data: {
                        getteacherID: true,
                        id: teacherID
                    },
                    success: function(data) {
                        //parse the json response
                        var teacher = JSON.parse(data);
                        //set the text of the confirmation popup
                        $('#confirmText').text("Are you sure you want to update " + teacher.name + "?");
                    }
                });
            }
        });
        //on click of the confirm button
        $(document).on('click', '#confirmButton', function() {
            //submit the form
            $id = $('#teacherID').val();
            $name = $('#teacherName').val();
            $account = $('#AccountSelector').val();
            $pastoral = document.getElementById('PastoralCheckbox').checked ? 1 : 0;
            $classes = $('#ClassSelector').val();
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
            xhttp.open("POST", "/admin/teachers.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("updateteacher=true" + "&id=" + $id + "&name=" + $name + "&pastoral=" + $pastoral + "&classes=" + $classes + "&account=" + $account);
        });
        //on click of the cancel button
        $(document).on('click', '#cancelButton', function() {
            //hide the confirmation popup
            $('#confirm').hide();
        });
        //delete button script
        $(document).on('click', 'button[id^="delete"]', function() {
            //get the teacher id from the button id
            var teacherID = $(this).attr('id').replace('delete', '');
            //print the id to the console
            console.log(teacherID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/teachers.php",
                data: {
                    getteacherID: true,
                    id: teacherID
                },
                success: function(data) {
                    //parse the json response
                    var teacher = JSON.parse(data);
                    //set the text of the confirmation popup
                    $('#confirmDeletionText').text("Are you sure you want to delete " + teacher.name + "?");
                }
            });
            //show the confirmation popup
            $('#confirmDeletion').show();
        });
        function updateTable(){
            $.ajax({
                type: "POST",
                url: "/admin/teachers.php",
                data: {
                    gettabledata: true
                },
                success: function(data) {
                    $('#mainbody').html(data);
                }
            });
        }
        //on click of the confirm button
        $(document).on('click', '#confirmDeletionButton', function() {
            //get the teacher id from the form
            var teacherID = $('#teacherID').val();
            //print the id to the console
            console.log(teacherID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/teachers.php",
                data: {
                    deleteteacher: true,
                    id: teacherID
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
    </style>
</head>
<?php include_once '../admin/nav.php'; ?>

<body>
    <div style="display: flex; margin-top: 2pt;">
        <div class="container">
            <div class="row">
                <div class="col well">
                    <h1>Teachers</h1>
                    <p>Here you can view all the teachers in the school</p>
                    <input class="form-control" id="myInput" type="text" onkeyup="searchupdate()" placeholder="Search..">
                    <table class="table table-striped table-hover table-scroll">
                        <thead>
                            <tr>
                                <th scope="col">Teacher ID</th>
                                <th scope="col">Teacher Name</th>
                                <th scope="col">Account</th>
                                <th scope="col">Pastoral</th>
                                <th scope="col">Classes</th>
                                <th scope="col">Students</th>
                                <th scope="col">Delete</th>
                            </tr>
                        </thead>
                        <tbody id="mainbody">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--add a edit page on the right, non-functional for now-->
        <div class="container" style="max-width: 25%;">
            <div class="col">
                <h1>Edit</h1>
                <form id="teacherUpdateForm">
                    <input type="text" class="form-control" placeholder="teacher ID" id="teacherID" style="margin: 1pt">
                    <input type="text" class="form-control" placeholder="Teacher Name" id="teacherName" style="margin: 1pt">
                    <select class="form-select" id="AccountSelector" style="margin: 1pt">
                        <?php
                        $accounts = get_all_accounts();
                        foreach ($accounts as $account) {
                            echo "<option value=", $account->get_id(), ">", $account->get_id(), "</option>";
                        }
                        ?>
                    </select>
                    <div class="d-flex">
                        <p style="margin: 1pt;">Pastoral:</p>
                        <input type="checkbox" class="form-check-input" id="PastoralCheckbox" style="margin: 5pt;">
                    </div>
                    <select class="form-select" id="ClassSelector" style="margin: 1pt">
                        <?php
                        $classes = get_all_classes();
                        foreach ($classes as $class) {
                            echo "<option value=", $class->get_id(), ">", $class->get_name(), "</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" id="submitFormButton" class="btn btn-primary" style="margin: 2pt">Add</button>
                    <!--clear button-->
                    <button type="button" id="clear" class="btn btn-primary" style="margin: 2pt">Clear</button>
                    <div id="error"></div>
                </form>
            </div>
        </div>
    </div>
    <!--hidden confirmation popup make it in the middle of the screen grey everything behind-->
    <div id="confirm" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
        <!--confirmation box-->
        <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
            <p id="confirmText">Are you sure you want to update this teacher?</p>
            <button type="button" id="confirmButton" class="btn btn-primary">Confirm</button>
            <button type="button" id="cancelButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
    <div id="confirmDeletion" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
        <!--confirmation box-->
        <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
            <p id="confirmDeletionText">Are you sure you want to delete this teacher?</p>
            <button type="button" id="confirmDeletionButton" class="btn btn-danger">Confirm</button>
            <button type="button" id="cancelDeletionButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
</body>