<?php

namespace local_mentor;

use core\event\role_unassigned;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Mentor SQL opertions here
 * 
 * @package local_mentor
 * @author 2026 Mohan Lal Sharma & Jasmeen Khanam <mohan.sharma@idslogic.com & jasmeen.khanam@idslogic.com>
 */
class mentor
{

	public static function get_mentors()
	{
		global $DB, $USER;

		$sql = "SELECT DISTINCT
			u.id,
			u.firstname,
			u.lastname,
			u.email,
			u.department,
			biodata.data AS bio,
			expertisedata.data AS expertise,
			interestdata.interests,
			GROUP_CONCAT(DISTINCT c.fullname SEPARATOR ', ') AS courses,
			AVG(lm.rating) AS averagerating
		FROM
			{local_mentor} lm
			JOIN {user} u ON u.id = lm.userid
			JOIN {course} c ON c.id = lm.courseid 
			LEFT JOIN (
				SELECT uid.`data`, uid.userid
				FROM
					{user_info_field} uif
					JOIN {user_info_data} uid ON uid.fieldid = uif.id
				WHERE
					uif.shortname = 'bio'
			) as biodata ON biodata.userid = u.id 
			LEFT JOIN (
				SELECT uid.`data`, uid.userid
				FROM
					{user_info_field} uif
					JOIN {user_info_data} uid ON uid.fieldid = uif.id
				WHERE
					uif.shortname = 'expertise'
			) as expertisedata ON expertisedata.userid = u.id
			LEFT JOIN (
				SELECT GROUP_CONCAT(t.name SEPARATOR ', ') AS interests, ti.itemid
				FROM
					{tag_instance} ti
					JOIN {tag} t ON t.id = ti.tagid
				WHERE
					ti.component = 'core'
					AND ti.itemtype = 'user'
				GROUP BY
					ti.itemid
			) as interestdata ON interestdata.itemid = u.id
			GROUP BY u.id";

		return $DB->get_records_sql($sql, ['userid' => $USER->id]);
	}

	/**
	 * Save the ratings for a mentor
	 */
	public static function save_rating(int $userid, int $courseid, int $rating, string $reason)
	{
		global $DB, $USER;

		$transaction = $DB->start_delegated_transaction();
		try {
			// find the mentor for the course
			$mentor = $DB->get_record('local_mentor', ['userid' => $userid, 'courseid' => $courseid]);
			if (!$mentor) {
				// create a new mentor record
				$mentor = new \stdClass();
				$mentor->userid = $userid;
				$mentor->courseid = $courseid;
				$mentor->rating = $rating;
				$mentor->timecreated = time();
				$mentor->timemodified = time();
				$mentor->id = $DB->insert_record('local_mentor', $mentor);
			}

			$log = new \stdClass();
			$log->mentor_id = $mentor->id;
			$log->rate = $rating;
			$log->userid = $USER->id;
			$log->reason = $reason;
			$log->timecreated = time();

			$DB->insert_record('local_mentor_rates_log', $log);

			// Get average rating for the mentor
			$average = $DB->get_field_sql('SELECT AVG(rate) as average FROM {local_mentor_rates_log} WHERE mentor_id = ?', [$mentor->id]);

			if ($average) {
				$mentor->rating = round($average, 1);
			} else {
				// update the existing mentor
				$mentor->rating = $rating;
			}
			$mentor->timemodified = time();
			$DB->update_record('local_mentor', $mentor);

			// TODO:Trigger rating record event

			$DB->commit_delegated_transaction($transaction);
			return true;
		} catch (\dml_exception $e) {
			$DB->rollback_delegated_transaction($transaction, $e);
			return false;
		}
	}

	/**
	 * Get enrolled courses for current user where user has teacher role and have not rated yet.
	 * 
	 * @param int $mentorid
	 * 
	 * @return array
	 */
	public static function get_courses_list_for_rating(int $mentorid): array
	{
		global $DB, $USER;
		$sql = "SELECT DISTINCT c.id, CONCAT(
                c.fullname, ' (', c.shortname, ')'
            ) as coursename
        FROM
            {local_mentor} m
            JOIN {course} c ON c.id = m.courseid
            AND c.visible = 1
        WHERE
            m.id NOT IN(
                SELECT DISTINCT mentor_id
                FROM {local_mentor_rates_log}
                WHERE
                    userid = :userid
            )
			AND m.userid = :mentorid";

		return $DB->get_records_sql_menu($sql, ['userid' => $USER->id, 'mentorid' => $mentorid]);
	}

	/**
	 * Create a mentor
	 */
	public static function make_mentor(int $userid, int $courseid)
	{
		global $DB;

		$record = new stdClass();
		$record->userid = $userid;
		$record->courseid = $courseid;
		$record->rating = 0;
		$record->timecreated = time();
		$record->timemodified = time();

		if (!$DB->record_exists('local_mentor', ['courseid' => $courseid, 'userid' => $userid])) {
			$DB->insert_record('local_mentor', $record);
		}
	}

	/**
	 * Delete mentor.
	 * 
	 * @param role_unassigned $event
	 */
	public static function delete_mentor(role_unassigned $event)
	{
		global $DB;
		$userid = $event->relateduserid;
		$courseid = $event->courseid;

		// TODO: Please check if user has mulitple roles then check that role

		if ($DB->record_exists('local_mentor', ['userid' => $userid, 'courseid' => $courseid])) {
			$DB->delete_records('local_mentor', ['userid' => $userid, 'courseid' => $courseid]);

			// TODO: delete logs through scheduled task for fast performances;
		}
	}
}
