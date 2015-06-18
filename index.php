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

$courseid = required_param('id', PARAM_INT);

// Check permissions
require_login($courseid);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('dataTables', 'report_btecprogress');
$PAGE->requires->jquery_plugin('footable', 'report_btecprogress');
$PAGE->requires->jquery_plugin('tooltip', 'report_btecprogress');

//require_capability('report/completion:view', $context);

$PAGE->set_context(context_course::instance($COURSE->id));
$PAGE->set_url('/report/btecprogress/index.php');
$PAGE->set_pagelayout('report');
$PAGE->set_heading($COURSE->fullname);
$PAGE->set_title('btecprogress', 'report_btecprogress');
echo $OUTPUT->header();
$report = new report_btecprogress();

$report->init($courseid);



$users=$report->get_students($courseid);
$assigns=$report->get_all_assigns($courseid);



print "<br/><br/>";

print $report->course->fullname;


//$criteria=$report->get_assign_criteria($courseid);

$maxcriteria=$report->get_max_criteria($courseid);

//$assigncount=count($userassigns);
$submissionstatus=$report->get_submission_status($courseid);


print "<table class='grades' border='1' width=80%>";
echo "<thead>";
echo "<tr>";
echo "<th class=nameheader >First Name</th>";
echo "<th class=nameheader >Last Name</th>";
$counter=0;
foreach ($assigns as $a){
    $counter++;
    if($counter % 2 ==0){
        $assignclass="assigneven";
    }else{
        $assignclass="assignodd";            
    }
    print "<th class=".$assignclass.">".$a->assignment_name ."</th>";
    $criteria=$report->get_assign_criteria($a->coursemodid);
  
    foreach($criteria as $c){  
        print "<th class=".$assignclass.">".$c->shortname."</th>";
    }  

}
print "<th class=totalcol>Total</th></tr>";
echo "</thead>";

foreach($users as $user){
    print "<tr><td class=username footable-sortable>".$user->firstname."</td>";
    print "<td class=username>".$user->lastname;
    print '<span class="footable-sort-indicator" > </span>';
            
            
    echo "</td>";
    $coursegrade=4;
    $overallgrade=4;
    $ug=$report->get_all_usergrades($user,$assigns);
    
       foreach ($assigns as $a){
       $criteria=$report->get_assign_criteria($a->coursemodid);
       $usergrade= $report->get_user_grade($user,$a);
       
       $link="<a href=../../mod/assign/view.php?id=".$a->coursemodid."&rownum=0&action=grade>";
       print "<td>".$link.$usergrade->grade."</a></td>";
      // print "<td>".$usergrade->grade."</td>";
       foreach($criteria as $c){
                 
         $g=$report->get_user_criteria_grades($user->userid,$a->coursemodid,$c->criteriaid);
         if($g=='A'){
             $tag='<td class="achieved">';
         }else if ($g=='N'){
             $tag='<td class="notmet">';
         }else{
             $tag='<td>';
         }
         print $tag;
         print $g;
         print '</td>';
        } 

    }
     
   /*calculated grade for all assignments */
    $overallgrade=$report->num_to_letter($ug->modulegrade);
   $tag='<td class='.$report->grade_style($overallgrade).'>';
   print $tag;
   print $overallgrade;
   
    print "</tr>";
}



print "</table>";
echo "<script>$('.grades').dataTable({"
. "aaSorting: [],"
. "iDisplayLength:20,"
. "aLengthMenu : [20,30],"    
. "bJQueryUI : true"
        . "});"
        . "</script>";


/*
 * http://themergency.com/footable-demo/responsive-container.htm
    $(document).ready(function() { 
                 oTable = $('#personsList').dataTable({
                "bJQueryUI": true,
                "sPaginationType": "full_numbers",
                "iDisplayLength": 25,
                "aLengthMenu": [25, 50, 100, 150],
                "aaSorting": [[0,'asc']],      //Sorts 1st column asc              
                "bPaginate": false              
            });
 * 
 * 
 * 
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
