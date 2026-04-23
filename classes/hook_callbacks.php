<?php

namespace local_mentor; // Yeh exact hona chahiye

use core\hook\navigation\primary_extend;
use navigation_node;
use moodle_url;

class hook_callbacks
{

    public static function add_mentors_tab(primary_extend $hook): void
    {
        global $PAGE;

        $node = $hook->primaryview->add(
            get_string('mentors', 'local_mentor'),
            new moodle_url('/local/mentor/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_mentor_primary'
        );

        if ($node && $PAGE->url->compare(new moodle_url('/local/mentor/index.php'))) {
            $node->make_active();
        }
    }

    public static function add_learn_and_upskills_tab(primary_extend $hook)
    {
        global $DB;

        $node = $hook->primaryview->add(
            get_string('learnupskills', 'local_mentor'),
            new moodle_url('#'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_mentor_primarymenu'
        );

        // Course categories fetch
        $categories = $DB->get_records('course_categories', null, 'id,name');

        foreach ($categories as $category) {
            $node->add(
                $category->name,
                new moodle_url('/course/index.php', ['categoryid' => $category->id]),
                navigation_node::TYPE_CUSTOM
            );
        }
    }
}
