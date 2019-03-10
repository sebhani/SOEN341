<?php

require_once __DIR__.'/../Databases/DBinterface/DBinterface.php';
require_once 'Session.php';
require_once 'Course.php';
require_once 'Semester.php';
require_once 'heapSort.php';


class UserSchedule
{
private $firstSem;        // Input obtained from user ("F" or "W" or "S")
private $firstYear;       // Input obtained from user (int)
private $coursesPerSem;   // Input obtained from user (int)
private $listOfSemesters; // Array of semesters

public function __construct($fSem, $fYear, $numCourses)
{
  $this->firstSem = $fSem;
  $this->firstYear = $fYear;
  $this->coursesPerSem = $numCourses;
  $listOfSemesters = array ();
}

public function getListOfSemesters ()
{
  return $this->listOfSemesters;
}

public function getFirstSem ()
{
  return $this->$firstSem;
}

public function dispUserSchedule()
{
  foreach ($this->listOfSemesters as $sem)
  {
    echo "YEAR ";
    echo $sem->getYear();
    echo "----- SEMESTER  ";
    echo $sem->getName();
    echo "<br>";
    $sem->dispSemester();
    echo '<br> <br>';
  }
}

public function genProgramSched ($user)
{
  $semesters = array("W", "S","F");

 $conNoClass = new Session ("NoClass", null, null,null, array("F"), "14:15:00", "14:30:00", null);
  //  $conNoClass1 = new Session ("NoClass", null, null, null, array("F"), "17:45:00", "20:15:00", null);
  //$conNoClassArr = array ($conNoClass);
  $conNoClassArr = null;

  // Obtain untaken courses by the user
  $untakenCourses = getUntakenCourses($user);

  // Get the key for first semester in the array of semesters
  $currentSemKey = array_search($this->firstSem, $semesters);
  $currentYear = $this->firstYear;

  $flag=false;

  while (count($untakenCourses) != 0)
  {
    //var_dump($untakenCourses);

    // Update the priority of all courses unfinished
    updateAllPriority($untakenCourses);

    //var_dump($untakenCourses);

    // Get the permitted courses to be taken this semester
    $permittedCourses = getPermittedCourses ($untakenCourses, $semesters[$currentSemKey]);

    // Sort the array based on their priority
    //heap_sort($permittedCourses);


    // Generate a schedule for a semester
    $sem = new Semester ($semesters[$currentSemKey], $currentYear, $this->coursesPerSem, $conNoClassArr);
    $sem->semesterGenerator($permittedCourses);

    // DEBUG:: Use when you wish to see the scheduling of the final semesters
    /*
    if ($permittedCourses == null or $flag)
    {
      echo ("!!!!!!!!!!!!!!!!!!!!!!!!!UNTAKEN COURSES!!!!!!!!!!!!!!!!!!!!!!!!! <br>");
      var_dump($untakenCourses);
      echo ("!!!!!!!!!!!!!!!!!!!!!!!!!Permitted COURSES!!!!!!!!!!!!!!!!!!!!!!!!! <br>");

      var_dump($permittedCourses);
      var_dump($sem);
      $flag = true;
    }
    */

    $this->listOfSemesters[]= $sem;

    // Exclude the taken courses from the untaken array
    foreach ($sem->getLecs() as $taken)
    {
      updateCourseStatus($taken, $untakenCourses);
      deleteCourse($taken, $untakenCourses);
    }

    // Increment year if the current semester was fall
    if ($semesters[$currentSemKey] == "F")
      $currentYear++;

    // Increment semester
    ++$currentSemKey;
    $currentSemKey %= 3;
  }
}
}

?>
