<?php

class TimestampManager
{
	private $data = array('Timestamp' => time() );

	public function printJSON()
	{
		echo json_encode($this->data);
	}
}

header('Content-type: text/javascript');

$mgr = new TimestampManager;
$mgr->printJSON();
?>