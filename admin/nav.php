<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <!-- left side of navbar -->
        <div class="navbar-brand">
            <a class="nav-link" href="/admin/index.php">Home</a>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAlt" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
        <div class="collapse navbar-collapse" id="navbarNavAlt">
            <!-- right side of navbar -->
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/admin/teachers.php">Teachers</a>
                <a class="nav-link" href="/admin/classes.php">Classes</a>
                <a class="nav-link" href="/admin/students.php">Students</a>
                <a class="nav-link" href="/admin/accounts.php">Accounts</a>
                <a class="nav-link" href="/admin/parents.php">Parents</a>
                <a class="nav-link" href="/admin/events.php">Events</a>
                <?php
                if (is_parent()) {
                ?>
                    <a class="nav-link" href="/parent/index.php">Parent</a>
                <?php
                }
                ?>
                <?php
                if (is_teacher()) {
                ?>
                    <a class="nav-link" href="/teacher/index.php">Teacher</a>
                <?php
                }
                ?>
                <a class="nav-link" href="/admin/myaccount.php">My Account</a>
                <a class="nav-link" href="/logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>
<div>
    <br>
    <br>
</div>