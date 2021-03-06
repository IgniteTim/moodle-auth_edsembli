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
 * Contains functions for syncing user accounts
 *
 * @package    auth_edsembli
 * @author     Tim Martinez <tim.martinez@ignitecentre.ca>
 * @copyright  2021 Tim Martinez <tim.martinez@ignitecentre.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_edsembli;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/report/edsembli/classes/base.php");

/**
 * Contains functions for syncing user accounts
 *
 * @author Tim Martinez <tim.martinez@ignitecentre.ca>
 */
class user {
    /*
     * Sync the supplied user.
     * 
     * @param SimpleXMLElment $user The user object from EdSembli
     * @param string $type The type of user we're processing (staff or student)
     * @return int The ID of the user from user table.
     */

    public static function sync($sisuser, $type) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/user/lib.php');

        //Get the person's ID based on the type of user
        switch ($type) {
            case 'staff':
                $userid = 'T' . $sisuser->Teacher_Code->__toString();
                break;
            case 'student':
                $userid = 'S' . $sisuser->Student_Code->__toString();
        }
        //First check to see if the user exists, first by unique ID.
        if (!$user = $DB->get_record('user', array('idnumber' => $userid))) {
            //The user doesn't exist. 
            //Create a user object
            $user = new \stdClass();
            $user->id = 0;
            $user->firstname = $sisuser->Usual_Name->__toString();
            if ($user->firstname == '') {
                $user->firstname = $sisuser->First_Name->__toString();
            }
            $user->lastname = $sisuser->Last_Name->__toString();
            $user->idnumber = $userid;
            switch ($type) {
                case 'staff':
                    $user->department = 'Staff';
                    break;
                case 'student':
                    $user->department = 'Student - Grade ' . $sisuser->Grade_Level->__toString();
                    break;
                default:
                    $user->department = 'Other User';
            }
            $user->modified = time();
            $pwd = self::randomPassword(10, true);
            $user->password = hash_internal_user_password($pwd);
            $user->password_clear = $pwd;
            $user->confirmed = 1;
            $user->suspended = 0;
            $user->mnethostid = 1;
            $user->auth = 'edsembli';

            //Let's generate a user name
            $username = self::generate_user_name($user->firstname, $user->lastname, 0, $type);

            $user->username = $username;

            $email = $username . '@' . get_config('auth_edsembli', 'emailsuffix');
            $user->email = $email;
            if ($username == '') {
                \report_edsembli\user::create_update(0, EDSEMBLI_USER_USERNAMEEXISTS, time(), $user);
                return EDSEMBLI_USER_USERNAMEEXISTS;
            }

            //Let's do a check to make sure the e-mail doesn't exist. If it does, let's throw an error           
            if ($check = $DB->get_record('user', array('email' => $email), 'id')) {
                \report_edsembli\user::create_update(0, EDSEMBLI_USER_EMAILEXISTS, time(), $user);
                return EDSEMBLI_USER_EMAILEXISTS;
            }


            $id = user_create_user($user, false);
            if ($id > 0) {
                //Log the password.
                $pwdrecord = new \stdClass();
                $pwdrecord->userid = $id;
                $pwdrecord->password = $pwd;
                $pwdrecord->timemodified = time();
                $DB->insert_record('auth_edsembli', $pwdrecord);
                \report_edsembli\user::create($id, EDSEMBLI_SUCCESS, time(), $user);
            } else {
                \report_edsembli\user::create_update(0, EDSEMBLI_UNKNOWNERROR, time(), $user);
            }

            //We're created the user. Return the ID
            return $id;
        } else {
            //We found a match to the user. Update the info if needed
            $doupdate = false;
            $newusername = '';

            $fn = $sisuser->Usual_Name->__toString();
            if ($fn == '') {
                $fn = $sisuser->First_Name->__toString();
            }
            if ($user->firstname != $fn) {
                $user->firstname = $fn;
                $doupdate = true;
            }

            if ($user->lastname != $sisuser->Last_Name->__toString()) {
                $user->lastname = $sisuser->Last_Name->__toString();
                $doupdate = true;
            }

            if ($doupdate) {
                /*
                 * The user's name has changed. Let's generate a new username and
                 * see if we need to update.
                 */
                $newusername = self::generate_user_name($user->firstname, $user->lastname, $user->id, $type);
                if ($type == 'staff' && $newusername == '') {
                    $user->username = $newusername;
                    \report_edsembli\user::update($user->id, EDSEMBLI_USER_USERNAMEEXISTS, time(), $user);
                    return EDSEMBLI_USER_EMAILEXISTS;
                }
                if ($newusername != $user->username) {
                    $user->username = $newusername;
                    $user->email = $newusername . '@ignitecentre.ca';
                    $user->email = $newusername . '@' . get_config('auth_edsembli', 'emailsuffix');
                }
            }

            if ($type == 'student') {
                //Check the department
                $department = 'Student - Grade ' . $sisuser->Grade_Level->__toString();
                if ($user->department != $department) {
                    $user->department = $department;
                    $doupdate = true;
                }
            }

            if ($doupdate) {
                $user->modified = time();
                user_update_user($user);
                \report_edsembli\user::update($user->id, EDSEMBLI_SUCCESS, time(), $user);
            }
        }
    }

    /*
     * Generate a unique user name
     * 
     * @param string $fname User First Name
     * @param string $sname User Last Name
     * @param int $userid The ID of the user (if we're checking an existing user)
     * @param string $userType The type of user (staff or student)
     * @return string The unique user name
     */

    private static function generate_user_name($fname, $sname, $userid = 0, $userType) {
        global $DB;

        $fname = preg_replace("/[^A-Za-z0-9-]/", "", $fname);
        $sname = preg_replace("/[^A-Za-z0-9-]/", "", $sname);

        $sql = 'SELECT id FROM {user} WHERE username = ? AND deleted = ? AND id <> ?';

        if ($userType == 'staff') {
            $finalusername = $fname . '.' . $sname;
            //Since staff accounts use the full name, if there's a conflict, let's fail.  
            $params = array($finalusername, 0, $userid);
            if ($user = $DB->get_record_sql($sql, $params)) {
                //A record exists with the same user name. Not good. Let's bail.
                return '';
            }
        } else {
            //Generate a student ID
            $username = substr($fname, 0, 1) . '.' . $sname;
            $finalusername = $username;
            $i = 1;
            $params = array($finalusername, 0, $userid);
            while ($user = $DB->get_record_sql($sql, $params)) {
                $finalusername = $username . $i;
                $params = array($finalusername, 0, $userid);
                $i++;
            }
        }
        return trim(\core_text::strtolower($finalusername));
    }

    /*
     * Generate a random, easy to remember password.
     */

    private static function randomPassword($len = 8, $ucfirst = false, $spchar = false) {
        /* Programmed by Christian Haensel
         * christian@chftp.com
         * http://www.chftp.com
         *
         * Exclusively published on weberdev.com.
         * If you like my scripts, please let me know or link to me.
         * You may copy, redistribute, change and alter my scripts as
         * long as this information remains intact.
         *
         * Modified by Josh Hartman on 2010-12-30.
         * Last modified: 2019-10-04
         * Thanks to JKDos for suggesting improvements.
         */
        if ($len >= 6 && ( $len % 2 ) !== 0) { // Length parameter must be greater than or equal to 6, and a multiple of 2
            $len = 8;
        }
        $length = $len - 2; // Makes room for a two-digit number on the end
        $conso = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z');
        $vocal = array('a', 'e', 'i', 'o', 'u');
        $spchars = array('!', '@', '#', '$', '%', '^', '*', '&', '*', '-', '+', '?');
        $password = '';
        srand((double) microtime() * 1000000);
        $max = $length / 2;
        for ($i = 1; $i <= $max; $i++) {
            $password .= $conso[rand(0, 19)];
            $password .= $vocal[rand(0, 4)];
        }
        if ($spchar == true) {
            $password = substr($password, 0, -1) . $spchars[rand(0, 11)];
        }
        $password .= rand(10, 99);
        if ($ucfirst == true) {
            $password = ucfirst($password);
        }
        return $password;
    }

}
