<?php
require("secure/db/mysql.php");
require("secure/entities/leaderboard.php");

class LeaderBoardManager
{
	private const OFFSET_MISSING 	= "Offset Missing or not Numeric";
	private const LIMIT_MISSING 	= "Limit Missing or not Numeric";
	/*
		I've made this function to create a LeaderBoard. 
		It's simple, but if there becomes anything we wish to do upon making a LeaderBoard, the logic will go here.
	*/
	public function createLeaderBoard($leaderboardId, $userId, $score)
	{
		return new LeaderBoard($userId, $leaderboardId, $score);
	}

	public function insertLeaderEntryFromPost($postData)
	{
		$ds = new Datastore;
		$db = $ds->getDB();

		$leaderEntry = $this->createLeaderBoard($postData['LeaderboardId'],$postData['UserId'],$postData['Score']);
		$leaderEntry->save($db);

		echo json_encode( $leaderEntry->toArray() );
	}

	public function getRankingsFromPost($postData)
	{
		$offset = $postData['Offset'];
		$limit = $postData['Limit'];

		if (is_null($offset) || !is_int($offset))
		{
			throw new Exception(self::OFFSET_MISSING);
		}

		if (is_null($limit) || !is_int($limit))
		{
			throw new Exception(self::LIMIT_MISSING);
		}

		$ds = new Datastore;
		$db = $ds->getDB();

		$leaderEntry = LeaderBoard::load($db, $postData['UserId'],$postData['LeaderboardId']);
		if (is_null($leaderEntry))
		{
			echo "{}";
			return;
		}

		$stmt = $db->query("SELECT userId as UserId, score as Score, rank as Rank FROM leaderboard WHERE leaderboardId=".$leaderEntry->getLeaderboardId()." ORDER BY score ASC LIMIT $limit OFFSET $offset");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$entryArr = $leaderEntry->toArray();

		$entryArr["Entries"] = $results;

		echo json_encode( $entryArr );
	}

}
?>