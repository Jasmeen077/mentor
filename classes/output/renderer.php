<?php

namespace local_mentor\output;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base
{

    public function render_mentor_cards($mentors)
    {
        $data = [
            'mentors' => array_values($mentors)
        ];

        return $this->render_from_template('local_mentor/mentor_cards', $data);
    }
}
