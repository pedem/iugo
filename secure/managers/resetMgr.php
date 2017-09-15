<?php
require("secure/db/mysql.php");
require("secure/entities/verifiable.php");

class ResetManager extends VerifiableHelper
{
	private $tableNames;
	private const ALL_TABLE_NAMES = array('leaderboard','transaction','userdata');
	private const INVALID_TABLES = "You must set TableNames to either an array of Table names or ALL";
	private const VERIFY_ERROR		= "Could Not Verify Tables to Reset";
	private const VERIFIER_MISSING	= "Verifier Missing." . self::VERIFY_ERROR;

	protected const VERIFY_ARRAY=array(
			"TableNames" => 'getTableNamesAsStr' # It will expect this in a hashable form, not an array
		);

	public function __construct($postData)
	{
		$tableNames = $postData['TableNames'];

		if (is_null($tableNames))
		{
			throw new Exception();
		}

		if ($tableNames=="ALL")
		{
			$tableNames = self::ALL_TABLE_NAMES;
		}

		$this->tableNames = $tableNames;

		$verifier = $postData['Verifier'];

		if (is_null($verifier))
		{
			throw new Exception(self::VERIFIER_MISSING);
		}

		if ($this->toVerifierStr()!=$verifyStr)
		{
			throw new Exception(self::VERIFY_ERROR);
		}

		$ds = new Datastore;
		$db = $ds->getDB();

		foreach ($this->tableNames as $val)
		{
			$db->query("TRUNCATE `$val`");
		}

		echo json_encode( array("Success"=> true ) );
	}

	public function getTableNamesAsStr()
	{
		$retStr = "";
		foreach ($this->tableNames as $val)
		{
			$retStr = $retStr . $val;
		}

		return $retStr;
	}

}
?>