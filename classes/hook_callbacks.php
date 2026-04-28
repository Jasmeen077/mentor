<?php

namespace local_mentor; // Yeh exact hona chahiye

use core\hook\navigation\primary_extend;
use navigation_node;
use core\url;

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

        // Add course custom participants report in the primary navigation.
        self::add_course_participants_menu($hook);
    }

    public static function add_mentors_tab(primary_extend $hook): void
    {
        global $PAGE;
        $issiteadmin = is_siteadmin();
        if ($issiteadmin) {
            $node = $hook->primaryview->add(
                get_string('mentorsreport', 'local_mentor'),
                new url('/local/mentor/report/mentor_ratings.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'local_mentor_primary'
            );
        } else {
            $node = $hook->primaryview->add(
                get_string('mentors', 'local_mentor'),
                new url('/local/mentor/index.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'local_mentor_primary'
            );
        }


        if ($node && $PAGE->url->compare(new url('/local/mentor/index.php')) || $PAGE->url->compare(new url('/local/mentor/report/mentor_ratings.php'))) {
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
            new url('#'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_mentor_primarymenu'
        );


        foreach ($categories as $category) {
            $node->add(
                $category->name,
                new url('/course/index.php', ['categoryid' => $category->id]),
                navigation_node::TYPE_CUSTOM
            );
        }
    }

    /**
     * Add course custom participants report in the primary navigation

     * @param primary_extend $hook
     * @return void
     */
    public static function add_course_participants_menu(primary_extend $hook)
    {
        global $USER, $PAGE;



        if (helper::can_access_participant_report($USER->id)) {
            $node = $hook->primaryview->add(
                get_string('courseparticipants', 'local_mentor'),
                new url('/local/mentor/report/participants.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'local_mentor_participants'
            );

            if ($node && $PAGE->url->compare(new url('/local/mentor/reprot/participants.php'))) {
                $node->make_active();
            }
        }
    }
}
