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
 * My Feedback Report.
 *
 * @package   report_btecprogress
 * @author    Jessica Gramp <j.gramp@ucl.ac.uk>
 * @credits   Based on original work report_mygrades by David Bezemer <david.bezemer@uplearning.nl> which in turn is based on 
 * 			  block_btecprogress by Karen Holland, Mei Jin, Jiajia Chen. Also uses SQL originating from Richard Havinga 
 * 			  <richard.havinga@ulcc.ac.uk>. The code for using an external database is taken from Juan leyva's
 * 			  <http://www.twitter.com/jleyvadelgado> configurable reports block.
 *            The idea for this reporting tool originated with Dr Jason Davies <j.p.davies@ucl.ac.uk> and 
 *            Dr John Mitchell <j.mitchell@ucl.ac.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_btecprogress_extend_navigation(global_navigation $navigation) {
    $url = new moodle_url('/report/btecprogress/index.php', array('course' => $course->id));
    $navigation->add(get_string('pluginname', 'report_btecprogress'), $url, null, null, new pix_icon('i/report', ''));
}

function report_btecprogress_extend_navigation_user($navigation, $user, $course) {
    $url = new moodle_url('/report/btecprogress/index.php', array('userid' => $user->id));
    $navigation->add(get_string('pluginname', 'report_btecprogress'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
}

function report_btecprogress_extend_navigation_course($navigation, $course, $context) {
    // if (has_capability('coursereport/btecprogress:view', $context)) {
    $url = new moodle_url('/report/btecprogress/index.php', array('id' => $course->id));
    $navigation->add(get_string('pluginname', 'report_btecprogress'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    // }
}

class report_btecprogress {

    /**
     * Initialises the report and sets the title
     */
    public function init() {
        $this->title = get_string('title', 'report_btecprogress');
    }
 
    
public function get_students($courseid) {
    global $DB;
    return $DB->get_records_sql('SELECT stu.id AS userid, stu.idnumber AS idnumber, stu.firstname, stu.lastname, stu.username AS student
        FROM {user} AS stu
        JOIN {user_enrolments} ue ON ue.userid = stu.id
        JOIN {enrol} enr ON ue.enrolid = enr.id
        WHERE enr.courseid = ?
        ORDER BY lastname ASC, firstname ASC, userid ASC', array($courseid));
}

public function get_course_assignments($courseid){
    
}
    
 public function get_user_assignments(){
     global $DB;
     $sql="select gbc.id,gbc.sortorder, u.username, c.shortname as course ,a.name as assignment_name,  gbf.remark
as overallfeedback, 
case gg.rawgrade 
when 1 then  'R'
when 2 then  'P'
when 3 then  'M'
when 4 then  'D'
end
as overallgrade


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
s.name='BTEC' and gg.rawgrade is not null group by username";
       $records = $DB->get_records_sql($sql);
        return $records;
     
 }
   public function get_grades() {
        global $DB;
        $sql = "select gbc.id, gbc.sortorder, u.username, c.shortname as course ,a.name as assignment_name,  gbc.shortname as criteria, gbf.remark, 
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
        $records = $DB->get_records_sql($sql);
        return $records;
        //admin_externalpage_setup('btecprogress', '', null, '', array('pagelayout' => 'report'));
        //$reportbtecprogress = new report_btecprogress_renderable();
    }

    /**
     * Gets whether or not the module is installed and visible
     *
     * @param str $modname The name of the module
     * @return bool true if the module exists and is not hidden in the site admin settings,
     *         otherwise false
     */
    public function mod_is_available($modname) {
        global $remotedb;
        $installedplugins = core_plugin_manager::instance()->get_plugins_of_type('mod');
        // Is the module installed?
        if (array_key_exists($modname, $installedplugins)) {
            // Is the module visible?
            if ($remotedb->get_field('modules', 'visible', array('name' => $modname
                    ))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
