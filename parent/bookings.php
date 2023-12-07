<?php
include_once('../utils.php');
//check if the user is logged in
include_once('./putils.php');
//parent view bookings for the current child
echo get_next_event_of_child(25012);
?>