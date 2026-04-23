<?php

namespace local_mentor\report;

require_once($CFG->libdir . '/tablelib.php');

use table_sql;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

class course_participants_table extends table_sql
{
    public function __construct($uniqueid)
    {
        parent::__construct($uniqueid);

        // =========================
        // COLUMNS
        // =========================
        $this->define_columns(['name', 'email', 'course']);
        $this->define_headers(['Name', 'Email', 'Courses & Roles']);

        global $PAGE;
        $this->define_baseurl($PAGE->url);

        // =========================
        // PAGINATION
        // =========================
        $this->pagesize(20, 1000);
    }

    public function query_db($pagesize, $useinitialsbar = true)
    {
        global $DB;

        // =========================
        // SAME SQL (OPTIMIZED)
        // =========================
        $fields = "
            u.id,
            CONCAT(u.firstname, ' ', u.lastname) AS name,
            u.email,
            GROUP_CONCAT(
                DISTINCT CONCAT(c.fullname, ' (', r.shortname, ')')
                SEPARATOR '<br>'
            ) AS course
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

        // =========================
        // COUNT (FOR PAGINATION)
        // =========================
        $countsql = "SELECT COUNT(DISTINCT u.id) FROM $from WHERE 1=1";
        $total = $DB->count_records_sql($countsql);

        $this->pagesize($pagesize, $total);

        // =========================
        // MAIN QUERY
        // =========================
        $sql = "SELECT $fields
                FROM $from
                WHERE $where
                GROUP BY u.id, u.firstname, u.lastname, u.email";

        // =========================
        // SAFE SORT FIX
        // =========================
        $sort = $this->get_sql_sort();

        // prevent Moodle wrong alias like emailname
        $sort = str_replace('emailname', 'u.email', $sort);

        if (empty($sort)) {
            $sort = " ORDER BY u.id DESC";
        }

        $sql .= $sort;

        // =========================
        // EXECUTE
        // =========================
        $this->rawdata = $DB->get_records_sql(
            $sql,
            [],
            $this->get_page_start(),
            $this->get_page_size()
        );
    }

    // =========================
    // CLICKABLE NAME
    // =========================
    public function col_name($row)
    {
        $url = new \moodle_url('/user/view.php', ['id' => $row->id]);
        return html_writer::link($url, $row->name, ['style' => 'color:#e10018;']);
    }

    public function col_email($row)
    {
        return $row->email;
    }

    public function col_course($row)
    {
        return $row->course;
    }
}
