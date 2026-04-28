<?php

/**
 * Message Manager class
 * 
 * @package local_mentor
 * @author Mohan Lal Sharma & Jasmeen Khanam <mohan.sharma@idslogic.com & jasmeen.khanam@idslogic>
 */

namespace local_mentor;

use local_mentor\helper;

defined('MOODLE_INTERNAL') || die();

class message_manager
{

    /**
     * Entry point (called from adhoc task)
     */
    public static function process(array $data)
    {

        if (empty($data['event'])) {
            return;
        }

        $event = $data['event'];

        mtrace('Messages sending for the event: ', $event);

        // Dynamically call method based on event
        $method = 'handle_' . $event;

        if (method_exists(__CLASS__, $method)) {
            self::$method($data);
        }
    }

    /**
     * EVENT: Role Assigned
     */
    private static function handle_role_assigned(array $data)
    {
        global $DB;

        $user = $DB->get_record('user', ['id' => $data['userid']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $data['courseid']]);
        $shortname = $DB->get_field('role', 'shortname', ['id' => $data['roleid']]);
        $vars = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'courseid' => $course->id,
            'coursename' => $course->fullname,
            'role'      => $shortname,
        ];

        self::send('role_assigned', $user, $vars);

        self::notify_admin_users('role_assigned', $vars);
    }

    /**
     * EVENT: Role Assigned
     */
    private static function handle_role_unassigned(array $data)
    {
        global $DB;

        $user = $DB->get_record('user', ['id' => $data['userid']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $data['courseid']]);
        $shortname = $DB->get_field('role', 'shortname', ['id' => $data['roleid']]);

        $vars = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'courseid' => $course->id,
            'coursename' => $course->fullname,
            'role'      => $shortname,
        ];

        self::send('role_unassigned', $user, $vars);

        self::notify_admin_users('role_unassigned', $vars);
    }

    /**
     * handle quiz attempt submission message
     */
    private static function handle_attempt_submitted(array $data)
    {
        global $DB;

        $user = $DB->get_record('user', ['id' => $data['userid']], '*', MUST_EXIST);

        $course = $DB->get_record('course', ['id' => $data['courseid']], '*', MUST_EXIST);

        $quiz = $DB->get_record_sql(
            "SELECT q.name
         FROM {quiz} q
         JOIN {quiz_attempts} qa ON q.id = qa.quiz
         WHERE qa.id = ?",
            [$data['attemptid']]
        );

        $vars = [
            'firstname'  => $user->firstname,
            'lastname'   => $user->lastname,
            'courseid'   => $course->id,
            'coursename' => $course->fullname,
            'quizname'   => $quiz ? $quiz->name : 'Quiz',
            'attemptid'  => $data['attemptid']
        ];

        self::send('attempt_submitted', $user, $vars);

        // $teachers = helper::get_teacher($course->id);

        // if (empty($teachers)) {
        //     error_log('No teachers found for course ID: ' . $course->id);
        //     return;
        // }

        // $uniqueTeachers = [];
        // foreach ($teachers as $teacher) {
        //     $uniqueTeachers[$teacher->id] = $teacher;
        // }

        // foreach ($uniqueTeachers as $teacher) {

        //     if (empty($teacher->email)) {
        //         continue;
        //     }

        //     self::send('quiz_teacher_notification', $teacher, $vars);
        // }
    }

    /**
     * Handle assignment submission messages
     * 
     */

    private static function handle_assessable_submitted(array $data)
    {
        global $DB;

        $user = $DB->get_record('user', ['id' => $data['userid']], '*', MUST_EXIST);

        $course = $DB->get_record('course', ['id' => $data['courseid']], '*', MUST_EXIST);

        $assignment = $DB->get_record('assign', ['id' => $data['assignid']], '*', MUST_EXIST);

        $vars = [
            'firstname'      => $user->firstname,
            'lastname'       => $user->lastname,
            'courseid'       => $course->id,
            'coursename'     => $course->fullname,
            'assignmentname' => $assignment ? $assignment->name : 'Assignment',
            'attemptid'      => $data['attemptid'] ?? null
        ];

        self::send('assessable_submitted', $user, $vars);

        // $teachers = helper::get_teacher($course->id);

        // if (empty($teachers)) {
        //     error_log('No teachers found for course ID: ' . $course->id);
        //     return;
        // }

        // $uniqueTeachers = [];
        // foreach ($teachers as $teacher) {
        //     $uniqueTeachers[$teacher->id] = $teacher;
        // }

        // foreach ($uniqueTeachers as $teacher) {

        //     if (empty($teacher->email)) {
        //         continue;
        //     }

        //     self::send('assignment_teacher_notification', $teacher, $vars);
        // }
    }

    /**
     * CORE SEND FUNCTION
     */
    private static function send(string $event, \stdClass $user, array $vars)
    {

        // Load templates from settings
        $subject = get_config('local_mentor', $event . '_subject');
        $body    = get_config('local_mentor', $event . '_body');

        // Replace variables
        $subject = self::replace($subject, $vars);
        $body    = self::replace($body, $vars);

        // Prepare message
        $message = new \core\message\message();
        $message->component         = 'local_mentor';
        $message->name              = 'notification';
        $message->userfrom          = \core\user::get_noreply_user();
        $message->userto            = $user;
        $message->subject           = $subject;
        $message->fullmessage       = html_to_text($body);
        $message->fullmessagehtml   = $body;
        $message->fullmessageformat = FORMAT_HTML;
        $message->smallmessage      = strip_tags($body);

        message_send($message);
    }

    /**
     * VARIABLE REPLACER
     */
    private static function replace(string $template, array $vars): string
    {

        return preg_replace_callback('/{(.*?)}/', function ($matches) use ($vars) {
            return $vars[$matches[1]] ?? '';
        }, $template);
    }

    /**
     * Get admins list for notifications
     */
    private static function notify_admin_users(string $event, array $vars)
    {
        global $DB;
        $admins = get_config('local_mentor', 'admin_notification_receiver');
        $adminids = explode(',', $admins);


        list($in_sql, $params) = $DB->get_in_or_equal($adminids, SQL_PARAMS_NAMED, 'u');
        $users = $DB->get_records_sql("SELECT * FROM {user} WHERE id $in_sql", $params);
        foreach ($users as $user) {
            self::send($event, $user, $vars);
        }
    }
}
