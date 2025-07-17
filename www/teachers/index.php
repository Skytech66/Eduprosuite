<?php
	if (isset($_GET['dashboard'])) {
	include 'dashboard.php';
	}
	 
	if (isset($_GET['exams'])) {
	  include 'exams.php';
	}

	if (isset($_GET['subjects'])) {
	 include 'subjects.php';
	}
	if (isset($_GET['exam_scores'])) {
	 include 'exam_scores.php';
	}

	if (isset($_GET['sms'])) {
	include 'sms.php';
	}

	if (isset($_GET['email_login'])) {
	include 'email_login.php';
	}

	if (isset($_GET['students_report'])) {
	include 'students_report.php';
	}
	if (isset($_GET['lesson_notes'])) {
	 include 'logginn.php';
	}
    if (isset($_GET['login'])) {
	 include 'login.php';
	}
	if (isset($_GET['form'])) {
	 include 'form.php';
	}
	if (isset($_GET['class_merit'])) {
	include 'class_merit.php';
	}
	
	if (isset($_GET['view_students'])) {
	include 'view_students.php';
	}

	if (isset($_GET['subjects_report'])) {
	include 'subjects_report.php';
	}
     if (isset($_GET['lo'])) {
	include 'lo.php';
}

if (isset($_GET['logg'])) {
	include 'logg.php';
}
if (isset($_GET['messages'])) {
	include 'messages.php';
}



?>
