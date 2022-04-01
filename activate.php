<?php
	session_start(); // resume the current session
	require_once("config.php"); // import config.php

	if (isset($_SESSION["auth"]) && $_SESSION["auth"] === true) { // Checks if the current session is logged in
		header("location: home.php"); // If so, the user is redirected to the home page
		exit; // Ends the current php script
	}

	if (isset($_GET["register"])) { // checks if the GET request contains the ID register
		$email = $_GET["email"]; // gets the email id and assigns it to this variable
		$password = $_GET["password"]; // get the password from id
		$username = $_GET["username"]; // get the username from id
		$act = md5(rand(0,1000));
		$expire = time() + (24 * 60 * 60); // 24 * 60 * 60 seconds (1 day) added to unix epoch
		// construct SQL statement to insert user into database
		$sql = "INSERT INTO Users (Username, Password, Email, ActivationCode, ActivationExpiry) VALUES ('".$username."', '".$password."', '".$email."', '".$act."', '".$expire."')";

		if (mysqli_query($database, $sql)) {
			// TODO(Harry): Finalise email text and design
			$body = '<html><body><p>Your activation code is '.$act.'</p>
<p>Please click the link below to verify your email address:</p>
<p><a href="http://82.19.253.173:31417/activate.php?id='.$act.'&email='.$email.'">http://82.19.253.173:31417/activate.php?id='.$act.'&email='.$email.'</a></p>
<p>Thanks!</p>
<p>
<p>
<h3><b>Bob Jefferies</b></h3>
<h4>Head of authentication at Le Santo inc.</h4>
</body>
</html>';

			// HACK(Harry): PHP Mail gets filtered, this seems to bypass it (uses sSMTP)
			shell_exec("echo '$body' | mail -s 'Test' -a 'Content-Type: text/html' -a From:'Bob Jefferies'\<harrysraspberrypi@gmail.com\> $email");
			header("location: index.php?act=1"); // redirect the user to the login page with the id act set to 1

		} else {
			echo "Something went wrong! " . mysqli_error($database); // display any errors
		}
	}

	if (isset($_GET["id"]) && isset($_GET["email"])) { // if the id and email id fields are filled in
		$id = $_GET["id"]; // set id variable to the id
		$email = $_GET["email"]; // set the email variable to the email
		$sql = "SELECT * FROM Users WHERE ActivationCode = '".$id."' AND Email = '".$email."'"; // create an SQL statement to find the record
		if (mysqli_num_rows(mysqli_query($database, $sql)) >= 1) { // if 1 or more rows are returned
			$sql = "UPDATE Users SET Activated = 1 WHERE ActivationCode = '".$id."' AND Email = '".$email."'"; // set activated to true
			mysqli_query($database, $sql); // query the database with the above statement
			header("location: index.php?act=2"); // redirect the user to the login page with the id act set to 2
		}
	} else {
		echo "What are you doing here!!!!! BE GONE!"; // error message xoxoxo
		header("location: index.php"); // redirect the user to the index page
	}
?>
