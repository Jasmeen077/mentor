<?php
defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\navigation\primary_extend::class,
        'callback' => [\local_mentor\hook_callbacks::class, 'add_mentors_tab'],
        'priority' => 100,
    ],
    [
        'hook' => \core\hook\navigation\primary_extend::class,
        'callback' => [\local_mentor\hook_callbacks::class, 'add_learn_and_upskills_tab'],
        'priority' => 100,
    ]
];
