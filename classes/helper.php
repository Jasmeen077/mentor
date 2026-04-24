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
}
