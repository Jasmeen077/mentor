<?php

namespace local_mentor;

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
}
