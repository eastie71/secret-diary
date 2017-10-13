<?php
	include_once "diarydb.php";
	$diaryContent = "";
	session_start();

	if (array_key_exists("id", $_COOKIE) && $_COOKIE['id']) {
		$_SESSION['id'] = $_COOKIE['id'];
	}

	if (array_key_exists('id', $_SESSION) && $_SESSION['id']) {
		$session_id = mysqli_real_escape_string($dblink,$_SESSION['id']);
		$query = "SELECT `diaryEntry` FROM diaryUserEntries WHERE `id` = $session_id";
		if ($result = mysqli_query($dblink, $query)) {
			$row = mysqli_fetch_array($result);
			$diaryContent = $row['diaryEntry'];
		} else {
			echo "An error occurred trying to load the Diary Entry - Please try again later.";
		}	
		//print_r($_SESSION);
	} else {
		header("Location: secretdiary.php");
	}

	if (isset($_POST["logout"])) {
		unset($_SESSION);
		$_SESSION['id'] = "";
		setcookie('id', "", time() - 60 * 60);
		$_COOKIE['id'] = "";
		session_destroy();
		header("Location: secretdiary.php?logout=1");
	}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
  	<title>Diary Entry</title>
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
			background-color: transparent;
		}
		.pull-right {
			float: right;
		}
		textarea {
			resize: none;
		}
		#diaryEntry {
			width: 100%;
			margin-top: 25px;
		}
	</style>
  </head>
  <body onload="setHeight()">
    <nav class="navbar navbar-light bg-light" id="navbar">
      	<a class="navbar-brand" href="#">Secret Diary Entry</a>
      	<form method="post">
      		<button class="btn btn-outline-success" name="logout" type="submit">Logout</button>
        </form>
    </nav>
    <div class="container-fluid">
    	<form method="post">
    		<fieldset class="form-group">
				<textarea class="form-control col-xs-12" id="diaryEntry" name="diaryEntry"><?php echo $diaryContent; ?></textarea>
			</fieldset>
    	</form>
    </div>
    
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>

    <script type="text/javascript">
     	$("#diaryEntry").bind('input propertychange', function() {
          $.ajax({
            method: "POST",
            url: "updatediary.php",
            data: { content: $("#diaryEntry").val() }
          });
      });

     	// On a window resize, need to reset the height for the textarea
    	$(window).resize(function() {
    		  setHeight();
    	})
     	function setHeight() {
	        var areaHeight = (window.innerHeight - $("#navbar").height() - 55);
	        $("#diaryEntry").css({"height":areaHeight});
      }
      function updateText() {	
      	$.ajax({
      		method: "POST",
      		url: "updatediary.php",
      		data: { content: $("#diaryEntry").val() }
      	})
      }
     </script>
  </body>
</html>