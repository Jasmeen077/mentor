<?php

namespace local_mentor\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class mentor_form extends \moodleform
{
    public function definition()
    {
        $mform = $this->_form;

        $userid = $this->_customdata['userid'];
        $courses = $this->_customdata['courses'];

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        $options = ['' => 'Select Course'];
        foreach ($courses as $id => $name) {
            $options[$id] = $name;
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

        if (strlen($data['reason']) < 30) {
            $errors['reason'] = 'Comment must be at least 30 letters';
        } elseif (strlen($data['reason']) > 300) {
            $errors['reason'] = 'Comment cannot exceed 300 letters';
        }

        return $errors;
    }
}
