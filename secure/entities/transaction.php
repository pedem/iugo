<?php

class Transaction
{
	// Protect the secret constant from prying eyes.
	private const SECRET_KEY 		= "NwvprhfBkGuPJnjJp77UPJWJUpgC7mLz";

	public const TRANSID_MISSING 	= "TransactionId Missing or not Numeric";
	public const USERID_MISSING 	= "UserId Missing or not Numeric";
	public const CURRENCY_MISSING	= "CurrencyAmount Missing or not Numeric";

	// Internal private variables
	private $transId;
	private $userId;
	private $currencyAmount;

	// Create a new Transaction, with validation checks.
	public function __construct($transId, $userId, $currencyAmount)
	{
		if (is_null($transId) || !is_int($transId))
		{
			throw new Exception(self::TRANSID_MISSING);
		}
		if (is_null($userId) || !is_int($userId))
		{
			throw new Exception(self::USERID_MISSING);
		}
		if (is_null($currencyAmount) || !is_int($currencyAmount))
		{
			throw new Exception(self::CURRENCY_MISSING);
		}
		// Set the internal values
		$this->transId = $transId;
		$this->userId = $userId;
		$this->currencyAmount = $currencyAmount;
	}

	// Create a SHA1 Hashed string that represents this transaction.
	public function toVerifierStr()
	{
		return sha1(self::SECRET_KEY . $this->transId . $this->userId .$this->currencyAmount);
	}

	// Create an Array representation of this Transaction.
	public function toArray()
	{
		return array(
			"TransactionId" => $this->transId,
			"UserId" => $this->userId,
			"CurrencyAmount" => $this->currencyAmount
		);
	}

	// Save this Transaction.
	public function save($db)
	{
		// Any Errors such as integrity Constraints being violated will be displayed as errors properly in controller.
		$db->query("INSERT into transaction (transId,userId,currencyAmount) VALUES ($this->transId,$this->userId,$this->currencyAmount)");
	}


	// Accessors
	public function getTransId()
	{
		return $this->transId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getCurrencyAmount()
	{
		return $this->currencyAmount;
	}

	public function setTransId($transId)
	{
		$this->transId = $transId;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
	}

	public function setCurrencyAmount($currencyAmount)
	{
		$this->currencyAmount = $currencyAmount;
	}

}

?>