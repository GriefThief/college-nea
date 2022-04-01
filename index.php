<?php
	session_start(); // Start a new session

	ini_set('display_errors', 1); // Change the config so that errors are displayed

	if (isset($_SESSION["auth"]) && $_SESSION["auth"] === true) { // Checks if the current session is logged in
		header("location: home.php"); // If so, the user is redirected to the home page
		exit; // Ends the current php script
	}

	require_once("config.php"); // Load the config.php program which connects to the database

	if ($_SERVER["REQUEST_METHOD"] == "POST") { // If a POST request comes from the server

		$logInError = "";
		$username = trim($_POST['username']); // Assign text from input 'username' to variable $username with trailing spaces removed
		$password = $_POST['password']; // Assign text from input 'password' to variable $password

		// Filter the username and password variables to avoid SQL injection
		$username = stripcslashes($username);
		$password = stripcslashes($password);
		$username = mysqli_real_escape_string($database, $username);
		$password = mysqli_real_escape_string($database, $password);

		$sql = "SELECT * FROM Users WHERE Username = '".$username."' AND Activated = 1"; // construct slq statement
		$result = mysqli_query($database, $sql); // assign the result to result variable
		
		if (mysqli_num_rows($result) >= 1) { // if at least one row is returned
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC); // Assign the results of the query to array $row
			$hashPassword = $row["Password"]; // Assign the password from the database to hashPassword
			if (password_verify($password, $hashPassword)) { // verify if the passwords are the same
				session_start(); // resume current session
				$_SESSION["auth"] = true; // set auth to true
				header("location: home.php"); // redirect the user to the home page
			} else {
				$logInError = "Invalid username or password!"; // Set the logInError variable to the error
			}
		} else {
			$logInError = "Invalid username or password!"; // set variable to error
		}
		mysqli_close($database); // close the database connection
	}

	if (isset($_GET["act"])) { // if act is set in the get request
		// if ($_GET["act"] == 1) { // if get is set to 1
		// 	$logInError = "Activation email sent!"; // display confirmation message
		// } else if ($_GET["act"] == 2) {
		// 	$logInError = "Account activated successfully!";
		// }

		switch ($_GET["act"]) {
			case 1:
				$logInError = "Activation email sent!"; // display confirmation message
				break;
			case 2:
				$logInError = "Account activated successfully!";
				break;
		}
	}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Login</title>
		<link rel="stylesheet" href="css\styles.css"/>
	</head>
	<body>
		<div class="row">
			<div class="header">
				<h1>Student Database</h1>
			</div>
			<div class="column side">
				<p>
			</div>
			<div class="column middle">
				<center>
					<div class="form">
						<h3>Login</h3>
						<form method="POST">
							<input name="username" type="text" placeholder="Username" required/>
							<input name="password" type="password" placeholder="Password" required/>
							<button>Login</button>
						</form>
						<p>Don't have an account? <a href="register.php">Register now!</a></p>
					</div>
					<?php
					if (!empty($logInError)) {
						echo '<div class="error">' . $logInError . '</div>';
					}
					?>
				</center>
			</div>
			<div class="column side">
				<p>
			</div>
		</div>
	</body>
</html>