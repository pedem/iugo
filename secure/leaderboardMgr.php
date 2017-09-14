<?php
require("mysql.php");

class LeaderBoard
{
	private const LEADER_MISSING 	= "LeaderboardId Missing";
	private const USERID_MISSING 	= "UserId Missing";
	private const SCORE_MISSING		= "Score Missing";

	private $userId;
	private $leaderboardId;
	private $score;
	private $rank;

	public function __construct($userId, $leaderboardId, $score)
	{


		if (is_null($userId))
		{
			throw new Exception(self::USERID_MISSING);
		}

		if (is_null($leaderboardId))
		{
			throw new Exception(self::LEADER_MISSING);
		}

		if (is_null($score))
		{
			throw new Exception(self::SCORE_MISSING);
		}


		$this->userId = $userId;
		$this->leaderboardId = leaderboardId;
		$this->score = score;
	}


	public function save()
	{
		$ds = new Datastore;
		$db = $ds->getDB();

		/*
		We're going to need to know what rank we are.
		Here are the scenarios:
			1) We didn't have a rank.  Add us, and lower the rank of every lower score.
			2) We had a rank, which has a HIGHER score than us.  In which case, we use THAT entry and don't update anything.
			3) We had a rank, and a LOWER score.  In this case, we update our rank, lower the rank of scores between our new score(exclusive) and our last score (inclusive).
		*/

		$stmt = $db->query("SELECT userId as UserId, score as Score, rank as Rank FROM leaderboard WHERE leaderboardId=$this->leaderboardId AND userId=$this->userId");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		echo json_encode( $results );

		return;


		$stmt = $db->query("SELECT COUNT(*) as cnt, SUM(currencyAmount) as all_sum FROM transaction WHERE userId=$userId");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$resultsSet = $db->query("SELECT userId as UserId, score as Score, rank as Rank FROM leaderboard WHERE leaderboardId=$this->leaderboardId ORDER BY score");


		$rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);


		// Any Errors such as integrity Constraints being violated will be displayed as errors properly in controller.
		$db->beginTransaction();


		$db->query("Insert into leaderboard (userId, leaderboardId, score) VALUES ($this->userId,$this->leaderboardId,$this->score)");
	}
}

class LeaderBoardManager
{
	/*
		I've made this function to create a LeaderBoard. 
		It's simple, but if there becomes anything we wish to do upon making a LeaderBoard, the logic will go here.
	*/
	public function createLeaderBoard($leaderboardId, $userId, $score )
	{
		return new LeaderBoard($userId, $leaderboardId, $score);
	}

	public function insertLeaderEntryFromPost($postData)
	{
		$leaderEntry = $this->createLeaderBoard($postData['LeaderboardId'],$postData['UserId'],$postData['Score']);
		$leaderEntry->save();
	}

	public function getRankingsFromPost($postData)
	{
		$leaderEntry = $this->createLeaderBoard($postData['LeaderboardId'],$postData['UserId'],$postData['Score']);
		$leaderEntry->save();
	}

}
?>