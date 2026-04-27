<?php
require_once('../../../config.php');
require_once($CFG->libdir . '/tablelib.php');

global $DB, $PAGE, $OUTPUT;

$userid = required_param('userid', PARAM_INT);

require_login();

$url = new \core\url('/local/mentor/report/mentor_report_details.php', [
    'userid' => $userid
]);

$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('mentorreportdetails', 'local_mentor'));
$PAGE->set_heading(get_string('mentorreportdetails', 'local_mentor'));

echo $OUTPUT->header();

$table = new \local_mentor\report\mentor_report_details(
    'mentor_report',
    $url,
    $userid
);

$table->out(10, true);

echo $OUTPUT->footer();
