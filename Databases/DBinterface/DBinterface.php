<?php
	//include("Session.php");

static $createdCourses = array();

function getLectureSections($course, $semester){
	require('config/db.php');		require('config/db.php');		require('config/db.php');		require('config/db.php');
	$stack=array();

	static $table ;

	switch ($semester)
	{
		case 'F':
		global $table;
		$table = 'flec';
		break;
		case 'W':
		global $table;
		$table = 'wlec';
		break;
		case 'S':
		global $table;
		$table = 'slec';
		break;
		default:
		global $table;
		$table = 'error';
	}

		//Create query
	$query = "SELECT * FROM `$table` WHERE `CourseName`='$course'";

		//Get Result
	$result = mysqli_query($conn, $query);

		//Fetch Data
	$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

		// Free Result
	mysqli_free_result($result);

		//Instantiating the Fall semster
	foreach($posts as $post)
	{
		$courseName = $post['CourseName'];
		$lecInfo = $post ['LecInfo'];
		$subSection = null;
		$lecDay = explode(",",$post ['LecDay']);
		$startLecTime= $post ['StartLecTime'];
		$endLecTime= $post ['EndLecTime'];
		$campus = "SGW";

			//Making a new session object with the course information
		$ham =  new Session($courseName,$lecInfo,$subSection,$semester,$lecDay,$startLecTime,$endLecTime,$campus);

		array_push($stack, $ham);
	}
	mysqli_close($conn);

	return $stack;
}


function getTutorialSection($course, $semester, $section){
		//include('tutorialfunction.php');
	require('config/db.php');

	$stack=array();

	static $table ;

	switch ($semester)
	{
		case 'F':
		global $table;
		$table = 'ftut';
		break;
		case 'W':
		global $table;
		$table = 'wtut';
		break;
		case 'S':
		global $table;
		$table = 'stut';
		break;
		default:
		global $table;
		$table = 'error';
	}

		//Create query
	$query = "SELECT * FROM `$table` WHERE `CourseName`='$course' AND `LecInfo`='$section'";


		//Get Result
	$result = mysqli_query($conn, $query);

		//Fetch Data
	$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

		// Free Result
	mysqli_free_result($result);

		//Instantiating the Fall semster
	foreach($posts as $post)
	{
		$courseName = $post['CourseName'];
		$lecInfo = $post ['LecInfo'];
		$subSection = $post['TutSection'];
		$lecDay = explode(",",$post ['TutDay']);
		$startLecTime= $post ['StartTutTime'];
		$endLecTime= $post ['EndTutTime'];
		$campus = "SGW";

			//Making a new session object with the course information
		$ham =  new Session($courseName,$lecInfo,$subSection,$semester,$lecDay,$startLecTime,$endLecTime,$campus);

		array_push($stack, $ham);
	}
	mysqli_close($conn);
	return $stack;

}

function getLabSection($course, $semester){

	require('config/db.php');
	$stack=array();
	static $table;
	switch ($semester)
	{
		case 'F':
		global $table;
		$table = 'flab';
		break;
		case 'W':
		global $table;
		$table = 'wlab';
		break;
		case 'S':
		global $table;
		$table = 'slab';
		break;
		default:
		global $table;
		$table = 'error';
	}

	//Create query
	$query = "SELECT * FROM `$table` WHERE `CourseName`='$course'";

	//Get Result
	$result = mysqli_query($conn, $query);

	//Fetch Data
	$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

	// Free Result
	mysqli_free_result($result);

	//Instantiating the Fall semster
	foreach($posts as $post)
	{
		$courseName = $post['CourseName'];
		$labSection = $post ['LabSection'];
		$labDay = explode(",",$post ['LabDay']);
		$startLabTime= $post ['StartLabTime'];
		$endLabTime= $post ['EndLabTime'];
		$campus = "SGW";

		//Making a new session object with the course information
		$ham =  new Session($courseName,$labSection,null,$semester,$labDay,$startLabTime,$endLabTime,$campus);

		array_push($stack, $ham);
	}

	mysqli_close($conn);
	return $stack;
}

	//Returns an array of Courses objects this user can take this semester from the remaining courses array of courses
	// DO NOT GENERATE NEW COURSES OBJECTS AND USE THE SAME ELEMENTS FROM $remainingCourses
