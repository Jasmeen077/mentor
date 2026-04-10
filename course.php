<?php
require('../../config.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
require_capability('moodle/site:viewreports', $context);

$PAGE->set_url(new moodle_url('/local/mentor/course.php'));
$PAGE->set_pagelayout('report');
$PAGE->set_title('Course Participants Report');
$PAGE->set_heading('Course Participants Report');

global $DB, $OUTPUT;

echo $OUTPUT->header();

// Download parameter
$download = optional_param('download', '', PARAM_ALPHA);

// Table setup (without the "role" column)
$table = new flexible_table('participants_report');
$table->define_columns(['name', 'email', 'course']);
$table->define_headers(['Name', 'Email', 'Course Name']);
$table->define_baseurl($PAGE->url);
$table->set_attribute('class', 'generaltable');
$table->setup();

// SQL Query to get unique users with their courses and roles
$sql = "SELECT 
            u.id AS userid,
            u.firstname,
            u.lastname,
            u.email,
            c.fullname AS course_name,
            r.shortname AS role
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id
        JOIN {enrol} e ON e.id = ue.enrolid
        JOIN {course} c ON c.id = e.courseid
        JOIN {role_assignments} ra ON ra.userid = u.id
        JOIN {context} ctx ON ctx.id = ra.contextid 
            AND ctx.contextlevel = 50 
            AND ctx.instanceid = c.id
        JOIN {role} r ON r.id = ra.roleid
        ORDER BY u.firstname, c.fullname";

// CSV Download
if ($download === 'csv') {
    $records = $DB->get_records_sql($sql);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="participants_report.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Email', 'Course Name', 'Role']);
    foreach ($records as $r) {
        fputcsv($out, [
            $r->firstname . ' ' . $r->lastname,
            $r->email,
            $r->course_name,
            $r->role
        ]);
    }
    fclose($out);
    exit;
}

// Fetch records for table
$records = $DB->get_recordset_sql($sql);

// Array to hold the courses and roles for each user
$user_courses_roles = [];

foreach ($records as $r) {
    $user_id = $r->userid;

    // Group by user and store their courses and roles
    if (!isset($user_courses_roles[$user_id])) {
        $user_courses_roles[$user_id] = [
            'name' => $r->firstname . ' ' . $r->lastname,
            'email' => $r->email,
            'courses' => []
        ];
    }

    // Add course and role to the user's list
    $user_courses_roles[$user_id]['courses'][$r->course_name][] = $r->role;
}

// Generate table rows based on the grouped data
foreach ($user_courses_roles as $user_id => $user_data) {
    // Make the user's name clickable, linking to their profile
    $profileurl = new moodle_url('/user/view.php', ['id' => $user_id]);
    $name = '<a href="' . $profileurl . '" style="color:#e10018; text-decoration:none;">' . $user_data['name'] . '</a>';

    $email = $user_data['email'];

    // Loop through each course for the user
    $courses_html = '';
    foreach ($user_data['courses'] as $course_name => $roles) {
        $courses_html .= '<div><strong>' . $course_name . '</strong></div>';

        // Display each role for the course below the course name
        foreach ($roles as $role) {
            $courses_html .= '<div style="color:green;">' . $role . '</div>';
        }
    }

    $table->add_data([$name, $email, $courses_html]);
}

$records->close();

// Download button
$downloadurl = new moodle_url($PAGE->url, ['download' => 'csv']);
echo $OUTPUT->single_button($downloadurl, 'Download Participants Report');

$table->print_html();

echo $OUTPUT->footer();
