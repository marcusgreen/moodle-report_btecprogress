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
 * Serve question type files
 *
 * @since      2.9
 * @package    report_btecprogress
 * @copyright  Marcus Green 2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');


global $PAGE, $COURSE, $DB, $CFG;
$PAGE->set_context(context_course::instance($COURSE->id));
$PAGE->set_url('/report/btecprogress/index.php');
$PAGE->set_pagelayout('report');
$PAGE->set_heading($COURSE->fullname);
$PAGE->set_title('btecprogress', 'report_btecprogress');
echo $OUTPUT->header();
$report = new report_btecprogress();
$report->init();


$assignments=$report->get_user_assignments();
//$grades=$report->get_grades();


foreach($assignments as $assignment){
    print $assignment->username." ";
    print $assignment->assignment_name;
        print "</br>";
}
/*
print "<table>";
foreach($records as $record){
    print "<tr><td>";
    print $record->username;
    print "</td><td>";
    print $record->assignment_name;
    print "</td><td>";
    print $record->overalgrade;
    print "</td></tr>";
}
print "</table>";
echo $OUTPUT->footer();

function get_course_grade(array $records){
    foreach($records as $record){
    }
}

*/
 
