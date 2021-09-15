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
 * A library of functions for getting EdSembli API data.
 *
 * @package    auth_edsembli
 * @author     Tim Martinez <tim.martinez@ignitecentre.ca>
 * @copyright  2021 Tim Martinez <tim.martinez@ignitecentre.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_edsembli;

defined('MOODLE_INTERNAL') || die();

/**
 * A library of functions for getting EdSembli API data.
 *
 * @author Tim Martinez <tim.martinez@ignitecentre.ca>
 */
class webservice {

    private $endpoint_url = '';
    
    private $client = null;

    /**
     * Constructor
     *
     * @param string $endpoint the path to the EdSembli URL. If null, use the settings
     */
    public function __construct($endpoint = null) {
        //$this->endpoint_url = 'https://ws-staging.edsembli.com/AB/Private/IGNITE/Webservice/Integration/mwWebSrvStAc.asmx?WSDL';
        $this->endpoint_url = get_config('auth_edsembli', 'endpoint');
        
        $this->client = new \SoapClient(__DIR__.'/../wsdl.xml', array('soap_version' => SOAP_1_2));
    }
    
    /*
     * Dump a list of functions
     */
    public function getFunctions() {
        return $this->client->__getFunctions();
    }
    
    /*
     * Returns an array of staff members
     * 
     * @returns array A list of staff
     */
    public function GetBoardStaff() {
        $response = $this->client->GetBoardStaff();

        $ret = new \SimpleXMLElement('<root>'. str_replace('diffgr:diffgram', 'diffgr', $response->GetBoardStaffResult->any . '</root>'));
     
        return $ret->diffgr->StAc_Teacher->Teachers->Teacher_Details;
    }
    
    /*
     * Returns an array of students
     * 
     * @returns array A list of students
     */
    public function GetStudents($schoolNum) {

        $response = $this->client->GetStudents(array('schoolNum' => $schoolNum, 'stuInitial' => ''));

        $ret = new \SimpleXMLElement('<root>'. str_replace('diffgr:diffgram', 'diffgr', $response->GetStudentsResult->any . '</root>'));
        
        //TODO: Log errors here

        return $ret->diffgr->StAc_Student->Students->Student_Details;
    }
    
    /*
     * Returns an array of teacher classes
     * 
     * #returns array A list of classes and the teachers assigned
     */
    public function GetTeacherClasses($schoolNum) {
        
        $response = $this->client->GetTeacherClasses(array('schoolNum' => $schoolNum, 'teacherCode' => ''));
        
        $ret = new \SimpleXMLElement('<root>'. str_replace('diffgr:diffgram', 'diffgr', $response->GetTeacherClassesResult->any . '</root>'));
        
        //TODO: Log errors here
        return $ret->diffgr->StAc_TeacherClass->Teacher_Classes->Teacher_Class_Details;
    }
    
    /*
     * Returns an array of student classes
     * 
     * #returns array A list of student section enrollments
     */
    public function GetStudentClasses($schoolNum) {
        
        $response = $this->client->GetStudentClasses(array('schoolNum' => $schoolNum, 'stuCode' => ''));
        
        $ret = new \SimpleXMLElement('<root>'. str_replace('diffgr:diffgram', 'diffgr', $response->GetStudentClassesResult->any . '</root>'));
        
        //TODO: Log errors here
        return $ret->diffgr->StAc_StuClass->Student_Classes->Student_Class_Details;
    }
}
