<?php
require("mysql.php");

class Transaction
{
	// Protect the secret constant from prying eyes.
	private const SECRET_KEY 		= "NwvprhfBkGuPJnjJp77UPJWJUpgC7mLz";

	private const TRANSID_MISSING 	= "TransactionId Missing";
	private const USERID_MISSING 	= "UserId Missing";
	private const CURRENCY_MISSING	= "CurrencyAmount Missing";

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

	public function getUserStats()
	{
		$ds = new Datastore;
		$db = $ds->getDB();

		// Any Errors such as integrity Constraints being violated will be displayed as errors properly in controller.
		$stmt = $db->query("SELECT COUNT(*), SUM(currencyAmount) FROM transaction WHERE userId=$this->userId");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		echo json_encode($results);
	}
}

class TransactionManager
{
	private const VERIFY_ERROR = "Could Not Verify Transaction";
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
		$trans = $this->processPOST($postData)

		$verifier = $postData['Verifier'];

		$this->verifyTransaction($trans, $verifier);

		$trans->save();

		$this->success();
	}

	public function getStatsFromPost($postData)
	{
		$trans = $this->processPOST($postData)

		$data = $trans->getUserStats();

		echo json_encode( $data );
	}

}

?>