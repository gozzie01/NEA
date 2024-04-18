<?php
require_once '../utils.php';
//check if the user is logged in
require_once './autils.php';

//get the Account id from the request
//if its a post get the post id if not try to get it from get
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getAccountID'])) {
    $id = $_POST['id'];
    //get the Account object from the database
    $Account = new Account($id);
    $Account->update();
    $Account_id = $Account->getID();
    $Account_name = $Account->getName();
    $Account_Email = $Account->getEmail();
    $Account_Phone = $Account->getPhone();
    $Account_ParentID = $Account->getParentID();
    $Account_TeacherID = $Account->getTeacherID();
    //format the json response
    $response = array(
        "id" => $Account_id,
        "name" => $Account_name,
        "email" => $Account_Email,
        "phone" => $Account_Phone,
        "parentid" => $Account_ParentID,
        "teacherid" => $Account_TeacherID

    );
    echo json_encode($response);
    die();
}
if ($_SERVER['REQUEST_METHOD'] && isset($_POST['gettabledata'])) {
    $Accounts = get_all_accounts();
    foreach ($Accounts as $Account) {
        echo "<tr id=AccountRow", $Account->getID(), ">";
        echo "<td>", $Account->getID(), "</td>";
        echo "<td>", $Account->getName(), "</td>";
        echo "<td>", $Account->getEmail(), "</td>";
        echo "<td></td>";
        echo "<td>", $Account->getPhone(), "</td>";
        echo "<td>", $Account->getParentID(), "</td>";
        echo "<td>", $Account->getTeacherID(), "</td>";
        echo "<td><button type='button' class='btn btn-danger' id='delete", $Account->getID(), "'>Delete</button></td>";
        echo "</tr>";
    }
    die();
}
//get parent selector
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getparentselector'])) {
    $Accounts = get_all_parents();
    foreach ($Accounts as $Account) {
        echo "<option value='", $Account->getID(), "'>", $Account->getName(), "</option>";
    }
    die();
}
//get teacher selector
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getteacherselector'])) {
    $Accounts = get_all_teachers();
    foreach ($Accounts as $Account) {
        echo "<option value='", $Account->getID(), "'>", $Account->getName(), "</option>";
    }
    die();
}
//if the request is a post and the id is set, check if the Account exists, if it does update it, if not create it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateAccount'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $parentid = $_POST['parentid'];
    $teacherid = $_POST['teacherid'];

    //check if the values passed are valid
    //format the variables properly
    $id = intval($id);
    $name = strval($name);
    //if the name is not set or is empty return an error
    if ($name == "null" || $name == "") {
        $response = array(
            "error" => "Name is required"
        );
        echo json_encode($response);
        die();
    }
    //if accountID is empty set it to null
    if ($email == "null" || $email == "") {
        //require it
        $response = array(
            "error" => "Email is required"
        );
        echo json_encode($response);
        die();
    } else {
        $email = strval($email);
    }
    //check phone
    if ($phone == "null" || $phone == "") {
        $phone = null;
    } else {
        $phone = strval($phone);
    }
    //check parentid
    if ($parentid == "null" || $parentid == "") {
        $parentid = null;
    } else {
        $parentid = intval($parentid);
    }
    //check teacherid
    if ($teacherid == "null" || $teacherid == "") {
        $teacherid = null;
    } else {
        $teacherid = intval($teacherid);
    }
    //check if the Account exists
    if (account_exists($id)) {
        //update the Account
        $respon = update_account($id, $name, $email, $phone, $parentid, $teacherid);
    } else {
        //create the Accounts   
        $respon = create_account($id, $name, $email, $phone, $parentid, $teacherid);
    }
    if ($respon) {
        $response = array(
            "success" => "Account updated successfully"
        );
        echo json_encode($response);
        die();
    } else {
        $response = array(
            "error" => "Account does not exist"
        );
        echo json_encode($response);
        die();
    }
}
//if the request is a post and the id is set, check if the Account exists, if it does delete it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteAccount'])) {
    $id = $_POST['id'];
    //check if the Account exists
    if (account_exists($id)) {
        //delete the Account
        $respon = delete_account($id);
    } else {
        $response = array(
            "error" => "Account does not exist"
        );
        echo json_encode($response);
        die();
    }
    if ($respon) {
        $response = array(
            "success" => "Account deleted successfully"
        );
        echo json_encode($response);
        die();
    } else {
        $response = array(
            "error" => "Account does not exist"
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
                url: "/admin/accounts.php",
                data: {
                    gettabledata: true
                },
                success: function(data) {
                    //parse the json response
                    $('#mainbody').html(data);
                }
            });
        }
        //on document load
        $(document).ready(function() {
            //make the multiselects searchable and 
            $('#ParentSelector').select2({
                theme: "bootstrap-5",
                placeholder: "Select a Parent",
                allowClear: true
            });
            $('#TeacherSelector').select2({
                theme: "bootstrap-5",
                placeholder: "Select a Teacher",
                allowClear: true
            });
            updateTable();
        });
        //when a Account is selected from the table, update the edit form to show the Account's details
        $(document).on('click', 'tr', function() {
            if ($(this).find("th").length > 0) {
                return;
            }
            //get the Account id from the row
            var AccountID = $(this).find("td:first").html();
            //print the id to the console
            console.log(AccountID);
            $(this).addClass('table-danger').siblings().removeClass('table-danger');
            //get the Account object from the database
            $.ajax({
                type: "POST",
                url: "/admin/accounts.php",
                data: {
                    getAccountID: true,
                    id: AccountID
                },
                success: function(data) {
                    //parse the json response
                    var Account = JSON.parse(data);
                    //set the values of the edit form to the Account's details
                    $('#AccountID').val(Account.id);
                    $('#AccountName').val(Account.name);
                    $('#AccountEmail').val(Account.email);
                    $('#AccountPhone').val(Account.phone);
                    $('#ParentSelector').val(Account.parentid);
                    $('#TeacherSelector').val(Account.teacherid);
                    //refresh the selects
                    $('#ParentSelector').trigger('change');
                    $('#TeacherSelector').trigger('change');
                    //change the text in the submit button to update
                    $('#submitFormButton').text("Update");
                }
            });
        });
        //clear button to stop selecting a Account
        $(document).on('click', '#clear', function() {
            //remove the selected class from the table
            $('tr').removeClass('table-danger');
            //clear the edit form
            $('#AccountID').val("");
            $('#AccountName').val("");
            $('#AccountEmail').val("");
            $('#AccountPhone').val("");
            $('#ParentSelector').val("");
            $('#TeacherSelector').val("");
            //change the text in the submit button to add
            $('#ParentSelector').trigger('change');
            $('#TeacherSelector').trigger('change');
            $('#submitFormButton').text("Add");
        });
        //on ready clear the edit form
        $(document).ready(function() {
            $('#clear').click();
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            $('.table-scroll tbody').css('height', height);
        });
        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            //just tbody
            $('.table-scroll tbody').css('height', height);
        });
        //on submit do nothing, let the button press function handle it
        $(document).on('submit', '#AccountUpdateForm', function(e) {
            e.preventDefault();
        });
        $(document).on('click', '#submitFormButton', function() {
            //if the button says add
            if ($(this).text() == "Add") {
                //submit the form using xhttprequest
                $id = $('#AccountID').val();
                $name = $('#AccountName').val();
                $email = $('#AccountEmail').val();
                $phone = $('#AccountPhone').val();
                $parentid = $('#ParentSelector').val();
                $teacherid = $('#TeacherSelector').val();
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
                        }
                    }
                };
                xhttp.open("POST", "/admin/accounts.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("updateAccount=true" + "&id=" + $id + "&name=" + $name + "&email=" + $email + "&phone=" + $phone + "&parentid=" + $parentid + "&teacherid=" + $teacherid);
            } else {
                //if the button says update
                //show the confirmation popup
                $('#confirm').show();
                //get the Account id from the form
                var AccountID = $('#AccountID').val();
                //print the id to the console
                console.log(AccountID);
                //get the Account object from the database
                $.ajax({
                    type: "POST",
                    url: "/admin/accounts.php",
                    data: {
                        getAccountID: true,
                        id: AccountID
                    },
                    success: function(data) {
                        //parse the json response
                        var Account = JSON.parse(data);
                        //set the text of the confirmation popup
                        $('#confirmText').text("Are you sure you want to update " + Account.name + "?");
                    }
                });
            }
        });
        //on click of the confirm button
        $(document).on('click', '#confirmButton', function() {
            //submit the form
            $id = $('#AccountID').val();
            $name = $('#AccountName').val();
            $email = $('#AccountEmail').val();
            $phone = $('#AccountPhone').val();
            $parentid = $('#ParentSelector').val();
            $teacherid = $('#TeacherSelector').val();
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
            xhttp.open("POST", "/admin/accounts.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("updateAccount=true" + "&id=" + $id + "&name=" + $name + "&email=" + $email + "&phone=" + $phone + "&parentid=" + $parentid + "&teacherid=" + $teacherid);
        });
        //on click of the cancel button
        $(document).on('click', '#cancelButton', function() {
            //hide the confirmation popup
            $('#confirm').hide();
        });
        $(document).on('click', 'button[id^="delete"]', function() {
            //get the teacher id from the button id
            var AccountID = $(this).attr('id').replace('delete', '');
            //print the id to the console
            console.log(AccountID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/accounts.php",
                data: {
                    getAccountID: true,
                    id: AccountID
                },
                success: function(data) {
                    //parse the json response
                    var Account = JSON.parse(data);
                    //set the text of the confirmation popup
                    $('#confirmDeletionText').text("Are you sure you want to delete " + Account.name + "?");
                }
            });
            //show the confirmation popup
            $('#confirmDeletion').show();
        });
        //on click of the confirm button
        $(document).on('click', '#confirmDeletionButton', function() {
            //get the teacher id from the form
            var AccountID = $('#AccountID').val();
            //print the id to the console
            console.log(AccountID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/accounts.php",
                data: {
                    deleteAccount: true,
                    id: AccountID
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
    <style>
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

        .AccountForm input {
            margin: 2px;
        }
    </style>
</head>
<?php include_once '../admin/nav.php'; ?>
<!--searchable table of Accounts-->

<body>
    <div style="display: flex; margin-top: 2pt;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>Accounts</h1>
                    <p>Here you can view all the Accounts in the school</p>
                    <input class="form-control" id="myInput" type="text" onkeyup="searchupdate()" placeholder="Search..">
                    <div class="well">
                        <table class="table table-striped table-scroll table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Account ID</th>
                                    <th scope="col">Account Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col"></th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Parent ID</th>
                                    <th scope="col">Teacher ID</th>
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
                <form id="AccountUpdateForm" class="AccountForm">
                    <!--Account id, name, yeargroup, select multiclasses from a list of all classes, select Accounts from a list of multi Accounts-->
                    <input type="text" class="form-control" placeholder="Account ID" id="AccountID">
                    <input type="text" class="form-control" placeholder="Account Name" id="AccountName">
                    <input type="text" class="form-control" placeholder="Email" id="AccountEmail">
                    <input type="text" class="form-control" placeholder="Phone" id="AccountPhone">
                    <select class="form-control" id="ParentSelector">

                    </select>
                    <select class="form-control" id="TeacherSelector">

                    </select>
                    <!--submit button-->
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
            <p id="confirmText">Are you sure you want to update this Account?</p>
            <button type="button" id="confirmButton" class="btn btn-primary">Confirm</button>
            <button type="button" id="cancelButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
    <div id="confirmDeletion" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
        <!--confirmation box-->
        <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
            <p id="confirmDeletionText">Are you sure you want to delete this Account?</p>
            <button type="button" id="confirmDeletionButton" class="btn btn-danger">Confirm</button>
            <button type="button" id="cancelDeletionButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
</body>
<!--7704-->
