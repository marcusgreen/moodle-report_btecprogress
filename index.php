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
$courseid = required_param('id', PARAM_INT);

$users=$report->get_students($courseid);
$assignments =$report->get_user_assignments($courseid);
$assigncount=count($assignments);
var_dump($assignments);

print "<table border=1><thead><tr><th>First Name</th><th>Last Name </th>";
foreach ($assignments as $assign){
    print "<th>".$assign->assignment_name ."</th>";
    
}
print "<th>Total</th></tr>";

foreach($users as $user){
    print "<tr><td>".$user->firstname."</td>";
    print "<td>".$user->lastname."</td>";
    foreach ($assignments as $assign){
        print "<td>".$assign->overallgrade."</td>";
    }
    print "<td>&nbsp;</td>";
    print "</tr>";
}
print "</table>";
