<<<<<<< Updated upstream
<?php

namespace local_mentor;

defined('MOODLE_INTERNAL') || die();

class observer
{
    /**
     * Make mentor if user has teacher or editingteacher role assigned
     * @param \core\event\user_enrolment_created|\core\event\user_enrolment_updated $event
     */
    public static function user_enrolled($event)
    {
        global $DB;

        $userid   = $event->relateduserid;
        $courseid = $event->courseid;
        $context  = $event->context;

        // Get teacher roles
        $roles = $DB->get_records_list('role', 'archetype', ['teacher', 'editingteacher']);

        if (!$roles) {
            return;
        }

        $roleids = array_column($roles, 'id');

        list($insql, $params) = $DB->get_in_or_equal($roleids);

        $params = array_merge([$userid, $context->id], $params);

        $hasrole = $DB->record_exists_select(
            'role_assignments',
            "userid = ? AND contextid = ? AND roleid $insql",
            $params
        );

        if ($hasrole) {

            if (!$DB->record_exists('local_mentor', [
                'userid'   => $userid,
                'courseid' => $courseid
            ])) {

                $record = new \stdClass();
                $record->userid   = $userid;
                $record->courseid = $courseid;
                $record->rating   = 0;
                $record->timecreated = time();

                $DB->insert_record('local_mentor', $record);
            }
        }
    }

    public static function user_email(\core\event\user_enrolment_created $event)
    {
        global $DB;

        error_log('user_email triggered');

        $userid = $event->relateduserid;
        $courseid = $event->courseid;

        $student = $DB->get_record('user', ['id' => $userid]);
        $course = $DB->get_record('course', ['id' => $courseid]);

        $context = \context_course::instance($courseid);
        if (!$context) {
            error_log('Context not found');
            return;
        }

        // Teachers (editing = 3, non-editing = 4)
        $editingteachers = get_role_users(3, $context);
        $noneditingteachers = get_role_users(4, $context);
        $teachers = array_merge($editingteachers, $noneditingteachers);

        // Remove duplicates
        $uniqueTeachers = [];
        foreach ($teachers as $teacher) {
            $uniqueTeachers[$teacher->id] = $teacher;
        }

        // Sender user (admin)
        $sender = $DB->get_record('user', ['id' => 12]);

        // ===== Notify Teachers =====
        foreach ($uniqueTeachers as $teacher) {
            $subject = 'New Employee Enrollment Notification - IDSLMS';

            $messagehtml = '
        <div style="font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; max-width:600px;">
            <div style="background:#e10018; color:white; padding:12px; font-size:18px;">
                IDSLMS Enrollment Notification
            </div>

            <p style="color:#d63384;"><strong>Dear ' . $teacher->firstname . ',</strong></p>

            <p>A new employee has enrolled in your assigned course.</p>

            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="padding:8px; border:1px solid #ddd;"><strong>Employee Name</strong></td>
                    <td style="padding:8px; border:1px solid #ddd; color:red;">' . $student->firstname . ' ' . $student->lastname . '</td>
                </tr>
                <tr>
                    <td style="padding:8px; border:1px solid #ddd;"><strong>Course Name</strong></td>
                    <td style="padding:8px; border:1px solid #ddd; color:blue;">' . $course->fullname . '</td>
                </tr>
            </table>

            <p style="margin-top:20px;">
                Regards,<br><br>
                <strong>IDSLMS Team</strong>
            </p>
        </div>';

            $messagetext = 'Dear ' . $teacher->firstname . ',
' . $student->firstname . ' ' . $student->lastname . ' enrolled in ' . $course->fullname . '.';

            $result = email_to_user($teacher, $sender, $subject, $messagetext, $messagehtml);
            error_log('Teacher mail status: ' . ($result ? 'sent' : 'failed'));
        }

        // ===== Notify Student =====
        $subject_student = 'Enrollment Confirmation - ' . $course->fullname;

        $messagehtml_student = '
    <div style="font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; max-width:600px;">
        <div style="background:#e10018; color:white; padding:12px; font-size:18px;">
            IDSLMS Enrollment Confirmation
        </div>

        <p style="color:#0d6efd;"><strong>Dear ' . $student->firstname . ',</strong></p>

        <p>Congratulations! You have been successfully enrolled in the following course:</p>

        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="padding:8px; border:1px solid #ddd;"><strong>Course Name</strong></td>
                <td style="padding:8px; border:1px solid #ddd; color:blue;">' . $course->fullname . '</td>
            </tr>
            <tr>
                <td style="padding:8px; border:1px solid #ddd;"><strong>Start Date</strong></td>
                <td style="padding:8px; border:1px solid #ddd; color:green;">' . date('d M Y') . '</td>
            </tr>
        </table>

        <p style="margin-top:20px;">
            We wish you a great learning experience!<br><br>
            <strong>IDSLMS Team</strong>
        </p>
    </div>';

        $messagetext_student = 'Dear ' . $student->firstname . ',
       You have successfully enrolled in ' . $course->fullname . '.';

        $result_student = email_to_user($student, $sender, $subject_student, $messagetext_student, $messagehtml_student);
        error_log('Student mail status: ' . ($result_student ? 'sent' : 'failed'));
    }


