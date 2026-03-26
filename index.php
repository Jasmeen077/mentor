<?php

require('../../config.php');
require_once($CFG->dirroot . '/local/mentor/classes/mentor_queries.php');

global $DB, $USER, $OUTPUT, $PAGE;

require_login();

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/local/mentor/index.php'));
$PAGE->set_context($context);
$PAGE->set_title('Mentor Feedback');
$PAGE->set_pagelayout('standard');
$PAGE->requires->css('/local/mentor/styles.css');

$mentors = \local_mentor\mentor_queries::get_mentors($USER->id);
// $mentors = $DB->get_records_sql($sql, ['userid' => $USER->id]);
foreach ($mentors as $mentor) {
  $mentor->bio = strip_tags($mentor->bio);
  $mentor->expertise = strip_tags($mentor->expertise);

  $rating = round($mentor->averagerating);
  $mentor->stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);

  $fieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'ratings']);

  $existing = $DB->get_record('user_info_data', [
    'userid' => $mentor->id,
    'fieldid' => $fieldid
  ]);

  $profiledata = new stdClass();
  $profiledata->userid = $mentor->id;
  $profiledata->fieldid = $fieldid;
  $profiledata->data = $mentor->averagerating;

  if ($existing) {
    $profiledata->id = $existing->id;
    $DB->update_record('user_info_data', $profiledata);
  } else {
    $DB->insert_record('user_info_data', $profiledata);
  }
}

// Renderer
$output = $PAGE->get_renderer('local_mentor');

// Page output
echo $OUTPUT->header();
// Mentor cards
echo $output->render_mentor_cards($mentors);

echo $OUTPUT->footer();
