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
 * Version Details
 *
 * @package    auth_edsembli
 * @author     Tim Martinez <tim.martinez@ignitecentre.ca>
 * @copyright  2021 Tim Martinez <tim.martinez@ignitecentre.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2021091400;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2021051100;        // Requires this Moodle version. (3.11)
$plugin->component = 'auth_edsembli';       // Full name of the plugin (used for diagnostics)

// This is a list of plugins, this plugin depends on (and their versions).                                                          
$plugin->dependencies = [  
    'report_edsembli' => 2021080800                                                                                                 
];