<?php
	session_start(); // start a new session

	$_SESSION["auth"] = false; // set session variable auth to false
	header("location: index.php"); // redirect the user to the login page

?>