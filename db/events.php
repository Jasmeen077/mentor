<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\user_enrolment_created',
        'callback' => '\local_mentor\observer::user_enrolled',
    ],
    [
        'eventname' => '\core\event\user_enrolment_updated',
        'callback' => '\local_mentor\observer::user_enrolled',
    ],
    [
        'eventname' => '\core\event\user_enrolment_created',
        'callback' => '\local_mentor\observer::user_email',
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => '\local_mentor\observer::quiz_submit_mail',
    ],
    [
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback' => '\local_mentor\observer::assignment_submit_mail',
    ],
    [
        'eventname' => '\mod_feedback\event\response_submitted',
        'callback'  => '\local_mentor\observer::feedback_submitted',
    ],
];
