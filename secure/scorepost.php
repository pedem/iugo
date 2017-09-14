<?php
require("leaderboardMgr.php");

$ldr = new LeaderBoardManager;
$ldr->insertLeaderEntryFromPost($postData);

?>