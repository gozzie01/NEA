<?php
require_once 'utils.php';
//check if the user is logged in
if (is_logged_in()) {
    header("Location: index.php");
    die();
}
//if the form has been submitted then we can try to log the user in
if (isset($_POST['email']) && isset($_POST['password'])) {
    //get the email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];
    //get the hashed password from the database
    //get the userId from email
    $userID = -1;
    $sql = "SELECT ID FROM User WHERE Email=? LIMIT 1";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($userID);
    $stmt->fetch();
    $stmt->close();

    $hashed_password = "";
    $sql = "SELECT Password FROM User WHERE ID=? LIMIT 1";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();
    //check if the password is correct
    if (password_verify($password, $hashed_password)) {
        //if the password is correct then we can log the user in
        //generate a token for the user
        $token = bin2hex(random_bytes(32));
        //store the token in the database
        $hashed_token = password_hash($token, PASSWORD_DEFAULT);
        $sql = "UPDATE User SET Token=? WHERE ID=?";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("si", $hashed_token, $userID);
        $stmt->execute();
        $stmt->close();
        //store the token in the session
        $_SESSION['token'] = $token;
        $_SESSION['user'] = $userID;
        //get the users details from the database
        $sql = "SELECT Admin FROM User WHERE ID=? LIMIT 1";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->bind_result($admin);
        $stmt->fetch();
        $stmt->close();
        //store the users details in the session
        $_SESSION['admin'] = $admin;
        //redirect the user to the index page
        //see if there is a parent with this user id
        $sql = "SELECT ID FROM Parent WHERE UserID=? LIMIT 1";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->bind_result($parent);
        $stmt->fetch();
        $stmt->close();
        if ($parent != null) {
            $_SESSION['parent'] = $parent;
        }
        //see if there is a teacher with this user id
        $sql = "SELECT ID FROM Teacher WHERE UserID=? LIMIT 1";
        $stmt = $GLOBALS['db']->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->bind_result($teacher);
        $stmt->fetch();
        $stmt->close();
        if ($teacher != null) {
            $_SESSION['teacher'] = $teacher;
        }
        //if they are a teacher see if they are pastoral
        if (isset($_SESSION['teacher'])) {
            $sql = "SELECT Pastoral FROM Teacher WHERE ID=? LIMIT 1";
            $stmt = $GLOBALS['db']->prepare($sql);
            $stmt->bind_param("i", $_SESSION['teacher']);
            $stmt->execute();
            $stmt->bind_result($pastoral);
            $stmt->fetch();
            $stmt->close();
            if ($pastoral == 1) {
                $_SESSION['pastoral'] = true;
            }
        }
        //redirect the user to the index page
        header("HTTP/1.1 200 OK");
        die();
    } else {
        //if the password is incorrect then we can tell the user that the password is incorrect
        //what would be an appropriate error here
        header('HTTP/1.1 401 Unauthorized');
        echo "Incorrect email or password";
        die();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <?php require_once 'includes.php'; ?>
    <script>
        //when the document is ready
        $(document).ready(function() {
            //when the login form is submitted
            $("#loginForm").submit(function(event) {
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
                //submit the form
                $("#loginButton").prop("disabled", true);
                //just popup box for testing
                postLoginForm();
                //refresh the page
                //location.reload();
            });
        });
        //make a function to post the login form
        function postLoginForm() {
            //get the email and password from the form
            var email = $("#email").val();
            var password = $("#password").val();
            //create a http request to post the login form
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                //if the request is complete
                if (this.readyState == 4) {
                    //if the request was successful
                    if (this.status == 200) {
                        //if the request was successful then we can redirect the user to the index page
                        window.location.replace("index.php");
                    } else {
                        //if the request was not successful then we can display an error message
                        alert(this.responseText);
                        $("#loginButton").prop("disabled", false);
                    }
                }
            };
            xhttp.open("POST", "login.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("email=" + email + "&password=" + password);
        }
    </script>
</head>

<body>
    <!-- The login form, submit using a js function, display any error in a custom message box in the center of the screen -->
    <?php
    //see if there is a password in the get request, hash it and display it
    if (isset($_GET['password'])) {
        echo password_hash($_GET['password'], PASSWORD_DEFAULT);
    }
    ?>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="d-flex">
            <div class="card card-body bg-light" style="max-width: 600px; width:auto; ">
                <h1 class="text-center">Login</h1>
                <br>
                <form id="loginForm" class="mb-6" action="login.php" method="post">
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input id="email" name="email" type="email" class="form-control" placeholder="Enter email" required>
                        <small id="emailHelp" class="form-text text-muted mb-2">We'll never share your email with anyone else.</small>
                        <div class="invalid-feedback">
                            Please enter a valid email address.
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
                            <button id="loginButton" type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>