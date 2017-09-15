<?php
require("managers/leaderboardMgr.php");

$postData = json_decode(file_get_contents('php://input'), true);

$mgr = new LeaderBoardManager;
$mgr->getRankingsFromPost($postData);

?>