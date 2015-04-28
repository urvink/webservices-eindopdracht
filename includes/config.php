<?php
/**
 * Created by PhpStorm.
 * User: U. Kirindongo
 * Date: 1/14/14
 * Time: 9:07 AM
 */

$host = 'localhost';
$db = '0756219';
$username = 'root';
$password = '';

$link = mysqli_connect($host,$username,$password,$db);
/* check connection */
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}
