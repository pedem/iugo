<?php
require("mysql.php");

class Transaction
{
	// Protect the secret constant from prying eyes.
	private const SECRET_KEY 		= "NwvprhfBkGuPJnjJp77UPJWJUpgC7mLz";

	public const TRANSID_MISSING 	= "TransactionId Missing";
	public const USERID_MISSING 	= "UserId Missing";
	public const CURRENCY_MISSING	= "CurrencyAmount Missing";

	// Internal private variables
	private $transId;
	private $userId;
	private $currencyAmount;

	// Create a new Transactio, with validation check.
	public function __construct($transId, $userId, $currencyAmount)
	{
		if (is_null($transId))
		{
			throw new Exception(self::TRANSID_MISSING);
		}
		if (is_null($userId))
		{
			throw new Exception(self::USERID_MISSING);
		}
		if (is_null($currencyAmount))
		{
			throw new Exception(self::CURRENCY_MISSING);
		}
		// Set the internal values
		$this->transId = $transId;
		$this->userId = $userId;
		$this->currencyAmount = $currencyAmount;
	}

	public function toVerifierStr()
	{
		return sha1(self::SECRET_KEY . $this->transId . $this->userId .$this->currencyAmount);
	}

	public function toArray()
	{
		return array(
			"TransactionId" => $this->transId,
			"UserId" => $this->userId,
			"CurrencyAmount" => $this->currencyAmount
		);
	}

	public function save()
	{
		$ds = new Datastore;
		$db = $ds->getDB();

		// Any Errors such as integrity Constraints being violated will be displayed as errors properly in controller.
		$db->query("Insert into transaction (transId,userId,currencyAmount) VALUES ($this->transId,$this->userId,$this->currencyAmount)");
	}

}

class TransactionManager
{
	private const VERIFY_ERROR		= "Could Not Verify Transaction";
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

	public function verifyTransaction($trans, $verifyStr)
	{
		if ($trans->toVerifierStr()!=$verifyStr)
		{
			throw new Exception(self::VERIFY_ERROR);
		}
	}

	public function processPOST($postData)
	{
		return $this->createTransaction($postData['TransactionId'],$postData['UserId'],$postData['CurrencyAmount']);
	}

	public function success()
	{
		echo json_encode( array("Success"=> true ) );
	}
	
	public function recordTransactionFromPost($postData)
	{
		$trans = $this->processPOST($postData);

		$verifier = $postData['Verifier'];

		$this->verifyTransaction($trans, $verifier);

		$trans->save();

		$this->success();
	}

	public function getUserStats($userId)
	{
		$ds = new Datastore;
		$db = $ds->getDB();

		// Any Errors such as integrity Constraints being violated will be displayed as errors properly in controller.
		$stmt = $db->query("SELECT COUNT(*), SUM(currencyAmount) FROM transaction WHERE userId=$userId");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// In the form:
		// [{"COUNT(*)":"1","SUM(currencyAmount)":"3"}]

		$count = (int)$result["COUNT(*)"];
		$sum = (double)"SUM(currencyAmount)";

		// If There's no count, should I throw an error?  I'm going to assume No here, but otherwise, uncomment this block
		/*
		if ($count==0)
		{
			throw new Exception(self::NO_USER_RESULTS);
		}
		/**/

		return array("UserId"=>$userId, "TransactionCount"=>$count, "CurrencySum"=>$sum);
	}

	public function getStatsFromPost($postData)
	{
		$userId = $postData["UserId"];

		if (is_null($userId))
		{
			throw new Exception(Transaction::USERID_MISSING);
		}

		$data = $this->getUserStats($userId);

		echo json_encode( $data );
	}

}

?>