<?php

/**
 * Message Manager class
 * 
 * @package local_mentor
 * @author Mohan Lal Sharma <mohan.sharma@idslogic.com>
 */

namespace local_mentor;

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
}
