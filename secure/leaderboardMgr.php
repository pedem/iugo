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
		$this->leaderboardId = $leaderboardId;
		$this->score = $score;
	}

	public function toArray()
	{
		return array(
			'LeaderboardId' => $this->leaderboardId,
			'UserId' => $this->userId,
			'Score' => $this->score,
			'Rank' => $this->rank
		);
	}

	public function save()
	{
		$ds = new Datastore;
		$db = $ds->getDB();

		/*
		We're going to need to know what rank we are.
		Here are the scenarios:
			1) We didn't have a rank.  Add us, and lower the rank of every lower score.
			2) We had a rank, which has a HIGHER or EQUAL score than us.  In which case, we use THAT entry and don't update anything.
			3) We had a rank, and a LOWER score.  In this case, we update our rank, lower the rank of scores between our new score(exclusive) and our last score (inclusive).

		Here's the ASSUMPTION:
			We're assuming we want to pre-calculate the ranking, as insert/updates are done far less often than checking our position in the leaderboards.
			In this case, insert/updates take longer, but making the current scoreboard is rediculously easy and fast.
		*/

		$stmt = $db->query("SELECT userId as UserId, score as Score, rank as Rank FROM leaderboard WHERE leaderboardId=$this->leaderboardId AND userId=$this->userId");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// CASE 1
		if (count($results)==0)
		{
			$stmt = $db->query("SELECT COUNT(*) as cnt FROM leaderboard WHERE leaderboardId=$this->leaderboardId and score<$this->score");
			$myRankResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$newRank = (int)$myRankResults['cnt'];

			// We are adding ourselves.

			$db->beginTransaction();
			$this->rank = $newRank;

			if ($this->rank==0)
			{
				$this->rank = 1;  // No Equal!  We're Number 1!

				// Any Errors such as integrity Constraints being violated will be displayed as errors properly in controller.
				$db->query("UPDATE leaderboard SET rank=rank+1 WHERE leaderboardId=$this->leaderboardId");  // Bump everyone else down.
				$db->query("INSERT into leaderboard (leaderboardId,userId,score,rank) VALUES ($this->leaderboardId,$this->userId,$this->score, 1)");  // Insert ME
			}
			else
			{
				$db->query("UPDATE leaderboard SET rank=rank+1 WHERE leaderboardId=$this->leaderboardId AND score>$this->score");  // Bump lower scores down in rank.
				$db->query("INSERT into leaderboard (leaderboardId,userId,score,rank) VALUES ($this->leaderboardId,$this->userId,$this->score, $this->rank)");  // Insert ME
			}

			$db->commit();
		}
		else
		{
			$currentEntry = $results[0];  // Only one possible, due to DB constraints.  Count that as an assumption, I guess.

			$currentScore = (int)$currentEntry['Score'];
			$currentRank = (int)$currentEntry['Rank'];

			// CASE 2
			if ($currentScore<=$this->score)
			{
				$this->score = $currentScore;
				$this->rank = $currentRank;
			}
			else
			{
				// CASE 3
				$stmt = $db->query("SELECT COUNT(*) as cnt FROM leaderboard WHERE leaderboardId=$this->leaderboardId and score<$this->score");
				$myRankResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$this->rank = (int)$myRankResults['cnt'] + 1;

				if ($currentRank==$this->rank)
				{
					// We're improved, but our ranking is the same.  Only need to update our score.
					$db->query("UPDATE leaderboard SET score=$this->score WHERE leaderboardId=$this->leaderboardId and userId=$this->userId");
				}
				else
				{
					$db->beginTransaction();
					// Bump lower scores down in rank.  This will also update us, but we don't really care, we'll set ours in the next update.  Filtering us out of this query would just take longer than needed.
					$db->query("UPDATE leaderboard SET rank=rank+1 WHERE leaderboardId=$this->leaderboardId AND score>$this->score AND score<=$currentScore");  
					$db->query("UPDATE leaderboard SET score=$this->score,rank=$this->rank WHERE leaderboardId=$this->leaderboardId and userId=$this->userId");  // Update ME
					$db->commit();
				}
			}
		}

		return;

	}
}

class LeaderBoardManager
{
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
		$leaderEntry = $this->createLeaderBoard($postData['LeaderboardId'],$postData['UserId'],$postData['Score']);
		$leaderEntry->save();

		echo json_encode( $leaderEntry->toArray() );
	}

	public function getRankingsFromPost($postData)
	{
		$leaderEntry = $this->createLeaderBoard($postData['LeaderboardId'],$postData['UserId'],$postData['Score']);
		$leaderEntry->save();
	}

}
?>