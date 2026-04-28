<?php

namespace local_mentor;

use core\event\role_assigned;

defined('MOODLE_INTERNAL') || die();

class observer
{
    /**
     * Observ role assigned event.
     * 
     * @param \core\event\role_assigned $event
     * @return void
     */
    public static function role_assigned_observer(role_assigned $event)
    {
        self::make_mentors($event);

        $customdata =   [
            'event' => 'role_assigned',
            'userid' => $event->relateduserid,
            'courseid' => $event->courseid,
            'roleid' => $event->objectid,
        ];

        self::add_message_in_queue($customdata);
    }

    /**
     * Observer role unassigned event
     * 
     * @param \core\event\role_unassigned $event
     * 
     * @return void
     */
    public static function role_unassigned_observer(\core\event\role_unassigned $event)
    {
        mentor::delete_mentor($event);

        $customdata =   [
            'event' => 'role_unassigned',
            'userid' => $event->relateduserid,
            'courseid' => $event->courseid,
            'roleid' => $event->objectid,
        ];

        self::add_message_in_queue($customdata);
    }

    //assignment  submission
    public static function assignment_submit_mail(\mod_assign\event\assessable_submitted $event)
    {
        global $DB;

        $cmid = $event->contextinstanceid;
        $cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
        $assign = $DB->get_record('assign', ['id' => $cm->instance], '*', MUST_EXIST);

        $customdata = [
            'event'     => 'assessable_submitted',
            'userid'    => $event->userid,
            'courseid'  => $event->courseid,
            'assignid'  => $assign->id,
            'attemptid' => $event->objectid,
        ];

        self::add_message_in_queue($customdata);
    }

    //quiz subbmission
    public static function quiz_submit_mail(\mod_quiz\event\attempt_submitted $event)
    {
        error_log('Quiz attempt_submitted event triggered');

        $customdata = [
            'event'     => 'attempt_submitted',
            'userid'    => $event->userid,
            'courseid'  => $event->courseid,
            'attemptid' => $event->objectid,
        ];

        self::add_message_in_queue($customdata);
        // self::handle_attempt_submitted($customdata);
    }

    public static function feedback_submitted(\mod_feedback\event\response_submitted $event)
    {
        global $DB, $CFG;

        // Debug
        error_log("Feedback event triggered");

        $userid   = $event->userid;
        $courseid = $event->courseid;

        // =========================
        // 👤 USER SAFE FETCH
        // =========================
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);


        // =========================
        // 📘 COURSE
        // =========================
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        // =========================
        // 📚 FIXED CM (IMPORTANT FIX)
        // =========================
        $context = $event->get_context();
        $cm = get_coursemodule_from_id('feedback', $context->instanceid, 0, false, MUST_EXIST);
        $activityname = $cm->name;

        // =========================
        // 📝 FEEDBACK ANSWERS (SAFE)
        // =========================
        $feedbackresponses = $DB->get_records('feedback_value', ['completed' => $event->objectid]);

        $answers_html = "";

        if ($feedbackresponses) {
            foreach ($feedbackresponses as $response) {

                $question = $DB->get_record('feedback_item', ['id' => $response->item]);

                if ($question && !empty($response->value)) {
                    $answers_html .= "
                <tr>
                    <td style='padding:8px; border:1px solid #ddd; background:#f9f9f9;'>
                        <strong>" . format_string($question->name) . "</strong>
                    </td>
                    <td style='padding:8px; border:1px solid #ddd;'>
                        " . s($response->value) . "
                    </td>
                </tr>";
                }
            }
        }

        // =========================
        // 🔗 NEXT ACTIVITY (SAFE)
        // =========================
        $nextactivitylink = $CFG->wwwroot . '/course/view.php?id=' . $courseid;

        $modinfo = get_fast_modinfo($course);

        foreach ($modinfo->get_cms() as $mod) {
            if (!empty($mod->uservisible) && $mod->id != $cm->id && !empty($mod->url)) {
                $nextactivitylink = $mod->url;
                break;
            }
        }

