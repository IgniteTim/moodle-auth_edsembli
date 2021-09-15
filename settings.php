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
 * EdSembli authentication plugin settings
 *
 * @package   auth_edsembli
 * @author    Tim Martinez <tim.martinez@ignitecentre.ca>
 * @copyright 2021 Tim Martinez <tim.martinez@ignitecentre.ca>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    //What is our school number?
    $title = get_string('config_schoolnum', 'auth_edsembli');
    $desc = get_string('config_schoolnum_desc', 'auth_edsembli');
    $settings->add(new admin_setting_configtext('auth_edsembli/schoolnum', $title, $desc, 0, PARAM_INT));
    
    //Email domain suffix
    $title = get_string('config_emailsuffix', 'auth_edsembli');
    $desc = get_string('config_emailsuffix_desc', 'auth_edsembli');
    $settings->add(new admin_setting_configtext('auth_edsembli/emailsuffix', $title, $desc, 'ignitecentre.ca', PARAM_TEXT));
    
    //Endpoint URL
    $title = get_string('config_endpoint', 'auth_edsembli');
    $desc = get_string('config_endpoint_desc', 'auth_edsembli');
    $settings->add(new admin_setting_configtext('auth_edsembli/endpoint', $title, $desc, '', PARAM_URL));
}