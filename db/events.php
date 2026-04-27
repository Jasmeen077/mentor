<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    // User enrol or unerol events
    [
        'eventname' => '\core\event\user_enrolment_created',
        'callback' => '\local_mentor\observer\enroll::user_enrolled',
    ],
    [
        'eventname' => '\core\event\user_enrolment_deleted',
        'callback' => '\local_mentor\observer\enroll::user_unenrolled',
    ],

    // Role assigned events
    [
        'eventname' => '\core\event\role_assigned',
        'callback' => '\local_mentor\observer::user_email',
    ],

    // Activity events
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => 'local_mentor\observer::quiz_submit_mail',
    ],
    [
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback' => 'local_mentor\observer::assignment_submit_mail',
    ],
    [
        'eventname' => '\mod_feedback\event\response_submitted',
        'callback'  => '\local_mentor\observer::feedback_submitted',
    ],
];
