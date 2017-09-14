<?php
// Everything we're doing is JSON
header('Content-type: text/javascript');

// The allowed paths to modules in the secure folder
$allowed = array(
	"transactionstats" => true,
	"transaction" => true,
	"timestamp" => true
);

class Controller
{
	// Privvate Constants
	private const SECURE_PATH = "secure";
	private const INVALID_PATH = "Invalid URL, Please consult Server Specification Ducument.";

	private $allowed;
	private $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function isAllowed()
	{
		return $allowed[$this->path];
	}

	public function run()
	{
		require(self::SECURE_PATH . "/$path.php");
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