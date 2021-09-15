<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Sync users from EdSembli with Moodle
 *
 * @package    auth_edsembli
 * @author     Tim Martinez <tim.martinez@ignitecentre.ca>
 * @copyright  2021 Tim Martinez <tim.martinez@ignitecentre.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_edsembli\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Sync users from EdSembli with Moodle
 *
 * @package    auth_edsembli
 * @copyright  2021 Tim Martinez <tim.martinez@ignitecentre.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_users extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('sync_users', 'auth_edsembli');
    }

    public function execute() {
        global $DB;

        $ws = new \auth_edsembli\webservice();

        /*
         * First create a temp table to hold the data coming from EdSembli.
         * We do this so that we can go back and look for deactivated accounts
         * later.
         */

        $dbman = $DB->get_manager();
        /*
        /// Define table user to be created
        $table = new xmldb_table('tmp_extuser');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('userid', XMLDB_INDEX_UNIQUE, array('userid'));

        mtrace('Creating Temp Table...');
        $dbman->create_temp_table($table);
*/        


        mtrace('Creating/Updating staff accounts...');
        $teachers = $ws->GetBoardStaff();

        foreach ($teachers as $teacher) {
            \auth_edsembli\user::sync($teacher, 'staff');
        }
  
        mtrace('Creating/Updating student accounts...');
        $students = $ws->GetStudents(get_config('auth_edsembli', 'schoolnum'));
        foreach ($students as $student) {
            \auth_edsembli\user::sync($student, 'student');
        }
        mtrace('Done!');
    }

}
