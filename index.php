<?php

require('../../config.php');

global $DB, $USER, $OUTPUT, $PAGE;

require_login();

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/local/mentor/index.php'));
$PAGE->set_context($context);
$PAGE->set_title('Mentor Feedback');
$PAGE->set_pagelayout('standard');
$PAGE->requires->css('/local/mentor/styles.css');


$sql = "SELECT DISTINCT u.id,
                       u.firstname,
                       u.lastname,
                       u.email,
                       u.department,
                       r.shortname AS role,
                       GROUP_CONCAT(t.name SEPARATOR ', ') AS interests
        FROM {user} u
        JOIN {role_assignments} ra ON ra.userid = u.id
        JOIN {context} ctx ON ctx.id = ra.contextid
        JOIN {course} c ON c.id = ctx.instanceid
        JOIN {enrol} e ON e.courseid = c.id
        JOIN {user_enrolments} ue ON ue.enrolid = e.id
        JOIN {role} r ON r.id = ra.roleid
        LEFT JOIN {tag_instance} ti ON ti.itemid = u.id
             AND ti.component = 'core'
             AND ti.itemtype = 'user'
        LEFT JOIN {tag} t ON t.id = ti.tagid
        WHERE ra.roleid = 3
          AND ctx.contextlevel = 50
          AND ue.userid = :userid
        GROUP BY u.id";
$mentors = $DB->get_records_sql($sql, ['userid' => $USER->id]);

// Renderer
$output = $PAGE->get_renderer('local_mentor');

// Page output
echo $OUTPUT->header();
// Mentor cards
echo $output->render_mentor_cards($mentors);

echo $OUTPUT->footer();
