<?php
require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');

global $DB, $PAGE, $OUTPUT;

require_login();

$PAGE->set_url(new moodle_url('/local/mentor/report.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Mentor Rating Report');
$PAGE->set_heading('Mentor Rating Report');

echo $OUTPUT->header();

// Table setup
$table = new flexible_table('mentor_rating_report');
$table->define_columns(['fullname', 'department', 'rating']);
$table->define_headers(['Mentor Name', 'Department', 'Average Rating']);
$table->define_baseurl(new moodle_url('/local/mentor/report.php'));
$table->set_attribute('class', 'generaltable');
$table->setup();

// SQL
$sql = "SELECT DISTINCT u.id,
               u.firstname,
               u.lastname,
               u.department,
               AVG(m.rating) AS averagerating
        FROM {user} u
        JOIN {role_assignments} ra ON ra.userid = u.id
        JOIN {role} r ON r.id = ra.roleid
        LEFT JOIN {local_mentor} m ON m.userid = u.id
        WHERE r.shortname IN ('editingteacher', 'teacher')
        GROUP BY u.id, u.firstname, u.lastname, u.department";

$mentors = $DB->get_records_sql($sql);

// Data
foreach ($mentors as $mentor) {
    $fullname = $mentor->firstname . ' ' . $mentor->lastname;
    $avg = $mentor->averagerating ? round($mentor->averagerating, 1) : 0;
    $stars = str_repeat('★', round($avg)) . str_repeat('☆', 5 - round($avg));

    $table->add_data([
        $fullname,
        $mentor->department,
        $stars . ' (' . $avg . ')'
    ]);
}

$table->finish_output();

echo $OUTPUT->footer();
