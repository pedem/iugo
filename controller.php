<?php
// Everything we're doing is JSON
header('Content-type: text/javascript');

$path = strtolower( stripslashes( trim( $_GET['path'] ) ) );
try
{
	require("$path.php");
}
catch (Exception $e)
{
	$error = array('Error' => true, "ErrorMessage"=> $e->getMessage() );
	echo json_encode($error);
}
?>