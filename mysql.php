<?php
class Datastore
{
	private const SERVERNAME 	= "localhost";
	private const USERNAME 		= "mpede_iugo";
	private const PASSWORD 		= "notsecret";

	public function getDB()
	{
		try
		{
			$db = new PDO("mysql:host=".self::SERVERNAME.";dbname=iugo", self::USERNAME, self::PASSWORD);
			// set the PDO error mode to exception
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e)
		{
			throw new Exception("Connection failed: " . $e->getMessage());
		}

		return $db;
	}
}

/*
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
/**/
?>