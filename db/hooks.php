<?php
defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\navigation\primary_extend::class,
        'callback' => [\local_mentor\hook_callbacks::class, 'extend_navigation_primary'],
        'priority' => 100,
    ]
];
