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

$url = new moodle_url("/report/btecprogress/index.php", $params);
$id=999;
$PAGE->set_url('/report/btecprogress/index.php');
$PAGE->set_pagelayout('report');
$PAGE->set_heading($COURSE->fullname);
$PAGE->set_title('btecprogress', 'report_btecprogress');
echo $OUTPUT->header();
$report = new report_btecprogress();
$report->init();
echo $OUTPUT->footer();


 function get_grades(){
 global $DB;
$sql="select gbc.sortorder, u.username, c.shortname as course ,a.name as assignment_name,  gbc.shortname as criteria, gbf.remark, 
if( gbf.score=0,'No','Y' ) as achieved, 
case gg.rawgrade 
when 1 then  'R'
when 2 then  'P'
when 3 then  'M'
when 4 then  'D'
end

as overalgrade
from  gradingform_btec_fillings gbf 
join gradingform_btec_criteria gbc on gbc.id=gbf.criterionid
join assign as a on a.id=gbf.instanceid
join assignfeedback_comments afc on a.id=afc.assignment
join course_modules as cm on a.id=cm.instance
join course as c on cm.course=c.id
join modules as m on cm.module=m.id
join grade_items gi on cm.instance=gi.iteminstance
join scale s on gi.gradetype=s.id
join grade_grades gg on gi.id=gg.itemid
join user u on gg.userid=u.id
where m.name='assign' and 
s.name='BTEC' and gg.rawgrade is not null";
$records=$DB->get_records_sql($sql);
var_dump($records);
    admin_externalpage_setup('btecprogress', '', null, '', array('pagelayout' => 'report'));
$reportbtecprogress = new report_btecprogress_renderable();

}
