<?php
	session_start(); // Start a new session

	ini_set('display_errors', 1); // Change the config so that errors are displayed

	if (isset($_SESSION["auth"]) && $_SESSION["auth"] === true) { // Checks if the current session is logged in
		header("location: home.php"); // If so, the user is redirected to the home page
		exit; // Ends the current php script
	}

	require_once("config.php"); // import config.php which contains the connection to my database

	$usernameError = $emailError = $passwordError = ""; // error variables

	if ($_SERVER["REQUEST_METHOD"] == "POST") { // If the request is coming through POST
		// assign variables from POST request
		$email = strtolower(trim($_POST["email"])); // set email to lowercase
		$username = strtolower(trim($_POST["username"])); // set username to lowercase
		$password = $_POST["password"];
		$confPassword = $_POST["confPassword"];

		$email = stripcslashes($email); // strip off backslahes e.g. /n
		$username = stripcslashes($username);
		$password = stripcslashes($password);
		$confPassword = stripcslashes($confPassword);
		$email = mysqli_real_escape_string($database, $email); // escapes special characters to avoid SQL injection
		$username = mysqli_real_escape_string($database, $username);
		$password = mysqli_real_escape_string($database, $password);
		$confPassword = mysqli_real_escape_string($database, $confPassword);

		// email validation
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // check if email is of valid format
			$emailError = "Please enter a valid email!"; // set variable to error
		} else {
			$sql = "SELECT * FROM Users WHERE Email = '$email'"; // construct SQL statement
			if (mysqli_num_rows(mysqli_query($database, $sql)) >= 1) { // if 1 or more rows are returned
				$emailError = "Email already in use!"; // set variable to error
			}
		}

		// username validation
		if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) { // regex to check that the username is valid
			$usernameError = "Username can only contain letters, nums, and underscores!"; // set variable to error
		} else {
			$sql = "SELECT * FROM Users WHERE Username = '$username'"; // construct SQL statement
			if (mysqli_num_rows(mysqli_query($database, $sql)) >= 1) { // if 1 or more rows are returned
				$emailError = "Username already in use!"; // set variable to error
			}
		}

		// password validation
		if ($password != $confPassword) { // check if password does not equal the confPassword input
			$passwordError = "Passwords do not match!"; // set variable to error
		}

		if (empty($usernameError) && empty($passwordError) && empty($emailError)) { // check if all the errors are empty
			$parPassword = password_hash($password, PASSWORD_DEFAULT); // Creates a hashed password
			// redirect user to activate.php with the username, email, and hashed password set as IDs
			header("location: activate.php?username=$username&email=$email&password=$parPassword&register=1");
		}

		mysqli_close($database); // close the database
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Register</title>
		<link rel="stylesheet" href="css\styles.css">
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
						<h3>Register</h3>
						<form method="POST">
							<input name="email" type="text" placeholder="Email" required/>
							<input name="username" type="text" placeholder="Username" minlength="8" required/>
							<input id="password" name="password" type="password" placeholder="Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required/>
							<div id="vanisher">
								<h5>Password must contain the following:</h5>
								<h5 id="low" class="invalid">A <b>lowercase</b> character</h5>
								<h5 id="up" class="invalid">An <b>uppercase</b> character</h5>
								<h5 id="num" class="invalid">A <b>number</b></h5>
								<h5 id="len" class="invalid">Minimum <b>8 characters</b></h5>
							</div>
							<script>
								// find elements using ID and assign them to variables
								var pw = document.getElementById("password");
								var low = document.getElementById("low");
								var up = document.getElementById("up");
								var num = document.getElementById("num");
								var len = document.getElementById("len");

								var lowLetters = /[a-z]/g; // regex for lowercase letters
								var upLetters = /[A-Z]/g; // regex for uppercase letters
								var nums = /[0-9]/g; // regex for digits

								pw.onfocus = function() { // when the user clicks into the password input
									document.getElementById("vanisher").style.display = "block"; // set the css display to block so it appears
								}

								pw.onblur = function() { // when the user clicks out of the password input
									document.getElementById("vanisher").style.display = "none"; // set the css display to none so it disappears
								}

								pw.onkeyup = function() { // after releasing a key press
									if (pw.value.match(lowLetters)) { // check if pw box matches regex
										low.classList.remove("invalid"); // remove invalid class from low
										low.classList.add("valid"); // add valid class to low
									} else {
										low.classList.remove("valid"); // remove valid class from low
										low.classList.add("invalid"); // add valid class to low
									}
									
									if (pw.value.match(upLetters)) { // check if pw box matches regex
										up.classList.remove("invalid"); // remove invalid class from up
										up.classList.add("valid"); // add valid class to up
									} else {
										up.classList.remove("valid"); // remove valid class from up
										up.classList.add("invalid"); // add valid class from up
									}
									
									if (pw.value.match(nums)) { // check if pw box matches regex
										num.classList.remove("invalid"); // remove invalid class from num
										num.classList.add("valid"); // add valid class to num
									} else {
										num.classList.remove("valid"); // remove valid class from num
										num.classList.add("invalid"); // add invalid class to num
									}
									
									if (pw.value.length >= 8) { // check if pw length is more than or equal to 8
										len.classList.remove("invalid"); // remove invalid class from len
										len.classList.add("valid"); // add valid class to len
									} else {
										len.classList.remove("valid"); // remove valid class from len
										len.classList.add("invalid"); // add invalid class to len
									}
								}
							</script>
							<input name="confPassword" type="password" placeholder="Confirm Password" required/>
							<button>Register</button>
							<?php
								if (!empty($emailError)) { // if emailError is not empty
									echo '<div class="error">' . $emailError . '</div>'; // display error text
								}
								if (!empty($usernameError)) { // if usernameError is not empty
									echo '<div class="error">' . $usernameError . '</div>'; // display error text
								}
								if (!empty($passwordError)) { // if passwordError is not empty
									echo '<div class="error">' . $passwordError . '</div>'; // display error text
								}
							?>
						</form>
						<p>Already have an account? <a href="index.php">Login now!</a></p>
					</div>
				</center>
			</div>
			<div class="column side">
				<p>
			</div>
		</div>
	</body>
</html>
