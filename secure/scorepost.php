<?php
require("leaderboardMgr.php");

$postData = json_decode(file_get_contents('php://input'), true);

$ldr = new LeaderBoardManager;
$ldr->insertLeaderEntryFromPost($postData);

?>