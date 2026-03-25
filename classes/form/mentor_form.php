<?php

namespace local_mentor\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class mentor_form extends \moodleform
{
    public function definition()
    {
        global $DB, $USER;

        $mform = $this->_form;

        // Hidden user ID
        $userid = $this->_customdata['userid'];

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        // Get courses enrolled by user
        $sql = "SELECT c.id, c.fullname
                  FROM {course} c
                  JOIN {enrol} e ON e.courseid = c.id
                  JOIN {user_enrolments} ue ON ue.enrolid = e.id
                 WHERE ue.userid = :userid
              ORDER BY c.fullname ASC";
        $courses = $DB->get_records_sql($sql, ['userid' => $USER->id]);

        $options = ['' => 'Select Course'];
        foreach ($courses as $course) {
            $options[$course->id] = $course->fullname;
        }

        // Course dropdown
        $mform->addElement('select', 'courseid', 'Course Name', $options);
        $mform->addRule('courseid', 'Required', 'required', null, 'client');

        // Rating
        $ratingoptions = [1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'];
        $mform->addElement('select', 'rating', 'Rating', $ratingoptions);
        $mform->addRule('rating', 'Required', 'required', null, 'client');

        // Comment
        $mform->addElement('textarea', 'reason', 'Comment', 'wrap="virtual" rows="3" cols="50"');
        $mform->setType('reason', PARAM_TEXT);
        $mform->addRule('reason', 'Required', 'required', null, 'client');

        $this->add_action_buttons(true, 'Submit Rating');
    }

    public function validation($data, $files)
    {
        $errors = [];

        if (empty($data['userid'])) {
            $errors['userid'] = 'User ID missing';
        }

        if (empty($data['courseid'])) {
            $errors['courseid'] = 'Please select a course';
        }

        if (empty($data['rating'])) {
            $errors['rating'] = 'Please provide a rating';
        }

        $wordcount = str_word_count(strip_tags($data['reason']));
        if ($wordcount < 30) {
            $errors['reason'] = 'Comment must be at least 30 words';
        } elseif ($wordcount > 200) {
            $errors['reason'] = 'Comment cannot exceed 200 words';
        }

        return $errors;
    }
}
