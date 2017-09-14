<?php
// Everything we're doing is JSON
header('Content-type: text/javascript');

// The allowed paths to modules in the secure folder
$allowed = array(
	"scorepost" => true,
	"leaderboardget" => true,
	"transactionstats" => true,
	"transaction" => true,
	"timestamp" => true
);

class Controller
{
	// Public Constants
	public const INVALID_PATH = "Invalid URL, Please consult Server Specification Ducument.";

	// Private Constants
	private const SECURE_PATH = "secure";

	private $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function isAllowed()
	{
		global $allowed;
		return $allowed[$this->path];
	}

	public function run()
	{
		require(self::SECURE_PATH . "/$this->path.php");
	}
}

$path = strtolower( stripslashes( trim( $_GET['path'] ) ) );

try
{
	$ctrl = new Controller($path);
	if ($ctrl->isAllowed())
		$ctrl->run();
	else
		throw new Exception(Controller::INVALID_PATH);
}
catch (Exception $e)
{
	$error = array('Error' => true, "ErrorMessage"=> $e->getMessage() );
	echo json_encode($error);
}
?>