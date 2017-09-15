<?php
require("managers/resetMgr.php");

$postData = json_decode( stripslashes(file_get_contents('php://input')), true);  // Needs StripSlashes to get around SQL Injection

$mgr = new ResetManager($postData);

?>