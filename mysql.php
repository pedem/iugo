<?php
$servername = "localhost";
$username = "mpede_iugo";
$password = "notsecret";

try {
    $db = new PDO("mysql:host=$servername;dbname=iugo", $username, $password);
    // set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully"; 
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }


try {
    // First of all, let's begin a transaction
    $db->beginTransaction();

    // A set of queries; if one fails, an exception should be thrown
    $db->query('first query');
    $db->query('second query');
    $db->query('third query');

    // If we arrive here, it means that no exception was thrown
    // i.e. no query has failed, and we can commit the transaction
    $db->commit();
} catch (Exception $e) {
	echo "Query failed: " . $e->getMessage();
    // An exception has been thrown
    // We must rollback the transaction
    $db->rollback();
}
?>