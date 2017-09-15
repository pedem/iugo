<?php
require("secure/db/mysql.php");
require("secure/entities/transaction.php");

class TransactionManager
{
	private const VERIFY_ERROR		= "Could Not Verify Transaction";
	private const VERIFIER_MISSING	= "Verifier Missing." . self::VERIFY_ERROR;
	private const NO_USER_RESULTS	= "No Results for UserID";

	/*
		I've made this function to create a Transaction. 
		It's simple, but if there becomes anything we wish to do upon making a Transaction, the logic will go here.
	*/
	public function createTransaction($transId, $userId, $currencyAmount )
	{
		$trans = new Transaction($transId, $userId, $currencyAmount);
		return $trans;
	}

	// Function to verify a transaction against a verification string.
	public function verifyTransaction($trans, $verifyStr)
	{
		if ($trans->toVerifierStr()!=$verifyStr)
		{
			throw new Exception(self::VERIFY_ERROR);
		}
	}

	// Converts POST data to a transaction
	public function processPOSTtoTrans($postData)
	{
		return $this->createTransaction($postData['TransactionId'],$postData['UserId'],$postData['CurrencyAmount']);
	}

	// Displays Success to the user
	public function success()
	{
		echo json_encode( array("Success"=> true ) );
	}
	
	// Records a transaction from POST data.
	// Essentially #3 entry point for Class
	public function recordTransactionFromPost($postData)
	{
		$trans = $this->processPOSTtoTrans($postData);

		$verifier = $postData['Verifier'];

		if (is_null($verifier))
		{
			throw new Exception(self::VERIFIER_MISSING);
		}

		$ds = new Datastore;
		$db = $ds->getDB();

		$this->verifyTransaction($trans, $verifier);

		$trans->save($db);

		$this->success();
	}

	// Returns an array of Stats for a given UserId.  If the UserId is not valid, I assume they want the stats to be 0.
	public function getUserStats($userId)
	{
		if (is_null($userId) || !is_int($userId))
		{
			throw new Exception(Transaction::USERID_MISSING);
		}

		$ds = new Datastore;
		$db = $ds->getDB();

		// Any Errors such as integrity Constraints being violated will be displayed as errors properly in controller.
		$stmt = $db->query("SELECT COUNT(*) as cnt, SUM(currencyAmount) as all_sum FROM transaction WHERE userId=$userId");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// In the form:
		// [{"COUNT(*)":"1","SUM(currencyAmount)":"3"}]

		$result = $results[0];

		$count = (int)$result["cnt"];
		$sum = (double)$result["all_sum"];

		// If There's no count, should I throw an error?  I'm going to assume No here, but otherwise, uncomment this block
		/*
		if ($count==0)
		{
			throw new Exception(self::NO_USER_RESULTS);
		}
		/**/

		return array("UserId"=>$userId, "TransactionCount"=>$count, "CurrencySum"=>$sum);
	}


	// Display Stats for a User identified in the POST data.
	// Essentially #4 entry point for Class
	public function getStatsFromPost($postData)
	{
		$userId = $postData["UserId"];

		if (is_null($userId) || !is_int($userId))
		{
			throw new Exception(Transaction::USERID_MISSING);
		}

		$data = $this->getUserStats($userId);

		echo json_encode( $data );
	}

}

?>