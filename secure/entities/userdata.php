<?php

class UserData
{
	public const USERID_MISSING 	= "UserId Missing or not Numeric";
	public const DATA_MISSING		= "Data Missing";

	// Internal private variables
	private $userId;
	private $data;

	/*
	Create a new UserData, with validation checks.
	There are two manditory fields for this constructor
	$userId:			Integer - The User's ID
	$data:				String  - The Serialized JSON Data you want to save
	*/
	public function __construct($userId, $data)
	{
		// Set the internal values
		$this->setUserId( $userId );
		$this->setData( $data );
	}


	// Create an Array representation of this UserData.
	// This would go away if this became Verifiable.
	public function toArray()
	{
		return array(
			"UserId" => $this->userId,
			"Data" => json_decode($this->data)
		);
	}

	// This function will escape the JSON string so it inserts properly.
	private function mysql_escape($inp) { 
		if(is_array($inp)) 
		{
			return array_map(__METHOD__, $inp); 
		}

		if(!empty($inp) && is_string($inp))
		{ 
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
		} 

		return $inp; 
	} 

	/*
	Save this UserData.
	There is one manditory field for this function
	$db:		OBJECT(PDO) - The Database Object
	*/
	public function save($db)
	{
		// Any Errors such as integrity Constraints being violated will be displayed as errors properly in controller.
		$db->query("INSERT into userdata (userId,data) VALUES ($this->userId,\"".$this->mysql_escape($this->data)."\")");
	}

	/*
	Update this UserData.
	There is one manditory field for this function
	$db:		OBJECT(PDO) - The Database Object
	*/
	public function update($db)
	{
		// Any Errors such as integrity Constraints being violated will be displayed as errors properly in controller.
		$db->query("UPDATE userdata SET data=\"".$this->mysql_escape($this->data)."\" where userId=$this->userId");
	}

	/*
	Loads a UserData Object given a Database object and a UserId

	There are two manditory fields for this function
	$db:		OBJECT(PDO) - The Database Object
	$userId:	Integer - The User's ID
	*/
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

		return new UserData($userId, $row['data']);
	}

	/*
	Recursively merge $obj1 and $obj2 into the returned Object
	Merges destructvely to obj1

	There are two manditory fields for this function
	$obj1:		Object - The Original to Merge into
	$obj2:		Object - The Object ot add to Obj1.  Authoritative
	*/
	private function object_merge_recursive($obj1, $obj2)
	{
		// I tried array_merge_recursive, but it would make arrays when it was supposed to overwrite values.
		if ( !is_object($obj1) )
		{
			return $obj2;
		}

		foreach ($obj2 as $key => $value) {
			if (is_object($obj1))
			{
				if (property_exists($obj1,$key) )
					$obj1->$key = $this->object_merge_recursive($obj1->$key, $value);
				else
					$obj1->$key = $obj2->$key;
			}
			else
			{ 
				return $obj2;
			}
		}

		return $obj1;
	}

	/*
	Recursively update the underlying data object with information in $data, which is an Object
	There are two manditory fields for this function
	$db:		OBJECT(PDO) - The Database Object
	$data:		Object  - The Deserialized JSON Data you want to update
	*/
	public function updateData($db, $data)
	{
		$this->data = json_encode( $this->object_merge_recursive( json_decode($this->data), $data) );

		$this->update($db);
	}


	// Accessors
	public function getData()
	{
		return $this->data;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function setData($data)
	{
		if (is_null($data))
		{
			throw new Exception(self::DATA_MISSING);
		}
		$this->data = $data;
	}

	public function setUserId($userId)
	{
		if (is_null($userId) || !is_int($userId))
		{
			throw new Exception(self::USERID_MISSING);
		}
		$this->userId = $userId;
	}

}

?>