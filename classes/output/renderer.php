<?php

namespace local_mentor\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Renderere class
 * 
 * @package local_mentor
 * @author 2026 Mohan Lal Sharma & Jasmeen Khanam <mohan.sharma@idslogic.com & jasmeen.khanam@idslogic.com>
 */
class renderer extends \core\output\renderer_base
{

    public function render_mentor_cards(): string|bool
    {

        $mentors = \local_mentor\mentor::get_mentors();
        $enroledcoursesandteachers = \local_mentor\helper::get_user_courses_with_teachers();

        foreach ($mentors as $mentor) {

            $mentor->bio = strip_tags($mentor->bio ?? 'N/A');
            $mentor->expertise = strip_tags($mentor->expertise ?? 'N/A');

            $rating = round($mentor->averagerating ?? 0);
            $mentor->stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
            // Verify if the current user can rate this mentor
            $mentor->can_rate = $enroledcoursesandteachers && $mentor->has_rate_count;
        }
        $data = [
            'mentors' => array_values($mentors)
        ];

        return $this->render_from_template('local_mentor/mentor_cards', $data);
    }
}
