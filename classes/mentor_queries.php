<?php

namespace local_mentor;

defined('MOODLE_INTERNAL') || die();

class mentor_queries
{

    public static function get_mentors($userid)
    {
        global $DB;


        $sql = "SELECT u.id,
                       u.firstname,
                       u.lastname,
                       u.email,
                       u.department,
                       r.shortname AS role,
                       biodata.data AS bio,
                       expertisedata.data AS expertise,
                       GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') AS interests,
                       ROUND(AVG(mr.rating),1) AS averagerating

                FROM {user} u

                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {context} ctx ON ctx.id = ra.contextid
                JOIN {course} c ON c.id = ctx.instanceid
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                JOIN {role} r ON r.id = ra.roleid

                LEFT JOIN {user_info_field} biofield 
                       ON biofield.shortname = 'bio'
                LEFT JOIN {user_info_data} biodata 
                       ON biodata.userid = u.id 
                      AND biodata.fieldid = biofield.id

                LEFT JOIN {user_info_field} expertisefield 
                       ON expertisefield.shortname = 'expertise'
                LEFT JOIN {user_info_data} expertisedata 
                       ON expertisedata.userid = u.id 
                      AND expertisedata.fieldid = expertisefield.id

                LEFT JOIN {tag_instance} ti 
                       ON ti.itemid = u.id
                      AND ti.component = 'core'
                      AND ti.itemtype = 'user'

                LEFT JOIN {tag} t ON t.id = ti.tagid

                LEFT JOIN {local_mentor} mr 
                       ON mr.userid = u.id

                WHERE ra.roleid = 3
                  AND ctx.contextlevel = 50
                  AND ue.userid = :userid

                GROUP BY u.id,
                         u.firstname,
                         u.lastname,
                         u.email,
                         u.department,
                         r.shortname,
                         biodata.data,
                         expertisedata.data";

        return $DB->get_records_sql($sql, ['userid' => $userid]);
    }
}
