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
 * @author Jasmeen Khanam <jasmeen.khanam@idslogic.com> April 2026
 */
class mentor_report_details extends sql_table
{
    public function __construct(string $uniqueid, moodle_url|string $baseurl, $userid)
    {
        parent::__construct($uniqueid);

        $headers = [
            'name' => get_string('name'),
            'email' => get_string('email'),
            'course' => get_string('course', 'local_mentor'),
            'rating' => get_string('rating', 'local_mentor'),
            'comment' => get_string('comment', 'local_mentor'),
            'timecreated' => get_string('timecreated', 'local_mentor')
        ];

        $this->define_columns(array_keys($headers));
        $this->define_headers(array_values($headers));

        $fields = "lmr.id as lmrid, 
               u.id,
               u.firstname,
               u.lastname,
               u.firstnamephonetic,
               u.lastnamephonetic,
               u.middlename,
               u.alternatename,
               u.email,
               c.fullname AS course,
               lmr.rate AS rating,
               lmr.reason AS comment,
               lmr.timecreated";

        $from = "{local_mentor_rates_log} lmr
             JOIN {local_mentor} lm ON lm.id = lmr.mentor_id
             JOIN {user} u ON u.id = lm.userid
             LEFT JOIN {course} c ON c.id = lm.courseid";

        $where = "u.id = :userid";

        $params = ['userid' => $userid];

        $this->set_sql($fields, $from, $where, $params);

        $this->set_count_sql("SELECT COUNT(1)
                          FROM {local_mentor_rates_log} lmr
                          JOIN {local_mentor} lm ON lm.id = lmr.mentor_id
                          WHERE lm.userid = :userid", $params);

        $this->define_baseurl($baseurl);
        $this->collapsible(false);
    }

    public function col_name($values)
    {
        return fullname($values);
    }

    public function col_rating($values)
    {
        return round($values->rating, 2);
    }

    public function col_timecreated($values)
    {
        return userdate($values->timecreated, '%d %B %Y, %I:%M %p');
    }

    public function col_actions($values)
    {
        $url = new moodle_url('/local/mentor/report/mentor_report_details.php', ['userid' => $values->id]);
        return html_writer::link($url, get_string('viewdetails', 'local_mentor'));
    }
}
