<?php
session_start();
session_destroy();
require_once('../connect.php');
echo '<script>window.location = "' . $root . '";</script>';
?>