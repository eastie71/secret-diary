<?php
	$dblink = mysqli_connect("localhost", "diaryadmin", "password", "secretDiaryEntries");
	if (mysqli_connect_error()) {
		echo "Database failed to connect.";
		exit(1);
	}

	function clean_input($data)
	{
		$data = trim($data);
  		$data = stripslashes($data);
  		$data = htmlspecialchars($data);
  		return $data;
	}
?>