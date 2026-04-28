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

    // settings for quiz
    $settings->add(new admin_setting_heading(
        'local_mentor/attempt_submitted_message',
        'Quiz completion message template',
        'Configure message shown when a user completes a quiz'
    ));

    $settings->add(new admin_setting_configtext(
        'local_mentor/attempt_submitted_subject',
        'Quiz completion subject',
        'Use variables like {firstname}, {lastname}, {quizname}, {coursefullname}',
        'Quiz completed: {quizname}'
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_mentor/attempt_submitted_body',
        'Quiz completion message body',
        'Placeholders: {firstname}, {lastname}, {quizname}, {coursename}, {attemptid}',
        'Hi {firstname}, congratulations! You have successfully completed the quiz <b>{quizname}</b> in course <b>{coursefullname}</b>. Your final grade is <b>{finalgrade}</b>.'
    ));

    //settings for assignment
    $settings->add(new admin_setting_heading(
        'local_mentor/assessable_submitted_message',
        'Assignment completion message template',
        'Configure message shown when a user completes an assignment'
    ));

    $settings->add(new admin_setting_configtext(
        'local_mentor/assessable_submitted_subject',
        'Assignment completion subject',
        'Use variables like {firstname}, {lastname}, {assignmentname}, {coursename}',
        'Assignment completed: {assignmentname}'
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_mentor/assessable_submitted_body',
        'Assignment completion message body',
        'Placeholders: {firstname}, {lastname}, {assignmentname}, {coursename}, {grade}, {feedback}',
        'Hi {firstname}, congratulations! You have successfully completed the assignment <b>{assignmentname}</b> in course <b>{coursefullname}</b>. Your grade is <b>{grade}</b>.'
    ));

    // settings for feedback
    $settings->add(new admin_setting_heading(
        'local_mentor/feedback_submission_message',
        'Feedback submission message template',
        'Configure message sent when feedback is submitted'
    ));

    $settings->add(new admin_setting_configtext(
        'local_mentor/feedback_submit_subject',
        'Feedback submission subject',
        'Use variables like {firstname}, {lastname}, {coursefullname}',
        'Feedback submitted successfully'
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_mentor/feedback_body',
        'Feedback Email Body',
        'Use placeholders like {firstname}, {lastname}, {coursename}, {activityname}',
        'Dear {firstname},<br><br>
        Your feedback has been successfully submitted in <b>{activityname}</b> of course <b>{coursename}</b>.<br><br>
        Thank you for your participation.<br><br>
        Regards,<br>
        LMS Team'
    ));

    $ADMIN->add('localplugins', $settings);
}
