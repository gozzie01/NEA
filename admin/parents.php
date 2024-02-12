<?php
require_once '../utils.php';
//check if the user is logged in
require_once './autils.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gettabledata'])) {
    $parents = get_all_parents();
    foreach ($parents as $Parent) {
        echo "<tr id=ParentRow", $Parent->getID(), ">";
        echo "<td>", $Parent->getID(), "</td>";
        echo "<td>", $Parent->getName(), "</td>";
        echo "<td>", $Parent->getAccount(), "</td>";
        echo "<td>", count($Parent->getStudents()), "</td>";
        echo "<td><button type='button' class='btn btn-danger' id='delete", $Parent->getID(), "'>Delete</button></td>";
        echo "</tr>";
    }
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getAccountSelector'])) {
    $accounts = get_all_accounts();
    foreach ($accounts as $account) {
        echo "<option value=", $account->getID(), ">", $account->getName(), "</option>";
    }
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getStudentSelector'])) {
    $students = get_all_students();
    foreach ($students as $student) {
        echo "<option value=", $student->getId(), ">", $student->getName(), "</option>";
    }
    die();
}
//get the Parent id from the request
//if its a post get the post id if not try to get it from get
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getParentID'])) {
    $id = $_POST['id'];
    //get the Parent object from the database
    $Parent = new Parent_($id);
    $Parent->update();
    $Parent_id = $Parent->getID();
    $Parent_name = $Parent->getName();
    $Parent_account = $Parent->getAccount();
    $Parent_students = $Parent->getStudents();
    //format the json response
    $response = array(
        "id" => $Parent_id,
        "name" => $Parent_name,
        "account" => $Parent_account,
        "students" => $Parent_students
    );
    echo json_encode($response);
    die();
}

