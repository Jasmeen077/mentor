<?php

namespace local_mentor\observer;

use core_user;

/**
 * This class holds the events functions (enroll & Unenroll)
 * 
 * @package local_mentor 
 * @author jasmeen khanam <jasmeen.khanam@idslogic.com>
 */
class enroll
{

    public static function user_enrolled($event)
    {
        global $DB;

        $userid = $event->relateduserid;
        $courseid = $event->courseid;

        if (!$courseid) {
            return;
        }

        $context = \context_course::instance($courseid, IGNORE_MISSING);

        if (!$context) {
            return;
        }

        $roles = $DB->get_records_list('role', 'shortname', ['teacher', 'editingteacher']);

        foreach ($roles as $role) {

            $exists = $DB->record_exists('role_assignments', [
                'userid' => $userid,
                'roleid' => $role->id,
                'contextid' => $context->id
            ]);

            if ($exists) {

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

    // user unerolled mail implementation
    public static function user_unenrolled(\core\event\user_enrolment_deleted $event)
    {
        global $DB;

        // Get user
        $userid = $event->relateduserid;
        $courseid = $event->courseid;

        if (!$userid || !$courseid) {
            return;
        }

        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        // Prepare message
        $message = new \core\message\message();
        $message->component = 'local_mentor';
        $message->name = 'unenrol_notification';

        $message->userfrom = \core_user::get_noreply_user();
        $message->userto = $user;

        $message->subject = 'Course Unenrollment Notification';

        $message->fullmessage = "Hello {$user->firstname},

        You have been unenrolled from the course: {$course->fullname}.

        If you have any questions, please contact your administrator.";

        $message->fullmessageformat = FORMAT_PLAIN;

        $message->fullmessagehtml = "
            <p>Hello {$user->firstname},</p>
            <p>You have been <b>unenrolled</b> from the course: <b>{$course->fullname}</b>.</p>
            <p>If you have any questions, please contact support.</p>
        ";

        $message->smallmessage = "You were unenrolled from {$course->fullname}";
        $message->notification = 1;

        $message->contexturl = (new \moodle_url('/course/view.php', ['id' => $courseid]))->out(false);
        $message->contexturlname = $course->fullname;

        // Send message
        message_send($message);
    }
}
