<?php
// Everything we're doing is JSON
header('Content-type: text/javascript');

// Syntax Errors in the requires will now be caught in main try/catch block.
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");

$path = strtolower( stripslashes( trim( $_GET['path'] ) ) );
try
{
	require("$path.php");
}
catch (Exception $e) {
    $error = array('Error' => true, "ErrorMessage"=> $e->getMessage() );
    echo json_encode($error);
}
?>