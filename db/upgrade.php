<?php

/**
 * Upgrade code for local mentor
 *
 * @package local_mentor
 * @copyright  2026 Mohan Lal Sharma <mohan.sharma@idslogic.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_mentor_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2026042402) {
        $table = new xmldb_table('local_mentor_rates_log');

        // Add userid field (safe with default).
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'mentor_id');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add index for performance.
        $index = new xmldb_index('userid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid']);

        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2026042402, 'local', 'mentor');
    }

    return true;
}
