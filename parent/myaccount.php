<?php
require_once '../utils.php';
if (!is_logged_in()) {
    header("Location: ../login.php");
    exit();
}
if (!is_parent()) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["phone"])) {
    //write a file to make sure it works
    //file_put_contents("test.txt", $_POST["name"] . " " . $_POST["email"] . " " . $_POST["phone"]);
    update_accountDetails($_SESSION['user'], $_POST["name"], $_POST["email"], $_POST["phone"]);
    //return 200 code
    header("HTTP/1.1 200 OK");
    die();
}
//get the user id from the session
$user_id = $_SESSION['user'];
$account = new Account($user_id);
$account->update();
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <title>Admin</title>
    <script>
        //when the document is ready
        $(document).ready(function() {
            //when the form is submitted
            $("form").submit(function(e) {
                //prevent the default action
                e.preventDefault();
                //get the form data
                var formData = {
                    name: $("#name").val(),
                    email: $("#email").val(),
                    phone: $("#phone").val()
                };
                //send the post request
                $.ajax({
                    type: "POST",
                    url: "myaccount.php",
                    data: formData,
                    success: function(data) {
                        //reload the page
                        location.reload();
                    },
                    error: function(data) {
                        //log the error
                        console.log(data);
                    }
                });
            });
        });
    </script>
</head>
<br>

<body>
    <?php require_once '../parent/nav.php'; ?>
    <div class="container">
        <!-- display update form filled with all information on file, which is  currently name email and phone number -->
        <div class="card-body card bg-light d-flex">
            <form>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" value="<?php echo $account->get_name(); ?>" id="name">
                </div>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="text" class="form-control" id="email" value="<?php echo $account->get_email(); ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" class="form-control" id="phone" value="<?php echo $account->get_phone(); ?>">
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>