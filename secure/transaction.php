<?php
require("managers/transactionMgr.php");

$postData = json_decode(file_get_contents('php://input'), true);

$mgr = new TransactionManager();
$mgr->recordTransactionFromPost($postData);
?>