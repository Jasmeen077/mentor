<?php

namespace local_mentor\report;

require_once($CFG->libdir . '/tablelib.php');

use core\url;
use core_table\sql_table;
use stdClass;
use \core\output\html_writer;

defined('MOODLE_INTERNAL') || die();

class course_participants_table extends sql_table
{
    public function __construct(string $uniqueid, url|string $url)
    {
        global $USER, $DB;

        parent::__construct($uniqueid);

        $headers = [
            'name' => get_string('name'),
            'email' => get_string('email'),
            'course' => get_string('courseandrole', 'local_mentor'),
            'action' => get_string('action'),
        ];

        $fields = "
            u.id,
            CONCAT(u.firstname, ' ', u.lastname) AS name,
            u.email,
            GROUP_CONCAT(
                DISTINCT CONCAT(c.fullname, ' (', r.shortname, ')')
                SEPARATOR '<br>'
            ) AS course,
                 GROUP_CONCAT(DISTINCT c.id SEPARATOR ',') as courseids
        ";

        $from = "
            {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {course} c ON c.id = e.courseid
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ctx ON ctx.id = ra.contextid
                AND ctx.contextlevel = 50
                AND ctx.instanceid = c.id
            JOIN {role} r ON r.id = ra.roleid
        ";
        $where = "1=1";
        $params = [];

        if (!is_siteadmin()) {
            $courses = \local_mentor\helper::has_teacher_role_in_course($USER->id);
            list($in_sql, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'c');
            $where = 'c.id ' . $in_sql . ' ';
        }


        $this->set_count_sql("SELECT COUNT(DISTINCT u.id) FROM " . $from . " WHERE " . $where, $params);
        $where .= " GROUP BY u.id";
        $this->set_sql($fields, $from, $where, $params);
        $this->define_baseurl($url);
        $this->define_columns(array_keys($headers));
        $this->define_headers(array_values($headers));
        $this->collapsible(false);
        $this->no_sorting('action');
    }


    public function col_name($row)
    {
        return parent::col_fullname(\core\user::get_user($row->id));
    }

    public function col_action(stdClass $row)
    {
        $actions = '';
        $courseids = explode(',', $row->courseids);
        $url = new url('/local/mentor/report/participants.php');
        foreach ($courseids as $id) {
            $params = [
                'userid' => $row->id,
                'courseid' => $id
            ];

            $url->params($params);
            $actions .= html_writer::link($url, get_string('unenrol', 'local_mentor'));
            $actions .= html_writer::empty_tag('br', ['class' => '']);
        }
        return $actions;
    }
}
