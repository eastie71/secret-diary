<?php
	include_once "diarydb.php";
	session_start();

	if (array_key_exists("content", $_POST)) {

		$session_id = mysqli_real_escape_string($dblink,$_SESSION['id']);
		$content = mysqli_real_escape_string($dblink, $_POST["content"]);

		$query = "UPDATE `diaryUserEntries` SET `diaryEntry` = '$content' WHERE id = $session_id LIMIT 1";
		mysqli_query($dblink, $query);
		
	}

?>