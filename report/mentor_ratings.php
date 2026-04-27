<?php
require_once('../../../config.php');
require_once($CFG->libdir . '/tablelib.php');

global $DB, $PAGE, $OUTPUT;

require_login();
$url = new \core\url('/local/mentor/report/mentor_ratings.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Mentor Rating Report');
$PAGE->set_heading('Mentor Rating Report');

echo $OUTPUT->header();

$table = new local_mentor\report\mentor_report('mentor_report', $url);

$table->out(10, true);
echo $OUTPUT->footer();
