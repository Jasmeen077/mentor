<?php

namespace local_mentor; // Yeh exact hona chahiye

use core\hook\navigation\primary_extend;
use navigation_node;
use moodle_url;

class hook_callbacks
{

    /**
     * Extend primary naviagation
     * 
     * @param primary_extend $hook
     * @return void
     */
    public static function extend_navigation_primary(primary_extend $hook): void
    {
        self::add_learn_and_upskills_tab($hook);
        self::add_mentors_tab($hook);
    }

    public static function add_mentors_tab(primary_extend $hook): void
    {
        global $PAGE;
        $issiteadmin = is_siteadmin();
        if ($issiteadmin) {
            $node = $hook->primaryview->add(
                get_string('mentorsreport', 'local_mentor'),
                new moodle_url('/local/mentor/report/mentor_ratings.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'local_mentor_primary'
            );
        } else {
            $node = $hook->primaryview->add(
                get_string('mentors', 'local_mentor'),
                new moodle_url('/local/mentor/index.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'local_mentor_primary'
            );
        }


        if ($node && $PAGE->url->compare(new moodle_url('/local/mentor/index.php')) || $PAGE->url->compare(new moodle_url('/local/mentor/report/mentor_ratings.php'))) {
            $node->make_active();
        }
    }

    public static function add_learn_and_upskills_tab(primary_extend $hook)
    {
        // Course categories fetch
        $categories = \local_mentor\helper::get_upskills_categories();

        if (empty($categories)) {
            return;
        }

        $node = $hook->primaryview->add(
            get_string('learnupskills', 'local_mentor'),
            new moodle_url('#'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_mentor_primarymenu'
        );


        foreach ($categories as $category) {
            $node->add(
                $category->name,
                new moodle_url('/course/index.php', ['categoryid' => $category->id]),
                navigation_node::TYPE_CUSTOM
            );
        }
    }
}
