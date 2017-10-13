<?php
	$valError = $successMsg = $submitemail = $submitpassword = $loginemail = $loginpassword = $row_id = $query = "";
	include_once "diarydb.php";
	
	session_start();

	// Check if "logout" has been passed to this file (inside the _GET array)
	if (array_key_exists("logout", $_GET)) {
		// destroy the SESSION var array
		unset($_SESSION);
		// reset the "id" reference
		$_SESSION['id'] = "";
		// reset the id cookie
		setcookie('id', "", time() - 60 * 60);
		$_COOKIE['id'] = "";
		session_destroy();
	} else if ((array_key_exists("id", $_SESSION) and !empty($_SESSION['id'])) or 
				(array_key_exists("id", $_COOKIE) and !empty($_COOKIE['id']))) {
		// if SESSION array has "id" and it is not empty OR COOKIE array has "id" and is not empty, 
		// then must be already logged in!
		header("Location: diaryentry.php");
	}

	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {	
		if (isset($_POST["signup"])) {
			checkSubmitFields();
			if (!$valError) {
				$anyError = false;
				// Check if the email already exists
				$query = "SELECT `id` FROM diaryUserEntries WHERE `email` = '".$submitemail."'";
				if ($result = mysqli_query($dblink, $query)) {
					$row = mysqli_fetch_array($result);
					if (sizeof($row) > 0) {
						$anyError = true;
						$valError = "<strong>Email: ".$submitemail." is already in use.</strong></br>";
					}
				} 
				if (!$anyError) {			
					mysqli_autocommit($dblink, FALSE);
					if (mysqli_query($dblink, "START TRANSACTION")) {
						$query = "INSERT into `diaryUserEntries` (`email`) VALUES('$submitemail')";
						if (!mysqli_query($dblink, $query)) {
							$anyError = true;
						} else {
							// Retrieve back the inserted row to get the row id to use for the session.
							$query = "SELECT `id` FROM diaryUserEntries WHERE `email` = '$submitemail'";					
							if ($result = mysqli_query($dblink, $query)) {
								$row = mysqli_fetch_array($result);
								$row_id= $row['id'];
								// Update the password
								$submitpassword = password_hash($submitpassword, PASSWORD_DEFAULT);
								$query = "UPDATE `diaryUserEntries` SET `password` = '$submitpassword' WHERE id = $row_idLIMIT 1";
								if (!mysqli_query($dblink, $query)) {
									$anyError = true;
								}
							} else {
								$anyError = true;
							}
						}
						if ($anyError) {
							mysqli_query($dblink, "ROLLBACK");
						} else {
							if (!mysqli_query($dblink, "COMMIT"))
								$anyError = true;
						}
					} else {
						$anyError = true;
					}
					if ($anyError) {
						$valError = "An error occurred trying to add new Email: ".$submitemail;
					} else {
						$_SESSION['id'] = $row_id;
						// stay logged in for 12 hours
						if ($_POST['submitRemember'])
							setcookie('id', $row_id, time() + 60*60*12);
						header("Location: diaryentry.php");
					}
				}
			}
		} elseif (isset($_POST["login"])) {
			checkSubmitFields();
			if (!$valError) {
				$anyError = false;
				$query = "SELECT `id`,`password` FROM diaryUserEntries WHERE `email` = '".$submitemail."'";
				if ($result = mysqli_query($dblink, $query)) {
					$row = mysqli_fetch_array($result);
					if (sizeof($row) == 0) {
						// No-one exists with the particular email
						$anyError = true;
					} else {
						$row_id= $row['id'];
						$thisPassword = $row['password'];
						// Verify the entered password against the stored hash
						if (!password_verify($submitpassword, $thisPassword)) {
							$anyError = true;
						}
					}
				}
				if ($anyError) {
					$valError = "<strong>Email or Password is incorrect</strong><br>";
				} else {
					$_SESSION['id'] = $row_id;
					// stay logged in for 12 hours
					if ($_POST['submitRemember']) {
						setcookie('id', $row_id, time() + 60*60*12);
					}
					header("Location: diaryentry.php");
				}
			}
		} else {
			$valError = "Unexpected error: Please try again.</br>";
		}
		if ($valError) {
			$valError = "The following errors were found: </br>".$valError;
			$valError = '<div class="alert alert-danger alert-dismissible fade show" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>'.$valError.'</div>';
		}
	}

	// Check the email and password fields submitted are not empty and clean up the strings
	function checkSubmitFields()
	{
		global $dblink, $submitemail, $valError, $submitpassword;

		if (empty($_POST["submitEmail"])) {
			$valError = "<strong>An email address is required</strong></br>";
		} else {
			$submitemail = clean_input(mysqli_real_escape_string($dblink, $_POST["submitEmail"]));	
		}
		if (empty($_POST["submitPassword"])) {
			$valError .= "<strong>A password is required</strong></br>";
		} else {
			$submitpassword = clean_input(mysqli_real_escape_string($dblink, $_POST["submitPassword"]));
		}
		return;
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Secret Diary</title>
	    <!-- Required meta tags always come first -->
	    <meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	    <meta http-equiv="x-ua-compatible" content="ie=edge">

	    <!-- Bootstrap CSS -->
	    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">

	    <style type="text/css">	    
	    	html { 
  				background: url(images/diary.jpg) no-repeat center center fixed; 
  				-webkit-background-size: cover;
  				-moz-background-size: cover;
  				-o-background-size: cover;
  				background-size: cover;
			}	
	   		body {
	    		background: none;
	    		/*text-align: center; */
	    		color: white;
	    	}
	    	h1 {
	    		margin-top: 125px;
	    		font-size: 350%;
	    		text-align: center;
	    	}
	    	h4 {
	    		margin-top: 20px;
	    		text-align: center;
	    	}
	    	#emailHeading {
	    		margin-top: 30px;
	    		font-weight: normal;
	    		text-align: center;
	    	}
	    	#signupText {
	    		text-align: center;
	    		font-size: 75%;
	    	}
	    	.entryFields {
	    		margin: 0 auto;
	    		width: 300px;
	    	}
	    	#switchMode {
	    		margin: 0 auto;
	    		padding-top: 15px;
	    		color: white;
	    	}
	    	.hidden {
	    		display: none;
	    	}
	    </style>
	</head>

	<body>

		<div class="container">
			<h1>Secret Diary</h1>
			<h4>Store your thoughts permanently and securely.</h4>

			<h5 id="emailHeading">Interested? Sign up now.</h5>
			
			<div class="entryFields" id="userMessage">
				<?php 
					if ($valError)
						echo $valError;
				?>
			</div>
			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<fieldset class="form-group">
					<input type="email" class="form-control entryFields" id="submitEmail" name="submitEmail" placeholder="Your Email">
					<div class="text-muted" id="signupText">We'll never share your email with anyone else</div>
				</fieldset>
				<fieldset class="form-group">
					<input type="password" class="form-control entryFields" id="submitPassword" name="submitPassword" placeholder="Password">
				</fieldset>
				
				<!-- Had lots of problems getting this centered! Google to find text-center, which worked for buttons below, but did not work for the
					checkbox below - I had to remove the form-inline and form-control to get it to center and checkbox next to text!! -->
				<div class="text-center">
					<label><input type="checkbox" id="submitRemember" name="submitRemember"> Stay signed on?</label>
				</div>
			
				<div class="text-center">
					<button type="submit" id="submitButton" name="signup" class="btn btn-success">Sign Up!</button>
				</div>
				<div class="text-center">
					<button type="button" id="switchMode" class="btn btn-link">Login</button>
				</div>
			</form>

		</div>

		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
    	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>

	    <script type="text/javascript">
	    	$("#switchMode").click(switchMode);

	    	$("form").submit(function(e) {
	    		return(validateForm(e));
	    	});

	    	$(document).ready(function (argument) {
	    		$("#switchMode").click(function (event) {
	    			$(this).blur();
	    		});
	    	});

	    	function switchMode() {
	    		if ($("#signupText").hasClass("hidden")) {
	    			$("#emailHeading").html("Interested? Sign up now.");
	    			$("#submitButton").html("Sign Up!");
	    			$("#submitButton").attr('name', "signup");
	    			$(this).html("Login");
	    		} else {
	    			$("#emailHeading").html("Login using your email and password.");
	    			$("#submitButton").html("Login");
	    			$("#submitButton").attr('name', "login");
	    			$(this).html("Sign Up");
	    		}
	    		$("#signupText").toggleClass("hidden");
	    	}

	    	function validateForm(e) {
	    		var errorMessage = "";
				var fieldsMissing = "";

				if ($("#submitEmail").val() == "") {
					fieldsMissing += "<br>Email";
				}

				if ($("#submitPassword").val() == "") {
					fieldsMissing += "<br>Password";
				}

				if (fieldsMissing != "") {
					errorMessage += "<strong>The following field(s) cannot be empty:</strong>" + fieldsMissing;
				}

				if (errorMessage != "") {					
					$("#userMessage").html('<div class="alert alert-danger alert-dismissible fade show" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>' + errorMessage + '</div>');
					return false;
				} else {
					return true;
				}
	    	}
	    </script>

	</body>

</html>