        // =========================
        // 📩 HR EMAIL LIST
        // =========================
        $hr_emails = [
            'jasmeen.khanam@idslogic.com'
        ];

        $subject = "New Feedback Submitted";

        // =========================
        // 🎨 HR TEMPLATE (FIXED)
        // =========================
        $message = "
        <div style='background:#f4f6f8; padding:20px; font-family:sans-serif;'>
        <div style='max-width:720px; margin:auto; background:#fff; border-radius:10px; overflow:hidden;'>

            <div style='background:#e10018; color:#fff; padding:18px; text-align:center;'>
            <h2 style='margin:0;'>New Feedback Received</h2>
            </div>

            <div style='padding:20px;'>

            <p><strong>Name:</strong> {$user->firstname} {$user->lastname}</p>
            <p><strong>Email:</strong> {$user->email}</p>
            <p><strong>Course:</strong> " . format_string($course->fullname) . "</p>
            <p><strong>Activity:</strong> " . format_string($activityname) . "</p>

            <h3 style='color:#e10018;'>Feedback Details</h3>

            <table width='100%' style='border-collapse:collapse;'>
                {$answers_html}
            </table>

            </div>

            <div style='text-align:center; padding:30px; font-size:12px; background:#fafafa; color:#777;'>
            IDSLMS | idslms.dev.idslogic.net
            </div>

        </div>
        </div>
        ";

        // =========================
        // 📤 SEND HR MAIL (SAFE - NO fullname ERROR)
        // =========================
        foreach ($hr_emails as $email) {

            $hruser = new \stdClass();
            $hruser->id = -99;
            $hruser->email = $email;
            $hruser->firstname = 'HR';
            $hruser->lastname = 'Team';
            $hruser->firstnamephonetic = '';
            $hruser->lastnamephonetic = '';
            $hruser->middlename = '';
            $hruser->alternatename = '';
            $hruser->maildisplay = true;
            $hruser->mailformat = 1;

            email_to_user(
                $hruser,
                $user,
                $subject,
                strip_tags($message),
                $message
            );
        }

        // =========================
        // 📩 USER EMAIL (NO BUTTON)
        // =========================
        $usersubject = "Thank You for Your Feedback";

        $usermessage = "
        <div style='background:#f4f6f8; padding:20px; font-family:sans-serif;'>
        <div style='max-width:600px; margin:auto; background:#fff; border-radius:10px; overflow:hidden;'>

            <div style='background:#e10018; color:#fff; padding:18px; text-align:center;'>
            <h2 style='margin:0;'>Thank You!</h2>
            </div>

            <div style='padding:20px;'>
            <p>Dear {$user->firstname},</p>

            <p>Thank you for submitting your feedback.</p>

            <p>Please continue your learning journey in the course.</p>

            <p style='margin-top:20px;'>Regards,<br>IDSLMS Team</p>
            </div>

            <div style='text-align:center; padding:30px; font-size:12px; background:#fafafa; color:#777;'>
            IDSLMS | idslms.dev.idslogic.net
            </div>

        </div>
        </div>
        ";

        email_to_user(
            $user,
            \core\user::get_noreply_user(),
            $usersubject,
            strip_tags($usermessage),
            $usermessage
        );
    }

    /**
     * Make mentor if user has teache or editing teacher role
     */
    public static function make_mentors(role_assigned $event)
    {
        global $DB;
        $userid = $event->relateduserid;
        $courseid = $event->courseid;
        $roleid = $event->objectid;

        $roleids = $DB->get_records_sql_menu('SELECT shortname, id FROM {role} WHERE archetype IN (?, ?)', ['editingteacher', 'teacher']);

        if (in_array($roleid, $roleids)) {
            mentor::make_mentor($userid, $courseid);
        }
    }

    public static function add_message_in_queue(array $customdata)
    {

        $task = new \local_mentor\task\send_notification_task();
        $task->set_custom_data($customdata);

        \core\task\manager::queue_adhoc_task($task);
    }
}
