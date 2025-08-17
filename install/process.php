<?php
$errors = [];

if (!empty($_POST)) {
	$conn = new mysqli(
		$_POST['MYSQL_HOST'],
		$_POST['MYSQL_USERNAME'],
		$_POST['MYSQL_PASSWORD'],
		$_POST['MYSQL_DATABASE']);

	// Check connection
	if ($conn->connect_error) {
		$errors[] = $conn->connect_error;
	} else {
		try {
			$myfile = fopen("../app/constants.php", "a");
			$txt    = "\n\n";

			foreach ($_POST as $key => $value) {
				$txt .= "define('$key', '$value');\n\n";
			}
			
			fwrite($myfile, $txt);
			fclose($myfile);

			$sql = file_get_contents('../database.sql');

			if ($conn->multi_query($sql) !== TRUE) {
				$errors[] = $conn->error;
			} else {
				header("Location: index.php");
			}
		} catch (Exception $e) {
			echo $e;
		}
	}
}
?>