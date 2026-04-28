<?php
require('../../../config.php');

require_login();

$userid = optional_param('userid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$context = context_system::instance();
$PAGE->set_context($context);

if (!\local_mentor\helper::can_access_participant_report($USER->id)) {
    throw new \core\exception\access_denied_exception(get_string('permission_denied', 'local_mentor'));
}
$url = new \core\url('/local/mentor/report/participants.php');

if ($confirm and confirm_sesskey()) {
    local_mentor\helper::unenrol_user_in_course($userid, $courseid, $url);
}

$PAGE->set_title(get_string('courseparticipants', 'local_mentor'));
$PAGE->set_heading(get_string('courseparticipants', 'local_mentor'));

// confirmation box
if ($userid && $courseid) {
    $nourl = new core\url($url->out());
    $url->params(['courseid' => $courseid, 'userid' => $userid, 'confirm' => 1, 'sesskey' => sesskey()]);

    $course = $DB->get_field('course', 'fullname', ['id' => $courseid]);

    $PAGE->set_url($url);

    $a = new stdClass();
    $a->fullname = fullname(core\user::get_user($userid));
    $a->course = format_string($course);
    $message = get_string('unenrolconfirm', 'local_mentor', $a);

    echo $OUTPUT->header();
    echo $OUTPUT->confirm($message, $url, $nourl);
    echo $OUTPUT->footer();
    exit;
}

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');

echo $OUTPUT->header();

$table = new \local_mentor\report\course_participants_table('participants_report', $url);

$table->out(20, true); // 20 records per page

echo $OUTPUT->footer();
