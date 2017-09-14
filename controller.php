<?php
// Everything we're doing is JSON
header('Content-type: text/javascript');

$allowed = array(
	"transactionstats" => true,
	"transaction" => true,
	"timestamp" => true
);
$path = strtolower( stripslashes( trim( $_GET['path'] ) ) );
try
{
	if ($allowed[$path])
		require("$path.php");
	else
		throw new Exception("Invalid URL, Please consult Server Specification Ducument.");
}
catch (Exception $e)
{
	$error = array('Error' => true, "ErrorMessage"=> $e->getMessage() );
	echo json_encode($error);
}
?>