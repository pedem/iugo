<?

class LeaderBoard
{
	private const LEADER_MISSING 	= "LeaderboardId Missing or not Numeric";
	private const USERID_MISSING 	= "UserId Missing or not Numeric";
	private const SCORE_MISSING		= "Score Missing or not Numeric";
	private const RANK_MISSING		= "Rank Missing or not Numeric";

	// Internal private variables
	private $userId;
	private $leaderboardId;
	private $score;
	private $rank;


	/*
	There are three manditory fields for this constructor
	$userId:		Integer - The User's ID
	$leaderboardId:	Integer - The Leaderboard ID
	$score:			Integer - The User's Score for this leaderboard
	*/
	public function __construct($userId, $leaderboardId, $score)
	{
		$this->setUserId( $userId );
		$this->setLeaderboardId( $leaderboardId );
		$this->setScore( $score );
	}

	// This would go away if this became Verifiable.
	public function toArray()
	{
		// I'm Assuming that order matters to the client.
		return array(
			'UserId' => $this->getUserId(),
			'LeaderboardId' => $this->getLeaderboardId(),
			'Score' => $this->getScore(),
			'Rank' => $this->getRank()
		);
	}

	/*
	Loads a UserData Object given a Database object and a UserId

	There are three manditory fields for this function
	$db:		OBJECT(PDO) - The Database Object
	$userId:	Integer - The User's ID
	$leaderboardId:	Integer - The Leaderboard ID
	*/
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

	/*
	Save this LeaderBoard.
	There is one manditory field for this function
	$db:		OBJECT(PDO) - The Database Object
	*/
	public function save($db)
	{
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

			$this->setRank( ((int)$myRankResults[0]['cnt']) + 1 );  // Only one row.

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
				$this->setScore( $currentScore );
				$this->setRank( $currentRank );
			}
			else
			{
				// CASE 3
				$stmt = $db->query("SELECT COUNT(*) as cnt FROM leaderboard WHERE leaderboardId=$this->leaderboardId and score<$this->score");
				$myRankResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$this->setRank( ((int)$myRankResults[0]['cnt']) + 1 );  // Only one row.

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

	// Accessors

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

	public function setLeaderboardId($leaderboardId)
	{
		if (is_null($leaderboardId) || !is_int($leaderboardId))
		{
			throw new Exception(self::LEADER_MISSING);
		}

		$this->leaderboardId = $leaderboardId;
	}

	public function setUserId($userId)
	{
		if (is_null($userId) || !is_int($userId))
		{
			throw new Exception(self::USERID_MISSING);
		}

		$this->userId = $userId;
	}

	public function setScore($score)
	{
		if (is_null($score) || !is_int($score))
		{
			throw new Exception(self::SCORE_MISSING);
		}

		$this->score = $score;
	}

	public function setRank($rank)
	{
		if (is_null($rank) || !is_int($rank))
		{
			throw new Exception(self::RANK_MISSING);
		}

		$this->rank = $rank;
	}
}
?>