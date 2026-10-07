<?php
$string['pluginname'] = 'Mentor';
$string['mentor:view'] = 'View mentor plugin';
$string['mentors'] = 'mentors';
$string['mentorslist'] = 'Mentors List';
$string['welcome'] = 'Welcome to the Mentors Page, where you can provide ratings for the mentor of your ongoing course by selecting the relevant course name.';
$string['feedback_success'] = 'Feedback submitted successfully.';
$string['learnupskills'] = 'Learn & Upskills';
$string['course'] = 'Course';
$string['upandskillscategories'] = 'Learn & Upskills Categories';
$string['upandskillscategories_description'] = 'Select categories to be displayed in Learn & Upskills menu.';
$string['mentorsreport'] = 'Mentors Report';
$string['messageprovider:unenrol_notification'] =  'Unerolment Notification';
$string['totalratings'] = 'No. of Employee';
$string['rating'] = 'Rating';
$string['last_updated'] = 'Last Rated';
$string['actions'] = 'Actions';
$string['mentor_report_details'] = 'Mentor Report Details';
$string['mentor_report_details_description'] = 'Detailed report of mentor ratings and feedback.';
$string['viewdetails'] = 'View Details';
$string['feedbacksaved'] = 'Feedback saved successfully.';
$string['feedbacksaveerror'] = 'Error saving feedback. Please try again.';
$string['rate_mentors'] = 'Rate Mentors';
$string['mentorreportdetails'] = 'Mentor Report Details';
$string['timecreated'] = 'Date';
$string['course'] = 'Course';
$string['comment'] = 'Comment';
$string['alreadyrated'] = 'You have been already rated for this mentor';
$string['courseparticipants'] = 'Course Participants Report';
$string['courseandrole'] = 'Courses (Roles)';
$string['unenrol'] = 'Unenrol user';
$string['unenrolsuccessmessage'] = 'The user \'{$a->name}\' has successfully unenrol form the course: {$a->course}';
$string['permission_denied'] = 'You do not have permission to access this page';
$string['unenrolconfirm'] = 'Do you want to unenrol {$a->fullname} form the {$a->course}.';
$string['admin_notification_receiver'] = 'Admin Notification Receiver';
$string['admin_notification_receiver_desc'] = 'Select Admins for sending the notification copy';
$string['messageprovider:notification'] = 'Quiz attempt submitted notification';

/**
 * settings
 */
// Role Assigned
$string['role_assigned_messages'] = 'Role assigned message templates';
$string['role_assigned_messages_desc'] = 'Configure role assigned and unassigned message templates';
$string['role_assigned_body_template'] = 'Hi {firstname}, you assigned a new role ({role}) in {coursename}';
$string['role_assigned_subject_template'] = 'Hi {firstname}';

$string['role_assigned_subject'] = 'Role assigned subject';
$string['role_assigned_subject_desc'] = 'Use variables like {firstname}, {lastname}, {coursename}';

$string['role_assigned_body'] = 'Role assignment body';
$string['role_assigned_body_desc'] = 'Message body with placeholders like {role}, {firstname}, {lastname}, {email}, {coursename}, {courseid}';

// Role Unassigned
$string['role_unassigned_messages'] = 'Role unassigned message templates';
$string['role_unassigned_messages_desc'] = 'Configure role unassigned message templates';

$string['role_unassigned_subject'] = 'Role unassigned subject';
$string['role_unassigned_subject_desc'] = 'Use variables like {firstname}, {lastname}, {coursename}';
$string['role_unassigned_subject_template'] = 'Hi {firstname}';

$string['role_unassigned_body'] = 'Role unassignment body';
$string['role_unassigned_body_desc'] = 'Message body with placeholders like {role}, {firstname}, {lastname}, {email}, {coursename}, {courseid}';
$string['role_unassigned_body_template'] = 'Hi {firstname}, you assigned a new role ({role}) in {coursename}';

// Quiz Completion
$string['attempt_submitted_message'] = 'Quiz completion message template';
$string['attempt_submitted_message_desc'] = 'Configure message shown when a user completes a quiz';

$string['attempt_submitted_subject'] = 'Quiz completion subject';
$string['attempt_submitted_subject_desc'] = 'Use variables like {firstname}, {lastname}, {quizname}, {coursefullname}';
$string['attempt_submitted_subject_template'] = 'Quiz completed: {quizname}';

$string['attempt_submitted_body'] = 'Quiz completion message body';
$string['attempt_submitted_body_desc'] = 'Placeholders: {firstname}, {lastname}, {quizname}, {coursename}, {attemptid}, {finalgrade}';
$string['attempt_submitted_body_template'] = 'Hi {firstname}, congratulations! You have successfully completed the quiz <b>{quizname}</b> in course <b>{coursefullname}</b>. Your final grade is <b>{finalgrade}</b>.';

// Assignment Completion
$string['assessable_submitted_message'] = 'Assignment completion message template';
$string['assessable_submitted_message_desc'] = 'Configure message shown when a user completes an assignment';

$string['assessable_submitted_subject'] = 'Assignment completion subject';
$string['assessable_submitted_subject_desc'] = 'Use variables like {firstname}, {lastname}, {assignmentname}, {coursename}';
$string['assessable_submitted_subject_template'] = 'Assignment completed: {assignmentname}';

$string['assessable_submitted_body'] = 'Assignment completion message body';
$string['assessable_submitted_body_desc'] = 'Placeholders: {firstname}, {lastname}, {assignmentname}, {coursename}, {grade}, {feedback}';
$string['assessable_submitted_body_template'] = 'Hi {firstname}, congratulations! You have successfully completed the assignment <b>{assignmentname}</b> in course <b>{coursefullname}</b>. Your grade is <b>{grade}</b>.';

// Feedback 
$string['response_submitted_message'] = 'Feedback Submitted Notification';
$string['response_submitted_message_desc'] = 'Configure the message sent when a user submits a response.';

$string['response_submitted_subject'] = 'Feedback submitted subject';
$string['response_submitted_subject_desc'] = 'Use variables like {firstname}, {lastname}, {coursename}.';
$string['response_submitted_subject_template'] = 'New response submitted in {coursename}';

$string['response_submitted_body'] = 'Feedback submitted message body';
$string['response_submitted_body_desc'] = 'Placeholders: {firstname}, {lastname}, {coursename}, {response}, {submittedon}';
$string['response_submitted_body_template'] = 'Hi {firstname}';


