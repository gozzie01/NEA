<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <!-- left side of navbar -->
        <div class="navbar-brand">
            <a class="nav-link" href="/teacher/index.php">Home</a>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAlt"> <span class="navbar-toggler-icon"></span> </button>
        <div class="collapse navbar-collapse" id="navbarNavAlt">
            <!-- right side of navbar -->
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/teacher/classes.php">Classes</a>
                <a class="nav-link" href="/teacher/myaccount.php">My Account</a>
                <a class="nav-link" href="/teacher/bookings.php">Bookings</a>
                <?php
                if (is_admin()){
                    echo '<a class="nav-link" href="/admin/index.php">Admin</a>';
                }
                if (is_parent()){
                    echo '<a class="nav-link" href="/parent/index.php">Parent</a>';
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