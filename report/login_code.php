<?php

if (version_compare(phpversion(), '5.4.0', '<')) {
    if (session_id() == '') {
	session_start();
    }
} else {
    if (session_status() == PHP_SESSION_NONE) {
	session_start();
    }
}

//require_once 'functions.php';



if (isset($_POST['email'])) {
	
    $username = isset($_POST['email']) ? $_POST['email'] : "";
    $password = isset($_POST['password']) ? $_POST['password'] : "";
	
	
    if ($username == "") 
	{

	$error = 'Username required !!!';
    } 
	
	
	else if ($password == "") {
		
	$error = 'Password required !!!';
    } 
	else {
		
	$result = checkUserExist($username, $password);
	//echo count($result);exit;
	
	
	if (count($result) > 0) {
		
	    $_SESSION['svmobiadmin'] = $result[0]['id'];
	    $_SESSION['svmobiadmin_username'] = $result[0]['username'];
	    $_SESSION['svmobiadmin_email'] = $result[0]['email'];
	    header("location:index.php");
	} else {
	    $error = 'Username / Password incorrect';
	}
    }
}


function checkUserExist($username, $password) {
	
		include 'includes/db_details.php';
		//echo "$db";exit;
		
		//echo "Hi";exit;
		 $sql="SELECT userid id, username, email FROM ".$db.".login_username WHERE (username='".$username."' or email='".$username."')  and password='".md5($password)."'";
		$result1 = $conn->query($sql);
			//$result=$result1->num_rows;
			$result = array();
		while ($row = $result1->fetch_assoc()) {
			  $result[] = $row;
			}
				//echo "hi";exit;
		return $result;
	
}