<?php
require("userdataMgr.php");

$postData = json_decode(file_get_contents('php://input'), true);

$mgr = new UserDataManager;
$mgr->saveFromPost($postData);

?>