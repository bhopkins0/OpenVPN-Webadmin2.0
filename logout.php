<?php
include('resources/functions.php');
if (!isLoggedIn()) {
    header("Location: index.php");
    die();
}
logoutButton();