    //assignment  submission
    public static function assignment_submit_mail($event)
    {
        self::send_mail($event, 'Assignment');
    }

    //quiz subbmission
    public static function quiz_submit_mail($event)
    {
        self::send_mail($event, 'Quiz');
    }

    private static function send_mail($event, $activitytype)
    {
        global $DB, $CFG;

        error_log($activitytype . ' mail triggered');

        $userid = $event->userid;
        $courseid = $event->courseid;

        if (!$userid || !$courseid) {
            error_log('Missing userid or courseid');
            return;
        }

        $student = $DB->get_record('user', ['id' => $userid]);
        $course = $DB->get_record('course', ['id' => $courseid]);

        if (!$student || !$course) {
            error_log('Student or course not found');
            return;
        }

        $context = \context_course::instance($courseid);

        $editingteachers = get_role_users(3, $context);
        $noneditingteachers = get_role_users(4, $context);

        $teachers = array_merge($editingteachers, $noneditingteachers);

        $uniqueTeachers = [];
        foreach ($teachers as $teacher) {
            $uniqueTeachers[$teacher->id] = $teacher;
        }

        $sender = $DB->get_record('user', ['id' => 12]);

        foreach ($uniqueTeachers as $teacher) {

            $subject = 'Employee ' . $activitytype . ' Submission Notification - IDSLMS';

            $messagehtml = '
        <div style="margin:0; padding:30px; background:#f4f6f9; font-family:Arial,sans-serif;">

            <div style="
                max-width:650px;
                margin:auto;
                background:#ffffff;
                border-radius:14px;
                overflow:hidden;
                box-shadow:0 10px 35px rgba(0,0,0,0.08);
                border:1px solid #e6e6e6;
            ">

                <div style="
                    background:linear-gradient(90deg,#1d2125,#e10018);
                    padding:25px;
                    text-align:center;
                ">
                    <h1 style="
                        margin:0;
                        color:#ffffff;
                        font-size:24px;
                        letter-spacing:1px;
                    ">
                        IDSLMS Notification
                    </h1>
                </div>

                <div style="padding:35px;">

                    <h2 style="
                        color:#e10018;
                        margin-bottom:18px;
                        font-size:22px;
                    ">
                        ' . $activitytype . ' Submission Alert
                    </h2>

                    <p style="
                        font-size:16px;
                        color:#1d2125;
                        margin-bottom:18px;
                    ">
                        Dear <strong>' . $teacher->firstname . '</strong>,
                    </p>

                    <p style="
                        font-size:15px;
                        color:#555;
                        line-height:1.8;
                    ">
                        An employee has successfully submitted a 
                        <strong style="color:#e10018;">' . $activitytype . '</strong> 
                        in your assigned course. Kindly review the submission details below.
                    </p>

                    <table style="
                        width:100%;
                        margin-top:25px;
                        border-collapse:collapse;
                        border-radius:10px;
                        overflow:hidden;
                    ">

                        <tr style="background:#f8f9fa;">
                            <td style="
                                padding:14px;
                                border:1px solid #ddd;
                                font-weight:bold;
                                width:35%;
                            ">
                                Employee Name
                            </td>
                            <td style="
                                padding:14px;
                                border:1px solid #ddd;
                                color:#e10018;
                                font-weight:500;
                            ">
                                ' . $student->firstname . ' ' . $student->lastname . '
                            </td>
                        </tr>

                        <tr>
                            <td style="
                                padding:14px;
                                border:1px solid #ddd;
                                font-weight:bold;
                            ">
                                Course Name
                            </td>
                            <td style="
                                padding:14px;
                                border:1px solid #ddd;
                                color:#1d2125;
                            ">
                                ' . $course->fullname . '
                            </td>
                        </tr>

                        <tr style="background:#f8f9fa;">
                            <td style="
                                padding:14px;
                                border:1px solid #ddd;
                                font-weight:bold;
                            ">
                                Activity Type
                            </td>
                            <td style="
                                padding:14px;
                                border:1px solid #ddd;
                                color:#0d6efd;
                            ">
                                ' . $activitytype . '
                            </td>
                        </tr>

                    </table>

                    <p style="
                        margin-top:30px;
                        font-size:14px;
                        color:#555;
                        line-height:1.7;
                    ">
                        Please login to IDSLMS and complete your review process.
                    </p>

                    <p style="
                        margin-top:25px;
                        font-size:15px;
                        color:#1d2125;
                    ">
                        Regards,<br>
                        <strong style="color:#e10018;">IDSLMS Team</strong><br>
                        <span style="font-size:13px;color:#777;">noreply@idslogic.com</span>
                    </p>

                </div>

                <div style="
                    background:#1d2125;
                    text-align:center;
                    padding:18px;
                    color:#ffffff;
                    font-size:12px;
                ">
                    © IDSLMS | Internal Learning Platform | https://idslms.dev.idslogic.net/
                </div>

            </div>

        </div>';

            $messagetext = $student->firstname . ' submitted ' . $activitytype . ' in ' . $course->fullname;

            $result = email_to_user(
                $teacher,
                $sender,
                $subject,
                $messagetext,
                $messagehtml
            );

            error_log($activitytype . ' mail status: ' . ($result ? 'sent' : 'failed'));
        }
    }

    public static function feedback_submitted(\mod_feedback\event\response_submitted $event)
    {
        global $DB, $CFG;

        error_log("Feedback event triggered");

        $userid   = $event->userid;
        $courseid = $event->courseid;

        // =========================
        // 👤 USER
        // =========================
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        $user->firstnamephonetic = $user->firstnamephonetic ?? '';
        $user->lastnamephonetic  = $user->lastnamephonetic ?? '';
        $user->middlename        = $user->middlename ?? '';
        $user->alternatename     = $user->alternatename ?? '';

        // =========================
        // 📘 COURSE
        // =========================
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        // =========================
        // 📚 ACTIVITY
        // =========================
        $context = $event->get_context();
        $cm = get_coursemodule_from_id('feedback', $context->instanceid, 0, false, MUST_EXIST);
        $activityname = $cm->name;

        // =========================
        // 📝 QUESTIONS + ANSWERS (FIXED STRING OUTPUT)
        // =========================
        $items = $DB->get_records('feedback_item', ['feedback' => $cm->instance]);

        $answers_html = "";

        if ($items) {
            foreach ($items as $item) {

                $value = $DB->get_record('feedback_value', [
                    'item' => $item->id,
                    'completed' => $event->objectid
                ]);

                if (!$value) {
                    continue;
                }

                $display = $value->value;

                // =========================
                // 🔥 FIX MULTICHOICE LABELS
                // =========================
                if ($item->typ === 'multichoice') {

                    $presentation = $item->presentation;

                    // format: 0|Poor|1|Good|2|Excellent
                    $opts = explode('|', $presentation);

                    if (isset($opts[$display + 1])) {
                        $display = $opts[$display + 1];
                    }
                }

                $answers_html .= "
            <tr>
                <td style='padding:8px; border:1px solid #ddd; background:#f9f9f9;'>
                    <strong>" . format_string($item->name) . "</strong>
                </td>
                <td style='padding:8px; border:1px solid #ddd;'>
                    " . s($display) . "
                </td>
            </tr>";
            }
        }

        // =========================
        // 🔗 NEXT ACTIVITY
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
        // 📩 HR EMAILS
        // =========================
        $hr_emails = [
            'anshu.mehra@idslogic.com',
            'hr@idslogic.com'
        ];

        $subject = "New Feedback Submitted";

        // =========================
        // 🎨 HR TEMPLATE (CLEAN + MODERN)
        // =========================
        $message = "
    <div style='background:#f4f6f8; padding:20px; font-family:sans-serif;'>
      <div style='max-width:750px; margin:auto; background:#fff; border-radius:12px; overflow:hidden;'>

        <div style='background:#e10018; color:#fff; padding:20px; text-align:center;'>
          <h2 style='margin:0;'>New Feedback Received</h2>
        </div>

        <div style='padding:25px;'>

          <p><strong>Name:</strong> {$user->firstname} {$user->lastname}</p>
          <p><strong>Email:</strong> {$user->email}</p>
          <p><strong>Course:</strong> " . format_string($course->fullname) . "</p>
          <p><strong>Activity:</strong> " . format_string($activityname) . "</p>

          <h3 style='color:#e10018; margin-top:20px;'>Feedback Details</h3>

          <table width='100%' cellspacing='0' cellpadding='0' style='border-collapse:collapse;'>
            {$answers_html}
          </table>

        </div>

        <div style='text-align:center; padding:15px; font-size:12px; background:#fafafa; color:#777;'>
          IDSLMS | Automated Notification
        </div>

      </div>
    </div>
    ";

        // =========================
        // 📤 SEND HR MAIL (SAFE)
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
      <div style='max-width:600px; margin:auto; background:#fff; border-radius:12px; overflow:hidden;'>

        <div style='background:#e10018; color:#fff; padding:20px; text-align:center;'>
          <h2 style='margin:0;'>Thank You!</h2>
        </div>

        <div style='padding:25px;'>

          <p>Dear {$user->firstname},</p>

          <p>Thank you for submitting your feedback. Your response has been recorded successfully.</p>

          <p>Please continue your learning journey in the course.</p>

          <p style='margin-top:20px;'>Regards,<br>IDSLMS Team</p>

        </div>

        <div style='text-align:center; padding:15px; font-size:12px; background:#fafafa; color:#777;'>
          Automated System Email
        </div>

      </div>
    </div>
    ";

        email_to_user(
            $user,
            \core_user::get_noreply_user(),
            $usersubject,
            strip_tags($usermessage),
            $usermessage
        );
    }
}
=======
<?php

namespace local_mentor;

use core_user;

defined('MOODLE_INTERNAL') || die();

class observer
{
    public static function user_email(\core\event\role_assigned $event)
    {
        global $DB;

        error_log('role_assigned triggered');

        $userid = $event->relateduserid;
        $courseid = $event->courseid;
        $roleid = $event->objectid;

        $student = $DB->get_record('user', ['id' => $userid]);
        $course = $DB->get_record('course', ['id' => $courseid]);

        $context = \context_course::instance($courseid);

        if (!$context) {
            return;
        }

        // ✅ Message by role directly
        if ($roleid == 3 || $roleid == 4) {
            $mainmessage = 'Welcome New Mentor to Enroll the Course.';
        } elseif ($roleid == 5) {
            $mainmessage = 'A new employee has been enrolled in your assigned course.';
        } else {
            return;
        }

        // Teachers fetch
        $editingteachers = get_role_users(3, $context);
        $noneditingteachers = get_role_users(4, $context);

        $teachers = array_merge($editingteachers, $noneditingteachers);

        $uniqueTeachers = [];
        foreach ($teachers as $teacher) {
            $uniqueTeachers[$teacher->id] = $teacher;
        }

        $sender = $DB->get_record('user', ['id' => 2]);

        foreach ($uniqueTeachers as $teacher) {

            $subject = 'Enrollment Notification - IDSLMS';

            $messagehtml = '
        <div style="font-family:Arial,sans-serif; border:1px solid #ddd; padding:20px; max-width:600px;">

            <div style="background:#0d6efd; color:white; padding:12px; font-size:18px;">
                IDSLMS Enrollment Notification
            </div>

            <p style="color:#d63384;">
                <strong>Dear ' . $teacher->firstname . ',</strong>
            </p>

            <p>' . $mainmessage . '</p>

            <table style="width:100%; border-collapse:collapse;">

                <tr>
                    <td style="padding:8px; border:1px solid #ddd;">
                        <strong>User Name</strong>
                    </td>
                    <td style="padding:8px; border:1px solid #ddd; color:red;">
                        ' . $student->firstname . ' ' . $student->lastname . '
                    </td>
                </tr>

                <tr>
                    <td style="padding:8px; border:1px solid #ddd;">
                        <strong>Course Name</strong>
                    </td>
                    <td style="padding:8px; border:1px solid #ddd; color:blue;">
                        ' . $course->fullname . '
                    </td>
                </tr>

            </table>

            <p style="margin-top:20px;">
                Regards,<br>
                <strong>IDSLMS Team</strong>
            </p>

        </div>';

            $messagetext = $mainmessage . ' ' .
                $student->firstname . ' ' . $student->lastname .
                ' in ' . $course->fullname;

            email_to_user(
                $teacher,
                $sender,
                $subject,
                $messagetext,
                $messagehtml
            );
        }
    }

