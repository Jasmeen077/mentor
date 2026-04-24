<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new admin_settingpage('local_mentor', get_string('pluginname', 'local_mentor'));

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

    $choices = \core_course_category::make_categories_list();

    $settings->add(new admin_setting_configmultiselect(
        'local_mentor/upandskillscategories',
        get_string('upandskillscategories', 'local_mentor'),
        get_string('upandskillscategories_description', 'local_mentor'),
        $choices,
        $choices,
    ));

    $ADMIN->add('localplugins', $settings);
}
