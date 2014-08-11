<?php
/**
 * Created by PhpStorm.
 * User: U. Kirindongo
 * Date: 1/14/14
 * Time: 9:07 AM
 */

$host = 'localhost';
$db = '0756219';


if($_SERVER['HTTP_HOST'] == 'localhost'){
	$username = 'root';
	$password = '';
}
else{
	$username = '0756219';
	$password = 'mandidios';
}

$link = mysqli_connect($host,$username,$password,$db);
//print_r($username.' '.$password);
/* check connection */
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}