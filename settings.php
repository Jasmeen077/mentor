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

    // Set admin email to for recieving all messages copy
    $admins = get_admins();
    foreach ($admins as $admin) {
        $options[$admin->id] = fullname($admin);
    }

    $settings->add(new admin_setting_configmultiselect(
        'local_mentor/admin_notification_receiver',
        get_string('admin_notification_receiver', 'local_mentor'),
        get_string('admin_notification_receiver_desc', 'local_mentor'),
        $options,
        $options,
    ));

    $choices = \core_course_category::make_categories_list();

    $settings->add(new admin_setting_configmultiselect(
        'local_mentor/upandskillscategories',
        get_string('upandskillscategories', 'local_mentor'),
        get_string('upandskillscategories_description', 'local_mentor'),
        $choices,
        $choices,
    ));
    // Role Assigned
    $settings->add(new admin_setting_heading(
        'local_mentor/role_assigned_messages',
        get_string('role_assigned_messages', 'local_mentor'),
        get_string('role_assigned_messages_desc', 'local_mentor')
    ));

    $settings->add(new admin_setting_configtext(
        'local_mentor/role_assigned_subject',
        get_string('role_assigned_subject', 'local_mentor'),
        get_string('role_assigned_subject_desc', 'local_mentor'),
        get_string('role_assigned_subject_template', 'local_mentor')
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_mentor/role_assigned_body',
        get_string('role_assigned_body', 'local_mentor'),
        get_string('role_assigned_body_desc', 'local_mentor'),
        get_string('role_assigned_body_template', 'local_mentor')
    ));


    // Role Unassigned
    $settings->add(new admin_setting_heading(
        'local_mentor/role_unassigned_messages',
        get_string('role_unassigned_messages', 'local_mentor'),
        get_string('role_unassigned_messages_desc', 'local_mentor')
    ));

    $settings->add(new admin_setting_configtext(
        'local_mentor/role_unassigned_subject',
        get_string('role_unassigned_subject', 'local_mentor'),
        get_string('role_unassigned_subject_desc', 'local_mentor'),
        get_string('role_unassigned_subject_template', 'local_mentor')
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_mentor/role_unassigned_body',
        get_string('role_unassigned_body', 'local_mentor'),
        get_string('role_unassigned_body_desc', 'local_mentor'),
        get_string('role_unassigned_body_template', 'local_mentor')
    ));


    // Quiz Completion
    $settings->add(new admin_setting_heading(
        'local_mentor/attempt_submitted_message',
        get_string('attempt_submitted_message', 'local_mentor'),
        get_string('attempt_submitted_message_desc', 'local_mentor')
    ));

    $settings->add(new admin_setting_configtext(
        'local_mentor/attempt_submitted_subject',
        get_string('attempt_submitted_subject', 'local_mentor'),
        get_string('attempt_submitted_subject_desc', 'local_mentor'),
        get_string('attempt_submitted_subject_template', 'local_mentor')
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_mentor/attempt_submitted_body',
        get_string('attempt_submitted_body', 'local_mentor'),
        get_string('attempt_submitted_body_desc', 'local_mentor'),
        get_string('attempt_submitted_body_template', 'local_mentor')
    ));


    // Assignment Completion
    $settings->add(new admin_setting_heading(
        'local_mentor/assessable_submitted_message',
        get_string('assessable_submitted_message', 'local_mentor'),
        get_string('assessable_submitted_message_desc', 'local_mentor')
    ));

    $settings->add(new admin_setting_configtext(
        'local_mentor/assessable_submitted_subject',
        get_string('assessable_submitted_subject', 'local_mentor'),
        get_string('assessable_submitted_subject_desc', 'local_mentor'),
        get_string('assessable_submitted_subject_template', 'local_mentor')
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_mentor/assessable_submitted_body',
        get_string('assessable_submitted_body', 'local_mentor'),
        get_string('assessable_submitted_body_desc', 'local_mentor'),
        get_string('assessable_submitted_body_template', 'local_mentor')
    ));

    // Feedback Completion

    $settings->add(new admin_setting_heading(
        'local_mentor/response_submitted_message',
        get_string('response_submitted_message', 'local_mentor'),
        get_string('response_submitted_message_desc', 'local_mentor')
    ));

    $settings->add(new admin_setting_configtext(
        'local_mentor/response_submitted_subject',
        get_string('response_submitted_subject', 'local_mentor'),
        get_string('response_submitted_subject_desc', 'local_mentor'),
        get_string('response_submitted_subject_template', 'local_mentor')
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_mentor/response_submitted_body',
        get_string('response_submitted_body', 'local_mentor'),
        get_string('response_submitted_body_desc', 'local_mentor'),
        get_string('response_submitted_body_template', 'local_mentor')
    ));

    $ADMIN->add('localplugins', $settings);
}
