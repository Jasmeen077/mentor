<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // Add link under "Reports" section
    $ADMIN->add('reports', new admin_externalpage(
        'local_mentor_report',                     // unique name
        'Mentors Report',                          // visible name
        new \moodle_url('/local/mentor/report.php'), // link
        'moodle/site:viewreports'                  // capability
    ));

    // Users section
    $ADMIN->add('reports', new admin_externalpage(
        'local_mentor_participants',
        'Course Participants Report',
        new \moodle_url('/local/mentor/course.php'),
        'moodle/site:viewreports'
    ));
}
