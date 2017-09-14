<?php
require("mysql.php");

class LeaderBoard
{
	private const LEADER_MISSING 	= "LeaderboardId Missing or not Numeric";
	private const USERID_MISSING 	= "UserId Missing or not Numeric";
	private const SCORE_MISSING		= "Score Missing or not Numeric";

	private $userId;
	private $leaderboardId;
	private $score;
	private $rank;

	public function __construct($userId, $leaderboardId, $score)
	{


		if (is_null($userId) || !is_int($userId))
		{
			throw new Exception(self::USERID_MISSING);
		}

		if (is_null($leaderboardId) || !is_int($leaderboardId))
		{
			throw new Exception(self::LEADER_MISSING);
		}

		if (is_null($score) || !is_int($score))
		{
			throw new Exception(self::SCORE_MISSING);
		}


		$this->userId = $userId;
		$this->leaderboardId = $leaderboardId;
		$this->score = $score;
	}

	public function toArray()
	{
		// I'm Assuming that order matters to the client.
		return array(
			'UserId' => $this->userId,
			'LeaderboardId' => $this->leaderboardId,
			'Score' => $this->score,
			'Rank' => $this->rank
		);
	}

	public static function load($db, $userId, $leaderboardId)
	{
		if (is_null($userId) || !is_int($userId))
		{
			throw new Exception(self::USERID_MISSING);
		}

		if (is_null($leaderboardId) || !is_int($leaderboardId))
		{
			throw new Exception(self::LEADER_MISSING);
		}

		$stmt = $db->query("SELECT score, rank FROM leaderboard WHERE leaderboardId=$leaderboardId AND userId=$userId");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (count($results)!=1)
		{
			return null;
		}

		$row = $results[0];

		$retVal = new LeaderBoard($userId, $leaderboardId, (int)$row['score']);
		$retVal->rank = (int)$row['rank'];

		return $retVal;
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

			$this->rank = ((int)$myRankResults[0]['cnt']) + 1;  // Only one row.

			// We are adding ourselves.

			$db->beginTransaction();

			try
			{
				$db->query("UPDATE leaderboard SET rank=rank+1 WHERE leaderboardId=$this->leaderboardId AND score>$this->score");  // Bump lower scores down in rank.
				$db->query("INSERT into leaderboard (leaderboardId,userId,score,rank) VALUES ($this->leaderboardId,$this->userId,$this->score, $this->rank)");  // Insert ME

				$db->commit();
			}
			catch (Exception $e)
			{
				$db->rollback();
				throw $e;
			}
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

				$this->rank = ((int)$myRankResults[0]['cnt']) + 1;  // Only one row.

				$db->beginTransaction();
				try
				{
					// Bump lower scores down in rank.  This will also update us, but we don't really care, we'll set ours in the next update.  Filtering us out of this query would just take longer than needed.
					$db->query("UPDATE leaderboard SET rank=rank+1 WHERE leaderboardId=$this->leaderboardId AND score>$this->score AND score<=$currentScore");  
					$db->query("UPDATE leaderboard SET score=$this->score,rank=$this->rank WHERE leaderboardId=$this->leaderboardId and userId=$this->userId");  // Update ME
					$db->commit();
				}
				catch (Exception $e)
				{
					$db->rollback();
					throw $e;
				}
			}
		}

		return;

	}

	// Accessors, depite the fact we only need one.

	public function getLeaderboardId()
	{
		return $this->leaderboardId;
	}
	public function getUserId()
	{
		return $this->userId;
	}
	public function getScore()
	{
		return $this->score;
	}
	public function getRank()
	{
		return $this->rank;
	}
}

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
		$leaderEntry = $this->createLeaderBoard($postData['LeaderboardId'],$postData['UserId'],$postData['Score']);
		$leaderEntry->save();

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

		$stmt = $db->query("SELECT userId as UserId, score as Score, rank as Rank FROM leaderboard WHERE leaderboardId=".$leaderEntry->getLeaderboardId()." ORDER BY score ASC LIMIT $limit OFFSET $offset");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$entryArr = $leaderEntry->toArray();

		$entryArr["Entries"] = $results;

		echo json_encode( $entryArr );
	}

}
?>