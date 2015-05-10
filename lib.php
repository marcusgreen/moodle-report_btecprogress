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
    
    private  $submissions;
    private  $grades;

    /**
     * Initialises the report and sets the title
     */
    public function init($courseid) {
        $this->title = get_string('title', 'report_btecprogress');
        $this->submissions =$this->get_submissions($courseid);
        $this->grades= $this->get_grades($courseid);
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

 public function get_submission_status($courseid){
 /*get list of submissions */    
$sql="select asb.id asbid,a.id as assignid,u.id userid,c.id courseid,asb.status asbstatus FROM assign_submission AS asb
JOIN assign AS a ON a.id = asb.assignment
JOIN user AS u ON u.id = asb.userid
JOIN course AS c ON c.id = a.course
where c.id=? and asb.status='submitted'";
global $DB;
$records = $DB->get_records_sql($sql,array($courseid));
 return $records;
    
 }  
 
 public function get_user_sub_status($user,$assign,$submissionstatus){
     $status="N";
     foreach($submissionstatus as $s){
         if (($user->userid==$s->userid )&& ($assign->assignid == $s->assignid)){
             $status="!";
             return $status;
         }         
     }
     return $status;     
 }
 
 
 public function get_submissions($courseid){
     global $DB;
   /*  $sql="select gbc.id as gbcid,gbc.sortorder,cm.id as coursemodid,
         u.id as userid, u.username, c.shortname as course ,
         a.id as assignid,a.name as assignment_name,  gbf.remark
as overallfeedback, 
gg.rawgrade as overallgrade
    * */
     
$sql="select asub.id as asubid,cm.id as coursemodid,a.id as assignid,c.shortname,u.id as userid,u.username,a.name,ag.grade from assign_submission as asub
join assign as a on a.id =asub.assignment
join course_modules as cm on cm.instance=a.id and cm.module=1
join grade_items as gi on gi.iteminstance=cm.instance and gi.itemmodule='assign' and gi.scaleid=2
join user as u on u.id=asub.userid
join course as c on c.id=cm.course
left join assign_grades ag on ag.assignment=asub.assignment and ag.userid=asub.userid
where asub.status='submitted'
and c.id=?
order by asubid";  
       $records = $DB->get_records_sql($sql,array($courseid));
       return $records;
     
 }
 
 public function get_user_submission($user,$submissions){
     
     foreach($submissions as $submit){
         
     }
     
 }
 
 public function num_to_letter($number){
            $letter="R";
            switch ($number){
            case 0;
                $letter='N';
                break;
            case 1;
                $letter='R';
                break;
            case 2;
                $letter='P';
                break;
            case 3;
                $letter='M';
                break;
            case 4;
                $letter='D';    
        }
        return $letter;

     
 }
 
 public function get_user_grade($user,$assign){     
     $usergrade=new usergrade($user);   
     foreach($this->submissions as $s){
          if(($user->userid==$s->userid)&& ($s->coursemodid==$assign->coursemodid)){
            if($s->grade==null){
                /*no submission */
                $usergrade->grade="!";
             }else{
                $usergrade->grade=$s->grade;
                $usergrade->assignid=$s->assignid;
             }
         }         
     }
    
     return $usergrade;
 }
 
 public function xget_user_grade($user,$assign,$userassigns){
        $ug=new usergrade($user);
        $submissionfound=false;
        foreach($userassigns as $ua){
         if(($ua->coursemodid==$assign->cmid) && ($ua->userid==$user->userid)){
             $ug->overallgrade=$ua->overallgrade;  
             $submissionfound=true;
         }         
     }   
 return $ug;
 }
 

 
 /* get all btec graded assigns weather or not they 
  * have submissions
  */
 

public function get_all_assigns ($courseid){
    
$sql="select distinct cm.id as coursemodid,a.id as assignid,ga.activemethod, gitems.id as itemid, gitems.itemname as assignment_name from scale as s 
join grade_items gitems on gitems.gradetype=s.id 
join course_modules cm on cm.instance=gitems.iteminstance 
join context c on c.instanceid=cm.id
join grading_areas ga on ga.contextid=c.id
join modules m on m.id=cm.module
join assign a on a.id=cm.instance
join course crs on crs.id=cm.course
where s.NAME='BTEC' and m.name='assign' and crs.id=?
and activemethod='btec'";
    
global $DB;
$records= $DB->get_records_sql($sql,array($courseid));
return $records;

}

public function get_max_for_assign($maxcriterion,$cmid){
    foreach($maxcriterion as $criteria){
        if ($criteria->cmid==$cmid){
            return $this->letter_to_num($criteria->shortname);
        }
    }  
}

public function letter_to_num($letter){
            $num=0;
            switch ($letter){
            case 'P';
                $num=1;
                break;
            case 'M';
                $num='2';
                break;
            case 'D';
                $num=3;
                break;
        }
        return $num;
}

public function get_max_criteria($courseid){
    
$sql="select a.id,cm.id as cmid,a.name,shortname from  gradingform_btec_criteria as gbcout 
join  grading_definitions gdef on gdef.id=gbcout.definitionid 
join  grading_areas ga on ga.id=gdef.areaid 
join  context con on con.id=ga.contextid 
join  course_modules cm on cm.id=con.instanceid 
join  assign a on a.id=cm.instance
where shortname=(select min(shortname)from  gradingform_btec_criteria as gbcin 
where gbcin.definitionid=gbcout.definitionid)";

global $DB;
$records= $DB->get_records_sql($sql,array($courseid));
return $records;    
}



public function xget_max_criteria($courseid){
    
$sql="select gbc.id as gbcid, a.id as assignid,a.name,ga.activemethod,gdef.name,gbc.shortname as criteria
from grading_areas ga 
join context con on con.id=ga.contextid
join course_modules cm on cm.id=con.instanceid
join course c on c.id=cm.course
join assign a on a.id=cm.instance
join grading_definitions gdef on gdef.areaid=ga.id
join gradingform_btec_criteria gbc on gbc.definitionid=gdef.id
where c.id=? order by a.id,gbc.shortname";
global $DB;
$records= $DB->get_records_sql($sql,array($courseid));

$criteria=array();
foreach($records as $r){
    $criteria[]=$r->assignid;    
}
$uc=array_unique($criteria);

$maxcriteria=array();
foreach($uc as $key=>$c ){
    foreach($records as $r){
        if($c[$key]=$r->assignid){
            print $c[$key];
            print " ";
            print $r->assignid;
        }
    }
}

var_dump($maxcriteria);



exit();

return $records;             
}



public function get_assign_top_criteriaX($courseid){
$sql="select a.id,a.name,gbcout.shortname from mdl_gradingform_btec_criteria as gbcout 
join mdl_grading_definitions gdef on gdef.id=gbcout.definitionid 
join mdl_grading_areas ga on ga.id=gdef.areaid 
join mdl_context con on con.id=ga.contextid 
join mdl_course_modules cm on cm.id=con.instanceid 
join mdl_assign a on a.id=cm.instance 
join mdl_course c on c.id=cm.course
where gbcout.shortname=(select min(shortname)
from mdl_gradingform_btec_criteria as gbcin 
where gbcin.definitionid=gbcout.definitionid)
and c.id=?";    
global $DB;
$records= $DB->get_records_sql($sql,array($courseid));
return$records;

    
}
 
   public function get_grades($courseid) {
       global $DB;

       $sql="select gg.id as gradeid,u.id as userid,u.firstname,u.lastname,cm.id,gi.itemname, gg.rawgrade from grade_grades as gg 
           join user u on u.id=gg.userid 
           join grade_items gi on gi.id=gg.itemid 
           join scale s on s.id=gi.scaleid
           join course c on c.id=gi.courseid
           join course_modules cm on cm.instance=gi.iteminstance 
           where gg.rawgrade is not null 
           and gi.itemmodule='assign'
           and s.name='BTEC'
           and cm.course=?";
       
          $records = $DB->get_records_sql($sql,array($courseid));
          return $records;
       
       /* $sql = "select u.id,gbc.id, gbc.sortorder, u.username, c.shortname as course ,a.name as assignment_name,  gbc.shortname as criteria, gbf.remark, 
if( gbf.score=0,'No','Y' ) as achieved, 
gg.rawgrade as overalgrade
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
s.name='BTEC' and gg.rawgrade is not null and a.course=?";
        $records = $DB->get_records_sql($sql,array($courseid));
        * */
      
       // return $records;
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
class usergrade{
    public $grade="N";
    public $assignid=0;
    public $user;
    public function usergrade($user){
        $this->user=$user;
    }
    
}
