<?php

/**
 * Send messages in the background.
 * 
 * @package local_mentor\task
 * @author Mohan Lal Sharma <mohan.sharma@idslogic.com>
 */

namespace local_mentor\task;

defined('MOODLE_INTERNAL') || die();

class send_notification_task extends \core\task\adhoc_task
{

    public function execute()
    {
        global $DB;

        $data = (array)$this->get_custom_data();

        \local_mentor\message_manager::process($data);
    }
}
