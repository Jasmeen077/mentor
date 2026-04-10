<?php

namespace local_mentor;

use core_user;

defined('MOODLE_INTERNAL') || die();

class observer
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
}
