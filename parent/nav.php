<?php
//pages needed, classes, my account, bookings, logout
//the nav bar also needs a home button that links to /parent/index.php
//this nav bar is special because it will have a separate dropdown to the left of all the links where
//the parent selects the child they want to view
$_SESSION['child'] = isset($_GET['child']) ? intval($_GET['child']) : null;

?>

<head>
    <script>
        //this function will be called when the user selects a child from the dropdown, it simply redirects the user to the same page but with the child id in the url

        $(document).ready(function() {
            //this function will be called when the user selects a child from the dropdown, it simply redirects the user to the same page but with the child id in the url
            $('#StudentSel').select2({
                theme: "bootstrap-5",
                minimumResultsForSearch: -1
            }).on('select2:select', function(e) {
                var data = e.params.data;
                if (data.id == -1) {
                    return;
                }
                window.location.href = "/parent/index.php?child=" + data.id;
            });
            //remove search bar from StudentSel

        });
        //test jquery is working with ale
    </script>
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <!-- left side of navbar -->
        <div class="navbar-brand">
            <a class="nav-link" href="/parent/index.php">Home</a>
        </div>
        <div>
            <form class="d-flex">
                <select class="form-select" id="StudentSel">
                    <?php
                    $parent_id = intval($_SESSION['parent']);
                    $children = get_parent_students($parent_id);
                    if (isset($_SESSION['child']) && in_array($_SESSION['child'], $children)) {
                        echo "<option value=-1>Select Child</option>";
                    } else {
                        echo "<option selected value=-1>Select Child</option>";
                    }
                    ?>
                    <?php
                    //get the children of the parent
                    //loop through the children and display them as options
                    foreach ($children as $child) {
                        if (isset($_SESSION['child']) && $_SESSION['child'] == $child) {
                            echo "<option selected value=", strval($child), ">", get_student_name(intval($child)), "</option>";
                        } else {
                            echo "<option value=", strval($child), ">", get_student_name(intval($child)), "</option>";
                        }
                    }
                    ?>
                </select>
            </form>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAlt" aria-label="toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
        <div class="collapse navbar-collapse" id="navbarNavAlt">
            <!-- right side of navbar -->
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href='/parent/classes.php<?php
                                                                if (isset($_SESSION['child'])) {
                                                                    $childid = intval($_SESSION['child']);
                                                                    echo ("?child=" . $childid . "");
                                                                } ?>
                '>Classes</a>
                <a class="nav-link" href="/parent/myaccount.php">My Account</a>
                <a class="nav-link" href="/parent/bookings.php<?php
                                                                if (isset($_SESSION['child'])) {
                                                                    $childid = intval($_SESSION['child']);
                                                                    echo ("?child=" . $childid . "");
                                                                } ?>">Bookings</a>
                <?php
                if (boolval(is_admin())) {
                    echo '<a class="nav-link" href="/admin/index.php">Admin</a>';
                }
                if (boolval(is_teacher())) {
                    echo '<a class="nav-link" href="/teacher/index.php">Teacher</a>';
                }
                ?>
                <a class="nav-link" href="/logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>
<div>
    <br>
    <br>
</div>