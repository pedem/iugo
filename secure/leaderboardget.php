<?php
require("leaderboardMgr.php");

$ldr = new LeaderBoardManager;
$ldr->getRankingsFromPost($postData);

?>