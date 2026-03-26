<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/mentor/classes/form/mentor_form.php');

global $DB, $USER, $OUTPUT, $PAGE;

require_login();

$userid = required_param('userid', PARAM_INT);

$PAGE->set_url(new moodle_url('/local/mentor/rating.php', ['userid' => $userid]));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Rate the Mentors');

$mform = new \local_mentor\form\mentor_form(null, ['userid' => $userid]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/my'));
} elseif ($data = $mform->get_data()) {

    $record = new stdClass();
    $record->userid = $data->userid;
    $record->courseid = $data->courseid;
    $record->rating = $data->rating;
    $record->timecreated = time();
    $record->timemodified = time();

    try {
        $mentorid = $DB->insert_record('local_mentor', $record);

        $log = new stdClass();
        $log->mentor_id = $mentorid;
        $log->rate = $data->rating;
        $log->reason = $data->reason;
        $log->timecreated = time();

        $DB->insert_record('local_mentor_rates_log', $log);

        redirect(new moodle_url('/my'), 'Feedback submitted successfully');
    } catch (\dml_exception $e) {
        echo $OUTPUT->header();
        echo $OUTPUT->notification('Error saving feedback: ' . $e->getMessage(), 'notifyproblem');
        $mform->display();
        echo $OUTPUT->footer();
        exit;
    }
}


echo $OUTPUT->header();
echo html_writer::tag('p', get_string('welcome', 'local_mentor'), ['class' => 'mentor-heading']);
$mform->display();
echo $OUTPUT->footer();