function getPermittedCourses($remainingCourses,  $semester)
{
	require('config/db.php');

	global $createdCourses;

	$stack = array();
	static $table;
	switch ($semester)
	{
		case 'F':
		global $table;
		$table = 'flec';
		break;
		case 'W':
		global $table;
		$table = 'wlec';
		break;
		case 'S':
		global $table;
		$table = 'slec';
		break;
		default:
		global $table;
		$table = 'error';
	}
		// Check if pre-requesites are satisfied
	foreach ($remainingCourses as $untaken)
	{
		if ($untaken->getPreReqs() != null)
		{
			foreach($untaken->getPreReqs() as $pre)
			{
				if (!$pre->getPass())
					continue 2;
			}
		}

			// Check if offered in $semester
		$courseName =	$untaken->getCourseName();

			//Create query
		$query = "SELECT * FROM `$table` WHERE `CourseName`='$courseName'";

			//Get Result
		$result = mysqli_query($conn, $query);

			//Fetch Data
		$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

		if (count($posts) != 0)
			array_push($stack, $untaken);
	}

	return $stack;
}


	//Returns an array of Courses objects for all the courses that the user did not take yet
	//$user will pass the course that user has taken by array
function getUntakenCourses($email)
{
		require('config/db.php');

		global $createdCourses;

		$query = 'select CourseName from coursesmain';
		$result = mysqli_query($conn, $query);
		$courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
		mysqli_free_result($result);

		$query = 'select UserId from users where Email="'.$email.'"';
		$result = mysqli_query($conn, $query);
	//$userID = mysqli_fetch_all($result, MYSQLI_ASSOC);
		$data = mysqli_fetch_array($result);
	//mysqli_free_result($data);
		$userId = $data['UserId'];

	//var_dump($data);
		if(empty($userId))
			return false;

		$query = "select CourseName from pass where UserId=$userId";
		$result = mysqli_query($conn, $query);
		$passedCourses = mysqli_fetch_all($result, MYSQLI_ASSOC);
		mysqli_free_result($result);

		for($i=0;$i<count($passedCourses);$i++){
			$takenCourse = $passedCourses[$i];
			$courseName = $passedCourses[$i]['CourseName'];
			$createdCourses[$courseName] = getCourse($courseName);
			$createdCourses[$courseName]->setPass(true);

			for($j=0;$j<count($courses);$j++){
				if($courses[$j] == $takenCourse)
				{
					array_splice($courses,$j,1);
				}
			}
		}

		$untakenCourses;
		for($i=0;$i<count($courses);$i++){
			$untakenCourses[$i] = getCourse($courses[$i]['CourseName']);
		}

		return $untakenCourses;

}

//Updates the database by changing the status of the courses recently taken
function updateTakenCourses($email,$courseName)
{
				require('config/db.php');
				echo false;
				echo true;
		$query = 'select UserId from users where Email="'.$email.'"';
		$result = mysqli_query($conn, $query);
	//$userID = mysqli_fetch_all($result, MYSQLI_ASSOC);
		$data = mysqli_fetch_array($result);
	//mysqli_free_result($data);
		$userId = $data['UserId'];
		if(empty($userId))
			return false;
		$untaken=getUntakenCourses($email);
		var_dump($untaken);
		static	$counter=0;
		$found=false;
		foreach ($untaken as $untook) {
			$courseUntakenName=$untook['CourseName'];
 			if($courseUntakenName==$courseName){
 			global $found;
 			 $found=true;
 			}
		}
		global $found;
 		if($found===true){
		$query = 'insert into pass (UserId, CourseName) values ('.$userId.',"'.$courseName.'")';
		$result = mysqli_query($conn, $query);
		}else{
			echo "This course has been already added";
		}

}

function getCourse($courseName){

	require('config/db.php');

	global $createdCourses;

	if (array_key_exists($courseName, $createdCourses))
		return $createdCourses[$courseName];

	else {

		$query ="SELECT *FROM `coursesmain` WHERE `CourseName`= '$courseName'";

		$result = mysqli_query($conn, $query);

			//Fetch Data
		$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
		$courseName = $posts[0]['CourseName'];
		$preReqs_str = explode(",",$posts[0] ['Prerequisite']);
		$coReqs_str = explode(",", $posts[0] ['Corerequisite']);
		$credits = (float) $posts[0]['Credit'];

		$preReqs = null;
		$coReqs = null;
		if ($preReqs_str[0] != null)
		{
			$preReqs = array ();
			foreach ($preReqs_str as $p)
				array_push($preReqs, getCourse($p));
		}

		if ($coReqs_str[0] != null)
		{
			$coReqs = array ();
			foreach ($coReqs_str as $c)
				array_push($coReqs, getCourse($c));
		}
		$course = new Course ($courseName, $preReqs, $coReqs, $credits, false, false);
		$createdCourses[$courseName]=$course;
		return $course;
	}
}

function getID($email){

	require('config/db.php');

	$query1 ="SELECT L.user_id FROM login L INNER JOIN `course` C ON C.`courseid` = L.`user_id` WHERE L.`email`='$email'";

	$result1 = mysqli_query($conn1, $query1);

	    //Fetch Data
	$posts1 = mysqli_fetch_all($result1, MYSQLI_ASSOC);

	return $posts1;
}

//getCourse('COMP232');

?>
</body>
</html>