<?php
require("db/mysql.php");
require("entities/userdata.php");

class UserDataManager
{

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
			$userData = new UserData($postData['UserId'], json_encode( $postData['Data']) );
			$userData->save($db);
		}
		else
		{
			$userData->updateData($db, $postData['Data'] );
		}

		$this->success();
	}

	public function loadFromPost($postData)
	{
		$ds = new Datastore;
		$db = $ds->getDB();

		$userData = UserData::load($db, $postData['UserId']);

		if (is_null($userData))
		{
			echo "{}";
		}
		else
		{
			echo $userData->getData();
		}
	}
}

?>