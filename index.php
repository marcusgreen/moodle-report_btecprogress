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
 * Report on assignments that use the BTEC advanced grading type
 *
 * @since      2.9
 * @package    report_btecprogress
 * @copyright  Marcus Green 2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');


global $PAGE, $COURSE, $DB, $CFG;

$courseid = required_param('id', PARAM_INT);
$groupid = optional_param('group', null, PARAM_INT);
$zoom = optional_param('zoom',null,PARAM_INT);

// Check permissions
require_login($courseid);
/* 
 * report
 * course
 * standard
 * base
 * 
 * 
 */
//if($zoom==true){
//$PAGE->set_pagelayout('base');
//}else{
$PAGE->set_pagelayout('report');
//}

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('dataTables', 'report_btecprogress');
$PAGE->requires->jquery_plugin('buttons_css', 'report_btecprogress');
$PAGE->requires->jquery_plugin('buttons_js', 'report_btecprogress');
$PAGE->requires->jquery_plugin('html5_js', 'report_btecprogress');
$PAGE->requires->jquery_plugin('fixedcolumns', 'report_btecprogress');

$PAGE->requires->jquery_plugin('buttons_foundation', 'report_btecprogress');



$PAGE->set_context(context_course::instance($COURSE->id));
$url = new moodle_url('/report/btecprogress/index.php');

$PAGE->set_url($url);
$PAGE->set_heading($COURSE->fullname);
$PAGE->set_title('btecprogress', 'report_btecprogress');
$html= $OUTPUT->header();

$PAGE->navigation->add(get_string('pluginname', 'report_btecprogress'), $url);
$report = new report_btecprogress();

$report->init($courseid);



$users = $report->get_students($courseid, $groupid);
$assigns = $report->get_all_assigns($courseid);

$html.= "<div class='coursename'>Course: ".$report->course->fullname."</div>";

$URL = $CFG->wwwroot . '/report/btecprogress/index.php';

$groups = array();
foreach ($report->groups as $id => $name) {
    $groups[$URL . '?id=' . $courseid . '&group=' . $id] = $name;
}

$default = $URL . '?id=' . $courseid;
$select = new url_select($groups, null, array($default => 'Select'));

if (isset($groupid)) {
    $select->selected = $URL . '?id=' . $courseid . '&group=' . $groupid;
}

$select->set_label('Group');

$html.= $OUTPUT->render($select);  

/* explains what the letters in the cells mean, e.g. N for No submission */
$html.="<div class='keycontainer'>".$report->get_key()."</div>";

$maxcriteria = $report->get_max_criteria($courseid);
$submissionstatus = $report->get_submission_status($courseid);
$html.= "<div class='gradecontainer'>";
$html.= "<table id='grades'>";
$html.= "<thead>";
$html.="<th>".get_string('firstname', 'report_btecprogress')."</th>";
$html.= "<th>".get_string('lastname', 'report_btecprogress')."</th>";

$assigncount = 0;
foreach ($assigns as $a) {
    $assigncount++;
    $assignment_name = $a->assignment_name;
    if (strlen($assignment_name) > 15) {
        $assignment_name = substr($assignment_name, 0, 15);
        $assignment_name = $assignment_name . "...";
    }
    $html.= "<th class='assignment' title='" . $a->assignment_name . "'>" . $assignment_name . "</th>";
    $criteria = $report->get_assign_criteria($a->coursemodid);
    foreach ($criteria as $c) {
        $html.= "<th class='criteria grade' title='$c->description'>" . $c->shortname . "</th>";
    }
}
$html.= "<th class='total'>Total</th></tr>";

$html.= "</thead>";
if ($assigncount > 0) {
    foreach ($users as $user) {
        $html.= "<tr><td>" . $user->firstname . "</td>";
        $html.= "<td>" . $user->lastname . "</td>";
        $ug = $report->get_all_usergrades($user, $assigns);
        foreach ($assigns as $a) {
            $criteria = $report->get_assign_criteria($a->coursemodid);
            $usergrade = $report->get_user_grade($user, $a);
            $tag = "<td>";
            if ($usergrade->grade == 'R') {
                $tag = "<td class='refer'>";
            } elseif ($usergrade->grade == 'P') {
                $tag = "<td class='achieved'>";
            } elseif($usergrade->grade=='!'){
                $tag="<td class='newsubmission'>";
            }

            $textclass = "";
            if ($usergrade->grade == '!') {
               // $textclass = 'newsubmission';
            }
            $rownum = get_assign_rownum($a->coursemodid, $user->userid);
            $link = "<a href=../../mod/assign/view.php?id=" . $a->coursemodid . "&rownum=" . $rownum . "&action=grade class='$textclass'>";
  
            if($usergrade->grade=="N"){
                $html.="<td class='nosubmission'>" . $usergrade->grade . "</a></td>";
            }else{
               $html.= $tag . $link . $usergrade->grade . "</a></td>";
            }

            foreach ($criteria as $c) {
                $g = $report->get_user_criteria_grades($user->userid, $a->coursemodid, $c->criteriaid);
                if ($g == 'A') {
                    $tag = '<td class="achieved">';
                } else if ($g == 'N') {
                    $tag = '<td class="nosubmission">';
                } else {
                    $tag = '<td>';
                }
                $html.= $tag;
                $html.= $g;
                $html.= '</td>';
            }
        }

        /* calculated grade for all assignments */
        $overallgrade = $report->num_to_letter($ug->modulegrade);
        $tag = '<td class=' . $report->grade_style($overallgrade) . '>';
        $html.= $tag;
        $html.= $overallgrade;
        $html.= "</td>";
        $html.= "</tr>";
    }
}
$html.="</table>";
$html.="</div>";
print $html;

$report->get_table_script($report->emptytable_message);
echo $OUTPUT->footer();
