<?php
require('../../config.php');

require_login();

// =========================
// CONTEXT (IMPORTANT)
// =========================
$context = context_system::instance();
$PAGE->set_context($context);

require_capability('moodle/site:viewreports', $context);

// =========================
// PAGE SETUP
// =========================
$PAGE->set_url('/local/mentor/course.php');
$PAGE->set_pagelayout('report');
$PAGE->set_title('Course Participants Report');
$PAGE->set_heading('Course Participants Report');

echo $OUTPUT->header();

// =========================
// TABLE
// =========================
$table = new \local_mentor\report\course_participants_table('participants_report');

$table->out(20, true); // 20 records per page

echo $OUTPUT->footer();
