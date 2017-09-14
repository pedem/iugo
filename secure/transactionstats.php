<?php
require("transactionMgr.php");

$postData = json_decode(file_get_contents('php://input'), true);

$transMgr = new TransactionManager();
$transMgr->getStatsFromPost($postData);
?>