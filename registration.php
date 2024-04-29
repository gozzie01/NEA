<?php
require_once 'utils.php';
//check if the user is logged in
if (is_logged_in()) {
    header("Location: index.php");
    exit();
}
$Emailfromtoken=get_email_from_token($_GET['token']);
//if the form has been submitted then we can try to log the user in
if (isset($_POST['password']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_POST['ResetToken'])) {
    //get the email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];
    $token = $_POST['ResetToken'];
    //validate the phone number
    $phone = $_POST['phone'];
    if (strlen($phone) < 11) {
        header('HTTP/1.1 401 Unauthorized');
        echo "Phone number too short";
        exit();
    } else if (strlen($phone) > 14) {
        header('HTTP/1.1 401 Unauthorized');
        echo "Phone number too long";
        exit();
        //start with +44
    } else if (substr($phone, 0, 3) != "+44") {
        header('HTTP/1.1 401 Unauthorized');
        echo "Phone number must start with +44";
        exit();
    }
    //strip + and try to int
    else if (is_nan(intval(substr($phone, 1)))) {
        header('HTTP/1.1 401 Unauthorized');
        echo "Phone number invalid";
        exit();
    }
    //password hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    //get the userId from email
    $userID = -1;
    $name = "";
    $sql = "SELECT ID, Name FROM User WHERE Email=? AND ResetToken=? LIMIT 1";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->bind_result($userID, $name);
    $stmt->fetch();
    $stmt->close();
    //check if the user exists
    if ($userID == -1) {
        header('HTTP/1.1 401 Unauthorized');
        echo "Incorrect token or email";
        exit();
    }
    //update the user
    $sql = "UPDATE User SET Password=?, Phone=?, ResetToken=NULL WHERE ID=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("ssi", $hashed_password, $phone, $userID);
    $stmt->execute();
    $stmt->close();
    sendEmail($email, "Password Changed", generate_password_change($name), true);
    //log the user in
    //redirect to login page
    header("Location: login.php");
    exit();
}

if (isset($_GET['token']) && is_token_valid($_GET['token'])) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Register</title>
        <?php require_once 'includes.php'; ?>
        <script>
            const searchParams = new URLSearchParams(window.location.search);
            //when the document is ready
            $(document).ready(function() {
                //when the register form is submitted
                $("#registerForm").submit(function(event) {
                    //prevent the default behaviour
                    event.preventDefault();
                    //get the email and password from the form
                    var email = $("#email").val();
                    var password = $("#password").val();
                    //check if the email is valid
                    if (!email.includes("@")) {
                        //if the email is not valid then we can display an error message
                        $("#email").addClass("is-invalid");
                        return;
                    }
                    //check if the password is valid
                    if (password.length < 1) {
                        //if the password is not valid then we can display an error message
                        $("#password").addClass("is-invalid");
                        return;
                    }
                    //replace leading 01 and 07 with +44 and add + if start with 44
                    var phone = $("#phone").val();
                    if (phone.startsWith("44")) {
                        phone = "+" + phone;
                    } else if (phone.startsWith("07")) {
                        phone = "+44" + phone.substring(1);
                    } else if (phone.startsWith("01")) {
                        phone = "+44" + phone.substring(1);
                    }
                    //if the phone number is not valid then we can display an error message
                    if (phone.length < 11) {
                        $("#phone").addClass("is-invalid");
                        return;
                    } else if (phone.length > 14) {
                        $("#phone").addClass("is-invalid");
                        return;
                    } //strip + and try to int
                    else if (isNaN(parseInt(phone.replace("+", "")))) {
                        $("#phone").addClass("is-invalid");
                        return;
                    }
                    //submit the form
                    $("#registerButton").prop("disabled", true);
                    //just popup box for testing
                    postregisterForm();
                    //refresh the page
                    //location.reload();
                });
            });
            //make a function to post the register form
            function postregisterForm() {
                //get the email and password from the form
                var email = $("#email").val();
                var password = $("#password").val();
                var phone = $("#phone").val();
                //get token from 
                var token = searchParams.get('token');
                if (phone.startsWith("44")) {
                    phone = "+" + phone;
                } else if (phone.startsWith("07")) {
                    phone = "+44" + phone.substring(1);
                } else if (phone.startsWith("01")) {
                    phone = "+44" + phone.substring(1);
                }

                //create a http request to post the register form
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    //if the request is complete
                    if (this.readyState == 4) {
                        //if the request was successful
                        if (this.status == 200) {
                            //if the request was successful then we can redirect the user to the index page
                            //send the password change email

                            window.location.replace("index.php");
                        } else {
                            //if the request was not successful then we can display an error message
                            alert(this.responseText);
                            $("#registerButton").prop("disabled", false);
                        }
                    }
                };
                xhttp.open("POST", "registration.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                if (searchParams.has('token')) {
                    xhttp.send("email=" + email + "&password=" + password + "&phone=" + phone + "&ResetToken=" + token);
                } else {}
            }
        </script>
    </head>

    <body>
        <!-- The register form, submit using a js function, display any error in a custom message box in the center of the screen -->
        <div class="d-flex justify-content-center align-items-center vh-100">
            <div class="d-flex">
                <div class="card card-body bg-light" style="max-width: 600px; width:auto; ">
                    <h1 class="text-center">Register</h1>
                    <br>
                    <form id="registerForm" class="mb-6" action="registration.php" method="post">
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input id="email" name="email" type="email" class="form-control" placeholder="<?php echo $Emailfromtoken; ?>" value="<?php echo $Emailfromtoken; ?>" disabled>
                            <small id="emailHelp" class="form-text text-muted mb-2">We'll never share your email with anyone else.</small>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" class="form-control" id="phone">
                            <small id="phoneHelp" class="form-text text-muted mb-2">We'll never share your phone number with anyone else.</small>
                            <div class="invalid-feedback">
                                Please enter a valid phone number.
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password" name="password" type="password" class="form-control" placeholder="Password" required>
                            <div class="invalid-feedback">
                                Please enter a password.
                            </div>
                        </div>
                        <br>
                        <div class="form-group d-flex justify-content-between">
                            <div class="ml-2">
                                <button id="registerButton" type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>

    </html>
<?
} else {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Register</title>
        <?php require_once 'includes.php'; ?>
    </head>

    <body>
        <!-- The register form, submit using a js function, display any error in a custom message box in the center of the screen -->
        <div class="d-flex justify-content-center align-items-center vh-100">
            <div class="d-flex">
                <div class="card card-body bg-light" style="max-width: 600px; width:auto; ">
                    <h1 class="text-center">Link invalid</h1>
                    <h2 class="text-center">please try again in a minute</h2>
                    <br>
                    <br>
                    <br>
                    <h6 class="text-center"> if errors persist contact support at admin@samgosden.tech
                        <?php if (isset($_GET['token'])) {
                            $token = (string)($_GET['token']);
                            echo $token;
                        } ?></h6>
                </div>
            </div>
        </div>
    </body>

    </html>
<?php
}
?>