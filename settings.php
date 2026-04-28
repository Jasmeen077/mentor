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
        new \moodle_url('/local/mentor/report/course.php'),
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

    // Create messages and subjects for the events and notifications
    $settings->add(new admin_setting_heading(
        'local_mentor/role_assigned_messages',
        'Role assigned message templates',
        'Configure role assigned and unassgined messages templates'
    ));

    // role assignment settings
    $settings->add(new admin_setting_configtext(
        'local_mentor/role_assigned_subject',
        'Role assgined subject',
        'Use variables like {firstname}, {lastname} {coursename}',
        'Hi {firstname}'
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_mentor/role_assigned_body',
        'Role assignement body',
        'Message body with placeholders like {role}, {firstname}, {lastname}, {email}, {coursename}, {courseid}',
        'Hi {firstname}, you assigned a new role ({assignmentname}) in {coursename}'
    ));

    $ADMIN->add('localplugins', $settings);
}
