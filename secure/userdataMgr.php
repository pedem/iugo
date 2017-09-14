<?php
require("mysql.php");

class UserData
{
	public const USERID_MISSING 	= "UserId Missing or not Numeric";
	public const DATA_MISSING	= "Data Missing";

	// Internal private variables
	private $userId;
	private $data;

	// Create a new Transaction, with validation checks.
	public function __construct($userId, $data)
	{
		if (is_null($userId) || !is_int($userId))
		{
			throw new Exception(self::USERID_MISSING);
		}
		if (is_null($data))
		{
			throw new Exception(self::CURRENCY_MISSING);
		}
		// Set the internal values
		$this->userId = $userId;
		$this->currencyAmount = $currencyAmount;
	}


	// Create an Array representation of this Transaction.
	public function toArray()
	{
		return array(
			"UserId" => $this->userId,
			"Data" => json_decode($this->data)
		);
	}

	// Save this Transaction.
	public function save()
	{
		$ds = new Datastore;
		$db = $ds->getDB();

		// Any Errors such as integrity Constraints being violated will be displayed as errors properly in controller.
		$db->query("INSERT into userdata (userId,data) VALUES ($this->userId,$this->data)");
	}

	public static function load($db, $userId)
	{
		if (is_null($userId) || !is_int($userId))
		{
			throw new Exception(self::USERID_MISSING);
		}

		$stmt = $db->query("SELECT data FROM userdata WHERE userId=$userId");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (count($results)!=1)
		{
			return null;
		}

		$row = $results[0];

		return new LeaderBoard($userId, $leaderboardId, (int)$row['data']);
	}

	public function updateData($data)
	{
		$this->data = array_merge_recursive($this->data, $data);

		$this->save();
	}

}

class UserDataManager
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

	// Displays Success to the user
	public function success()
	{
		echo json_encode( array("Success"=> true ) );
	}

	// Converts POST data to a transaction
	public function saveFromPost($postData)
	{
		if (is_null($postData['Data']))
		{
			$this->success();
			return;
		}
		$ds = new Datastore;
		$db = $ds->getDB();

		$userData = UserData::load($db, $postData['UserId']);
		if (is_null($userData))
		{
			$userData = new UserData($postData['UserId'], $postData['Data']);
			$userData->save();
		}
		else
		{
			$userData->updateData($postData['Data']);
		}

		$this->success();
	}

}

?>