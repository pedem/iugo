<?php

class TimestampManager
{
	// We don't want this public, will hold timestamp data
	private $data;

	public function __construct()
	{
		// initialize the data to the current time.
		$this->data = array('Timestamp' => time() );
	}

	/*
		Function to print the internally stored data as a JSON string.
	*/
	public function printJSON()
	{
		echo json_encode($this->data);
	}
}

$mgr = new TimestampManager;
$mgr->printJSON();
?>