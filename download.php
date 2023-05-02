<?php
include 'resources/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    die();
}
$serverInfo = getServerInfo($_POST["chooseServer"]);
if (!empty($_POST["downloadClient"]))
    downloadVPNClient($serverInfo["server_ID"],$_POST["downloadClient"],getAPIKey($serverInfo["server_ID"]));

