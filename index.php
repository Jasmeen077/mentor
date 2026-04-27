<?php

require('../../config.php');

global $DB, $USER, $OUTPUT, $PAGE;

require_login();

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/local/mentor/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('mentorslist', 'local_mentor'));
$PAGE->set_heading(get_string('mentorslist', 'local_mentor'));
$PAGE->set_pagelayout('standard');

$output = $PAGE->get_renderer('local_mentor');

echo $OUTPUT->header();
echo $output->render_mentor_cards();
echo $OUTPUT->footer();
