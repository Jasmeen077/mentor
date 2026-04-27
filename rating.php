<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/mentor/classes/form/mentor_form.php');

global $DB, $OUTPUT, $PAGE;

require_login();

$userid = required_param('userid', PARAM_INT);

$PAGE->set_url(new moodle_url('/local/mentor/rating.php', ['userid' => $userid]));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('rate_mentors', 'local_mentor'));
$PAGE->set_heading(get_string('rate_mentors', 'local_mentor'));

$courses = \local_mentor\mentor::get_courses_list_for_rating($userid);

if (empty($courses)) {
    throw new moodle_exception('duplicate', 'local_mentor');
}

$mform = new \local_mentor\form\mentor_form(null, ['userid' => $userid, 'courses' => $courses]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/mentor/index.php'));
} elseif ($data = $mform->get_data()) {

    if (local_mentor\mentor::save_rating($data->userid, $data->courseid, $data->rating, $data->reason)) {
        redirect(new moodle_url('/local/mentor/index.php'), get_string('feedbacksaved', 'local_mentor'), null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        redirect(new moodle_url('/local/mentor/rating.php', ['userid' => $userid]), get_string('feedbacksaveerror', 'local_mentor'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

echo $OUTPUT->header();
echo html_writer::tag('p', get_string('welcome', 'local_mentor'), ['class' => 'mentor-heading']);
$mform->display();
echo $OUTPUT->footer();
