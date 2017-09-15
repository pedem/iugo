<?php
require("verifiable.php");

class Transaction extends VerifiableHelper
{
	public const TRANSID_MISSING 	= "TransactionId Missing or not Numeric(Integer)";
	public const USERID_MISSING 	= "UserId Missing or not Numeric(Integer)";
	public const CURRENCY_MISSING	= "CurrencyAmount Missing or not Numeric(Double)";

	// Internal private variables
	private $transId;
	private $userId;
	private $currencyAmount;

	protected const VERIFY_ARRAY=array(
			"TransactionId" => 'getTransId',
			"UserId" => 'getUserId',
			"CurrencyAmount" => 'getCurrencyAmount'
		);
	
	/*
	Create a new Transaction, with validation checks.
	There are three manditory fields for this constructor
	$transId:			Integer - The Transaction ID (UNIQUE)
	$userId:			Integer - The User's ID
	$currencyAmount:	Number  - The amount of currency for this transaction.
	*/
	public function __construct($transId, $userId, $currencyAmount)
	{
		// Set the internal values
		$this->setTransId($transId);
		$this->setUserId($userId);
		$this->setCurrencyAmount($currencyAmount);
	}

	/*
	Save this Transaction.
	There is one manditory field for this function
	$db:		OBJECT(PDO) - The Database Object
	*/
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
		if (is_null($transId) || !is_int($transId))
		{
			throw new Exception(self::TRANSID_MISSING);
		}
		$this->transId = $transId;
	}

	public function setUserId($userId)
	{
		if (is_null($userId) || !is_int($userId))
		{
			throw new Exception(self::USERID_MISSING);
		}
		$this->userId = $userId;
	}

	public function setCurrencyAmount($currencyAmount)
	{
		if (is_null($currencyAmount) || !is_double($currencyAmount))
		{
			throw new Exception(self::CURRENCY_MISSING);
		}
		$this->currencyAmount = $currencyAmount;
	}

}

?>