    //assignment  submission
    public static function assignment_submit_mail($event)
    {
        self::send_mail($event, 'Assignment');
    }

    //quiz subbmission
    public static function quiz_submit_mail($event)
    {
        self::send_mail($event, 'Quiz');
    }

    private static function send_mail($event, $activitytype)
    {
        global $DB;

        error_log($activitytype . ' mail triggered');

        $userid = $event->userid;
        $courseid = $event->courseid;

        if (!$userid || !$courseid) {
            error_log('Missing userid or courseid');
            return;
        }

        $student = $DB->get_record('user', ['id' => $userid]);
        $course = $DB->get_record('course', ['id' => $courseid]);

        if (!$student || !$course) {
            error_log('Student or course not found');
            return;
        }

        $context = \context_course::instance($courseid);

        $editingteachers = get_role_users(3, $context);
        $noneditingteachers = get_role_users(4, $context);

        $teachers = array_merge($editingteachers ?: [], $noneditingteachers ?: []);

        // Remove duplicates
        $uniqueTeachers = [];
        foreach ($teachers as $teacher) {
            $uniqueTeachers[$teacher->id] = $teacher;
        }

        // ✅ Sender (ID 12)
        $sender = $DB->get_record('user', ['id' => 12]);

        // =========================
        // 📧 MAIL TO TEACHERS
        // =========================
        foreach ($uniqueTeachers as $teacher) {

            if (empty($teacher->email)) {
                continue;
            }

            $subject = 'Employee ' . $activitytype . ' Submission Notification - IDSLMS';

            $messagetext = $student->firstname . ' submitted ' . $activitytype . ' in ' . $course->fullname;

            $messagehtml = '<p><b>' . $student->firstname . ' ' . $student->lastname . '</b> submitted ' . $activitytype . ' in <b>' . $course->fullname . '</b></p>';

            email_to_user($teacher, $sender, $subject, $messagetext, $messagehtml);
        }

        // =========================
        // 📧 MAIL TO STUDENT (NEW)
        // =========================
        if (!empty($student->email)) {

            $student_subject = "Your {$activitytype} Submission Successful - IDSLMS";

            $student_messagetext = "Your {$activitytype} has been submitted successfully in {$course->fullname}. Please continue with next activity.";

            $student_messagehtml = '
        <div style="font-family:Arial;padding:20px;">
            <h2 style="color:#e10018;">Submission Successful 🎉</h2>

            <p>Dear <b>' . $student->firstname . '</b>,</p>

            <p>
                Your <b>' . $activitytype . '</b> has been successfully submitted 
                in <b>' . $course->fullname . '</b>.
            </p>

            <p>
                Thank you! Please continue with the next activity 🚀
            </p>

            <br>
            <p><b>IDS LMS Team</b></p>
        </div>';

            $result = email_to_user(
                $student,
                $sender,
                $student_subject,
                $student_messagetext,
                $student_messagehtml
            );

            error_log("Student mail status: " . ($result ? 'SENT' : 'FAILED'));
        }
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

        // Ensure fullname() errors never come
        $user->firstnamephonetic = $user->firstnamephonetic ?? '';
        $user->lastnamephonetic  = $user->lastnamephonetic ?? '';
        $user->middlename        = $user->middlename ?? '';
        $user->alternatename     = $user->alternatename ?? '';

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
            \core_user::get_noreply_user(),
            $usersubject,
            strip_tags($usermessage),
            $usermessage
        );
    }
}
>>>>>>> Stashed changes
