<?php

namespace local_mentor;

defined('MOODLE_INTERNAL') || die();

class observer
{
    public static function user_enrolled($event)
    {
        global $DB;

        $userid = $event->relateduserid;
        $courseid = $event->courseid;

        // Course context
        $context = \context_course::instance($courseid);

        // Teacher + Editing teacher roles
        $roles = $DB->get_records_list('role', 'shortname', ['teacher', 'editingteacher']);

        foreach ($roles as $role) {

            $exists = $DB->record_exists('role_assignments', [
                'userid' => $userid,
                'roleid' => $role->id,
                'contextid' => $context->id
            ]);

            if ($exists) {

                // Insert into local_mentor table only if not exists
                $mentor = $DB->get_record('local_mentor', [
                    'userid' => $userid,
                    'courseid' => $courseid
                ]);

                if (!$mentor) {
                    $record = new \stdClass();
                    $record->userid = $userid;
                    $record->courseid = $courseid;
                    $record->rating = 0;

                    $DB->insert_record('local_mentor', $record);
                }

                break;
            }
        }
    }
}