//if the request is a post and the id is set, check if the Parent exists, if it does update it, if not create it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateParent'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $accountID = $_POST['accountID'];
    $students = $_POST['students'];
    //check if the values passed are valid
    //format the variables properly
    $id = intval($id);
    $name = strval($name);
    //if accountID is empty set it to null
    if ($accountID == "null" || $accountID == "") {
        $accountID = null;
    } else {
        $accountID = intval($accountID);
    }
    //the arrays will be passed as strings so we need to convert them to arrays
    //check if there is a value
    if ($students != "") {
        //if there is a value then convert it to an array
        str_replace(" ", "", $students);
        str_replace("[", "", $students);
        str_replace("]", "", $students);
        $students = explode(",", $students);
    } else {
        $students = null;
    }
    //check if the Parent exists
    if (parent_exists($id)) {
        //update the Parent
        $respon = update_parent($id, $name, $accountID, $students);
    } else {
        //create the parents
        $respon = create_parent($id, $name, $accountID, $students);
    }
    if ($respon) {
        $response = array(
            "success" => "Parent updated successfully"
        );
        echo json_encode($response);
        die();
    } else {
        $response = array(
            "error" => "Parent does not exist"
        );
        echo json_encode($response);
        die();
    }
}
//if the request is a post and the id is set, check if the Parent exists, if it does delete it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteParent'])) {
    $id = $_POST['id'];
    //check if the Parent exists
    if (parent_exists($id)) {
        //delete the Parent
        $respon = delete_parent($id);
    } else {
        $response = array(
            "error" => "Parent does not exist"
        );
        echo json_encode($response);
        die();
    }
    if ($respon) {
        $response = array(
            "success" => "Parent deleted successfully"
        );
        echo json_encode($response);
        die();
    } else {
        $response = array(
            "error" => "Parent does not exist"
        );
        echo json_encode($response);
        die();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    die();
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
            //add the options to account selector and student selector
            $.ajax({
                type: "POST",
                url: "/admin/parents.php",
                data: {
                    getAccountSelector: true
                },
                success: function(data) {
                    $('#AccountSelector').html(data);
                }
            });
            $.ajax({
                type: "POST",
                url: "/admin/parents.php",
                getStudentSelector: true,
                data: {},
                success: function(data) {
                    $('#StudentSelector').html(data);
                }
            });
            //make the multiselects searchable and 
            $('#StudentSelector').select2({
                theme: "bootstrap-5",
                placeholder: "Select Students",
                width: "100%",
                allowClear: true,
                multiple: true
            });
            $('#AccountSelector').select2({
                theme: "bootstrap-5",
                placeholder: "Select Account",
                width: "100%",
                allowClear: true,
                multiple: false
            });
        });

        function updateTable() {
            $.ajax({
                type: "POST",
                url: "/admin/parents.php",
                data: {
                    gettabledata: true
                },
                success: function(data) {
                    $('#mainbody').html(data);
                }
            });
        }
        //when a Parent is selected from the table, update the edit form to show the Parent's details
        $(document).on('click', 'tr', function() {
            if ($(this).find("th").length > 0) {
                return;
            }
            //get the Parent id from the row
            var ParentID = $(this).find("td:first").html();
            //print the id to the console
            console.log(ParentID);
            $(this).addClass('table-danger').siblings().removeClass('table-danger');
            //get the Parent object from the database
            $.ajax({
                type: "POST",
                url: "/admin/parents.php",
                data: {
                    getParentID: true,
                    id: ParentID
                },
                success: function(data) {
                    //parse the json response
                    var Parent = JSON.parse(data);
                    //set the values of the edit form to the Parent's details
                    $('#ParentID').val(Parent.id);
                    $('#ParentName').val(Parent.name);
                    $('#AccountSelector').val(Parent.account);
                    $('#StudentSelector').val(Parent.students);
                    //refresh the selects
                    $('#AccountSelector').trigger('change');
                    $('#StudentSelector').trigger('change');
                    //change the text in the submit button to update
                    $('#submitFormButton').text("Update");
                }
            });
        });
        //clear button to stop selecting a Parent
        $(document).on('click', '#clear', function() {
            //remove the selected class from the table
            $('tr').removeClass('table-danger');
            //clear the edit form
            $('#ParentID').val("");
            $('#ParentName').val("");
            $('#AccountSelector').val("");
            $('#StudentSelector').val("");
            //refresh the selects
            $('#AccountSelector').trigger('change');
            $('#StudentSelector').trigger('change');
            //change the text in the submit button to add
            $('#submitFormButton').text("Add");
        });
        //on ready clear the edit form
        $(document).ready(function() {
            $('#clear').click();
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            $('.table-scroll tbody').css('height', height);
            updateTable();
        });
        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            //just tbody
            $('.table-scroll tbody').css('height', height);
        });
        //on submit do nothing, let the button press function handle it
        $(document).on('submit', '#ParentUpdateForm', function(e) {
            e.preventDefault();
        });
        $(document).on('click', '#submitFormButton', function() {
            //if the button says add
            if ($(this).text() == "Add") {
                //submit the form using xhttprequest
                $id = $('#ParentID').val();
                $name = $('#ParentName').val();
                $accountID = $('#AccountSelector').val();
                $students = $('#StudentSelector').val();
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
                xhttp.open("POST", "/admin/parents.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("updateParent=true" + "&id=" + $id + "&name=" + $name + "&accountID=" + $accountID + "&students=" + $students);
            } else {
                //if the button says update
                //show the confirmation popup
                $('#confirm').show();
                //get the Parent id from the form
                var ParentID = $('#ParentID').val();
                //print the id to the console
                console.log(ParentID);
                //get the Parent object from the database
                $.ajax({
                    type: "POST",
                    url: "/admin/parents.php",
                    data: {
                        getParentID: true,
                        id: ParentID
                    },
                    success: function(data) {
                        //parse the json response
                        var Parent = JSON.parse(data);
                        //set the text of the confirmation popup
                        $('#confirmText').text("Are you sure you want to update " + Parent.name + "?");
                    }
                });
            }
        });
        //on click of the confirm button
        $(document).on('click', '#confirmButton', function() {
            //submit the form
            $id = $('#ParentID').val();
            $name = $('#ParentName').val();
            $accountID = $('#AccountSelector').val();
            $students = $('#StudentSelector').val();
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
                                updateTable();
                            }
                        }
                    } else {
                        //if the request was not successful then we can display an error message
                        alert(this.responseText);
                    }
                }
            };
            xhttp.open("POST", "/admin/parents.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("updateParent=true" + "&id=" + $id + "&name=" + $name + "&accountID=" + $accountID + "&students=" + $students);
        });
        //on click of the cancel button
        $(document).on('click', '#cancelButton', function() {
            //hide the confirmation popup
            $('#confirm').hide();
        });
        $(document).on('click', 'button[id^="delete"]', function() {
            //get the teacher id from the button id
            var ParentID = $(this).attr('id').replace('delete', '');
            //print the id to the console
            console.log(ParentID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/parents.php",
                data: {
                    getParentID: true,
                    id: ParentID
                },
                success: function(data) {
                    //parse the json response
                    var Parent = JSON.parse(data);
                    //set the text of the confirmation popup
                    $('#confirmDeletionText').text("Are you sure you want to delete " + Parent.name + "?");
                }
            });
            //show the confirmation popup
            $('#confirmDeletion').show();
        });
        //on click of the confirm button
        $(document).on('click', '#confirmDeletionButton', function() {
            //get the teacher id from the form
            var ParentID = $('#ParentID').val();
            //print the id to the console
            console.log(ParentID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/parents.php",
                data: {
                    deleteParent: true,
                    id: ParentID
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

        .ParentForm input {
            margin: 2px;
        }
    </style>
</head>
<?php include_once '../admin/nav.php'; ?>
<!--searchable table of parents-->

<body>
    <div style="display: flex; margin-top: 2pt;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>parents</h1>
                    <p>Here you can view all the parents in the school</p>
                    <input class="form-control" id="myInput" type="text" onkeyup="searchupdate()" placeholder="Search..">
                    <div class="well">
                        <table class="table table-striped table-scroll table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Parent ID</th>
                                    <th scope="col">Parent Name</th>
                                    <th scope="col">Account ID</th>
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
        </div>
        <!--add a edit page on the right, non-functional for now-->
        <div class="container" style="max-width: 25%;">
            <div class="col">
                <h1>Edit</h1>
                <form id="ParentUpdateForm" class="ParentForm">
                    <!--Parent id, name, yeargroup, select multiclasses from a list of all classes, select parents from a list of multi parents-->
                    <input type="text" class="form-control" placeholder="Parent ID" id="ParentID">
                    <input type="text" class="form-control" placeholder="Parent Name" id="ParentName">
                    <select class="form-select" id="AccountSelector">

                    </select>
                    <select class="form-select" id="StudentSelector">

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
            <p id="confirmText">Are you sure you want to update this Parent?</p>
            <button type="button" id="confirmButton" class="btn btn-primary">Confirm</button>
            <button type="button" id="cancelButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
    <div id="confirmDeletion" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
        <!--confirmation box-->
        <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
            <p id="confirmDeletionText">Are you sure you want to delete this Parent?</p>
            <button type="button" id="confirmDeletionButton" class="btn btn-danger">Confirm</button>
            <button type="button" id="cancelDeletionButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
</body>