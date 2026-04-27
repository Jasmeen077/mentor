<?php

namespace local_mentor\report;

require_once($CFG->libdir . '/tablelib.php');

use core_table\sql_table;
use html_writer;
use core\url as moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Mentor Report Details Table Class
 * 
 * @package local_mentor
 * @author Mohan Lal Sharma <mohan.sharma@idslms.com> April 2026
 */
class mentor_report_details extends sql_table
{
    public function __construct(string $uniqueid, moodle_url| string $baseurl)
    {
        parent::__construct($uniqueid);

        $headers = [
            'name' => get_string('name'),
            'email' => get_string('email'),
            'course' => get_string('courses'),
            'rating' => get_string('rating', 'local_mentor'),
            'timecreated' => get_string('timecreated', 'local_mentor')
        ];

        $this->define_columns(array_keys($headers));
        $this->define_headers(array_values($headers));

        $fields = "u.id as id, u.email AS email, GROUP_CONCAT(DISTINCT c.fullname) AS courses,
         COUNT(lmr.id) AS totalratings, AVG(lmr.rate) AS rating, MAX(lmr.timecreated) AS last_updated";

        $from = "{local_mentor} lm
                JOIN {local_mentor_rates_log} lmr ON lm.id = lmr.mentor_id
                JOIN {user} u ON u.id = lm.userid
                AND u.deleted = 0
                LEFT JOIN {course} c oN c.id = lm.courseid";

        $where = "u.deleted = 0 GROUP BY u.id";
        $this->set_sql($fields, $from, $where, []);
        $this->set_count_sql("SELECT COUNT(DISTINCT u.id) FROM " . $from, []);

        $this->define_baseurl($baseurl);

        $this->collapsible(false);
    }

    /**
     * user name
     */
    public function col_name($values)
    {
        return parent::col_fullname(\core\user::get_user($values->id));
    }

    public function col_rating($values)
    {
        return round($values->rating, 2);
    }


    public function col_last_updated($values)
    {
        if ($values->last_updated) {
            return userdate($values->last_updated);
        }
        return 'N/A';
    }

    public function col_actions($values)
    {
        $url = new moodle_url('/local/mentor/report/mentor_report_details.php', ['userid' => $values->id]);
        return html_writer::link($url, get_string('viewdetails', 'local_mentor'));
    }
}
