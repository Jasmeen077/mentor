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
];
