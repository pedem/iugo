<?php
$path = strtolower( stripslashes( trim( $_GET['path'] ) ) );

require("$path.php");
?>