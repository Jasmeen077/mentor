<?php

namespace local_mentor;

use core\url;
use Exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class of local mentor plugin
 * 
 * @package local_mentor 
 * @author 2026 Mohan Lal Sharma <mohan.sharma@idslogic.com>
 */
class helper
{

    /**
     * Get categories for learn and upskills menu
     * 
     * @return array List of categories
     */
    public static function get_upskills_categories(): array
    {
        global $DB;

        $selectedcategories = get_config('local_mentor', 'upandskillscategories');
        if (empty($selectedcategories)) {
            return [];
        }

        $selectedcategories = explode(',', $selectedcategories);
        list($sqlin, $params) = $DB->get_in_or_equal($selectedcategories, SQL_PARAMS_NAMED, 'cat');

        $sql = "SELECT id, name FROM {course_categories} WHERE id $sqlin";
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get current user`s courses and teachers
     * @return array List of courses with teachers
     */
    public static function get_user_courses_with_teachers(): array
    {
        global $USER, $DB;

        // Get all enrolled courses for current user
        $courses = enrol_get_users_courses($USER->id, true);

        $teacherdata = [];

        foreach ($courses as $course) {

            // Get teachers (editingteacher + teacher roles)
            $context = \context_course::instance($course->id);

            $roleids = $DB->get_fieldset_select('role', 'id', "archetype IN ('editingteacher', 'teacher')");
            $teachers = get_role_users(
                $roleids,
                $context,
                false,
                'ra.id as raid, u.id as uid, u.firstname, u.lastname, u.email'
            );

            foreach ($teachers as $teacher) {
                $teacherdata[$teacher->uid][$course->id] = $course->fullname;
            }
        }
        return $teacherdata;
    }

    /**
     * Has teacher role of current user in any enroled courses. 
     * 
     * @param int $userid
     * @return array|null
     */
    public static function has_teacher_role_in_course(int $userid): array|null
    {
        global $DB;
        $courses = enrol_get_users_courses($userid, true, 'id');

        list($in_sql, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'c');
        $sql = "SELECT ctx.instanceid as courseid
                FROM
                    {context} ctx
                    JOIN {role_assignments} ra ON ra.contextid = ctx.id
                    JOIN {role} r ON r.id = ra.roleid
                WHERE
                    ctx.contextlevel = 50
                    AND ctx.instanceid $in_sql
                    AND r.archetype = 'editingteacher'
                    AND ra.userid = :userid;";
        $params["userid"] = $userid;
        $record = $DB->get_records_sql($sql, $params);
        return $record;
    }

    public static function can_access_participant_report(int $userid): bool
    {
        $courses = self::has_teacher_role_in_course($userid) ?: 0;
        return is_siteadmin($userid) || $courses;
    }

    /**
     * Unenrol user with all enrolments form the course
     * 
     * @param int $userid
     * @param int $courseid
     * @param url $url
     * @return void
     */
    public static function unenrol_user_in_course(int $userid, int $courseid, url $url): void
    {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        try {
            // Get all enrol instances for the course
            $instances = enrol_get_instances($courseid, true);

            foreach ($instances as $instance) {
                $plugin = enrol_get_plugin($instance->enrol);

                if ($plugin && $plugin->allow_unenrol($instance)) {
                    $plugin->unenrol_user($instance, $userid);
                }
            }
            $transaction->allow_commit();
            $course = $DB->get_field('course', 'fullname', ['id' => $courseid]);
            $a = new stdClass();
            $a->name = fullname(\core\user::get_user($userid));
            $a->course = format_string($course);

            redirect($url, get_string('unenrolsuccessmessage', 'local_mentor', $a), null, \core\output\notification::NOTIFY_SUCCESS);
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
    }
}
