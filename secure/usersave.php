<?php
require("userdataMgr.php");

$postData = json_decode( stripslashes(file_get_contents('php://input')), true);  // Needs StripSlashes to get around SQL Injection

$mgr = new UserDataManager;
$mgr->saveFromPost($postData);

?>