<?php 
//create connection
$conn = mysqli_connect('localhost','root','root','sequencebuilder');
//check connection
if(mysqli_connect_errno()){
	//connection Failed
	echo 'Failed to connect'.mysqli_connect_errno();

}
//create connection
$conn1 = mysqli_connect('localhost','root','root','sequencebuilder');
//check connection
if(mysqli_connect_errno()){
	//connection Failed
	echo 'Failed to connect'.mysqli_connect_errno();

}

 ?>