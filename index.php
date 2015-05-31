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
$courseid = required_param('id', PARAM_INT);
$report->init($courseid);


$users=$report->get_students($courseid);
$assigns=$report->get_all_assigns($courseid);


print "<br/><br/>";


//$criteria=$report->get_assign_criteria($courseid);

$maxcriteria=$report->get_max_criteria($courseid);

//$assigncount=count($userassigns);
$submissionstatus=$report->get_submission_status($courseid);


print "<table border=1><thead><tr><th>First Name</th><th>Last Name </th>";
foreach ($assigns as $a){
    print "<th>".$a->assignment_name ."</th>";
    $criteria=$report->get_assign_criteria($a->coursemodid);
  
    foreach($criteria as $c){
  
        print "<th>".$c->shortname."</th>";
    }  

}
print "<th>Total</th></tr>";

foreach($users as $user){
    print "<tr><td>".$user->firstname."</td>";
    print "<td>".$user->lastname."</td>";
    $coursegrade=4;
    $overallgrade=4;
    $ug=$report->get_all_usergrades($user,$assigns);
       foreach ($assigns as $a){
       $criteria=$report->get_assign_criteria($a->coursemodid);
       $usergrade= $report->get_user_grade($user,$a);
       print "<td>".$report->num_to_letter($usergrade->grade)."</td>";
      // print "<td>".$usergrade->grade."</td>";
       foreach($criteria as $c){
                 
         $g=$report->get_user_criteria_grades($user->userid,$a->coursemodid,$c->criteriaid);
         if($g=='A'){
             $tag='<td class="achieved">';
         }else{
             $tag='<td>';
         }
         print $tag;
         print $g;
         print '</td>';
        } 

    }
   print "<td>".$report->num_to_letter($ug->modulegrade)."</td>";
    print "</tr>";
}
print "</table>";

/*
print "<table border=1><thead><tr><th>First Name</th><th>Last Name </th>";
foreach ($assignments as $assign){
    print "<th>".$assign->assignment_name ."</th>";
    
}
print "<th>Total</th></tr>";

foreach($users as $user){
    print "<tr><td>".$user->firstname."</td>";
    print "<td>".$user->lastname."</td>";
    $overallgrade=0;
    foreach ($assignments as $assign){
        print intval($assign->overallgrade);
        switch (intval($assign->overallgrade)){
            case 1;
                $overallgrade='R';
                break;
            case 2;
                $overallgrade='P';
                break;
            case 3;
                $overallgrade='M';
                break;
            case 4;
                $overallgrade='D';    
        }
        print "<td>".$overallgrade."</td>";
    }
    print "<td>&nbsp;</td>";
    print "</tr>";
}
print "</table>";
*/
