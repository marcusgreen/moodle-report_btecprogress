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

    private $submissions;
    private $grades;
    private $maxcriteria;
    private $assigncriteria;
    private $criteriagrades;

    /**
     * Initialises the report and sets the title
     */
    public function init($courseid) {
        $this->title = get_string('title', 'report_btecprogress');
        $this->submissions = $this->get_submissions($courseid);
        $this->grades = $this->get_grades($courseid);
        $this->maxcriteria=$this->get_max_criteria($courseid);
        $this->assigncriteria=$this->get_all_criteria($courseid);
        $this->criteriagrades=$this->get_criteria_grades($courseid);
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
    

    public function get_submission_status($courseid) {
        /* get list of submissions */
        $sql = "select asb.id asbid,a.id as assignid,u.id userid,c.id courseid,asb.status asbstatus FROM {assign_submission} AS asb
JOIN {assign} AS a ON a.id = asb.assignment
JOIN {user} AS u ON u.id = asb.userid
JOIN {course} AS c ON c.id = a.course
where c.id=? and asb.status='submitted'";
        global $DB;
        $records = $DB->get_records_sql($sql, array($courseid));
        return $records;
    }

    public function get_user_sub_status($user, $assign, $submissionstatus) {
        $status = "N";
        foreach ($submissionstatus as $s) {
            if (($user->userid == $s->userid ) && ($assign->assignid == $s->assignid)) {
                $status = "!";
                return $status;
            }
        }
        return $status;
    }

    public function get_submissions($courseid) {
        global $DB;
        $sql = "select asub.id as asubid,cm.id as coursemodid,a.id as assignid,c.shortname,u.id as userid,u.username,a.name,ag.grade from {assign_submission} as asub
join {assign} as a on a.id =asub.assignment
join {course_modules} as cm on cm.instance=a.id and cm.module=1
join {grade_items} as gi on gi.iteminstance=cm.instance and gi.itemmodule='assign' and gi.scaleid=2
join {user} as u on u.id=asub.userid
join {course} as c on c.id=cm.course
left join {assign_grades} ag on ag.assignment=asub.assignment and ag.userid=asub.userid
where asub.status='submitted'
and c.id=?
order by asubid";
        $records = $DB->get_records_sql($sql, array($courseid));
        return $records;
    }

    public function get_user_submission($user, $submissions) {

        foreach ($submissions as $submit) {
            
        }
    }

    
    public function grade_style($overallgrade){
        $style = "";
        switch ($overallgrade) {
            case 'R';
            $style = 'refer';
                break;
            case 'P';
                $style= 'pass';
                break;
            case 'M';
                $style = 'merit';
                break;
            case 'D';
                $style = 'distinction';
        }
        return $style;
        
    }
    
    public function num_to_letter($number) {
        $letter = "R";
        switch ($number) {
            case 0;
                $letter = 'N';
                break;
            case 1;
                $letter = 'R';
                break;
            case 2;
                $letter = 'P';
                break;
            case 3;
                $letter = 'M';
                break;
            case 4;
                $letter = 'D';
        }
        return $letter;
    }
    
    public function get_max_grade($assignid){
        foreach($this->maxcriteria as $criteria){
                if($criteria->id==$assignid){
                    return $this->letter_to_num(substr($criteria->shortname,0,1));
                }
            }
                   
    }

    public function get_user_grade($user, $assign) {
        $usergrade = new usergrade($user);
        foreach ($this->submissions as $s) {
            if (($user->userid == $s->userid) && ($s->coursemodid == $assign->coursemodid)) {
                if ($s->grade == null) {
                    /* no submission */
                    $usergrade->grade = "!";
                } else {
                    $usergrade->grade = $s->grade;
                    $usergrade->assignid = $s->assignid;
  
                    $usergrade->addgrade($s->grade,$this->get_max_grade($s->assignid));
                }
            }
        }
        return $usergrade;
    }

    public function get_all_usergrades($user, $assigns) {
        $usergrade = new usergrade($user);
        foreach ($assigns as $a) {
            foreach ($this->submissions as $s) {
                if (($user->userid == $s->userid) && ($s->coursemodid == $a->coursemodid) && ($a->assignid = $s->assignid)) {
                    if ($s->grade == null) {
                        /* no submission */
                        $usergrade->grade = "!";
                    } else {
                        $usergrade->grade = $s->grade;
                        $usergrade->assignid = $s->assignid;
                        $usergrade->addgrade($s->grade,$this->get_max_grade($s->assignid));

                    }
                }
            }
        }
        return $usergrade;
  }

    /* get all btec graded assigns weather or not they 
     * have submissions
     */

    public function get_all_assigns($courseid) {

        $sql = "select distinct cm.id as coursemodid,a.id as assignid,ga.activemethod, gitems.id as itemid, gitems.itemname as assignment_name from {scale} as s 
join {grade_items} gitems on gitems.gradetype=s.id 
join {course_modules} cm on cm.instance=gitems.iteminstance 
join {context} c on c.instanceid=cm.id
join {grading_areas} ga on ga.contextid=c.id
join {modules} m on m.id=cm.module
join {assign} a on a.id=cm.instance
join {course} crs on crs.id=cm.course
where s.NAME='BTEC' and m.name='assign' and crs.id=?
and activemethod='btec'";

        global $DB;
        $records = $DB->get_records_sql($sql, array($courseid));
        return $records;
    }
    
  public function get_criteria_grades($courseid){      
      $sql="select gbf.id,gbc.id as criteriaid,a.id as assignid, cm.id as coursemodid,u.id as userid,a.name,gbc.shortname,gbf.score as score from {course} as crs
JOIN  {course_modules}  AS cm ON crs.id = cm.course
JOIN  {assign}  AS a ON a.id = cm.instance
JOIN  {context}  AS ctx ON cm.id = ctx.instanceid
JOIN  {grading_areas}  AS ga ON ctx.id=ga.contextid
JOIN  {grading_definitions}  AS gd ON ga.id = gd.areaid
JOIN  {gradingform_btec_criteria}  AS gbc ON (gbc.definitionid = gd.id)
JOIN  {grading_instances}  AS gin ON gin.definitionid = gd.id
JOIN  {assign_grades}  AS ag ON ag.id = gin.itemid
JOIN  {user}  AS u ON u.id = ag.userid
JOIN  {gradingform_btec_fillings}  AS gbf ON (gbf.instanceid = gin.id)
AND (gbf.criterionid = gbc.id)
WHERE  gin.status = 1
and cm.module=1 and cm.course=?
and gd.method='btec'";
        global $DB;
        $records = $DB->get_records_sql($sql, array($courseid));
        return $records;  
      
  }
public function get_user_criteria_grades($userid,$coursemodid,$criteriaid){
    foreach ($this->criteriagrades as $c){
        if(($c->userid==$userid)&& ($c->coursemodid==$coursemodid) && ($c->criteriaid==$criteriaid)){
           return $this->critera_num_to_letter($c->score);
            //return $c->score;
        }        
    }
  
}

public function critera_num_to_letter($criterianum){
        switch ($criterianum) {
            case '0';
                return 'N';
            case '1';
                return 'A';
        }
        return '?';
    
}

  /* Gets the criteria for an individual assignment */
  public function get_assign_criteria($coursemodid){
      $criteria =  array();
        foreach($this->assigncriteria as $ac){
          if($ac->coursemodid==$coursemodid){
            $criteria[]=$ac;
           }
       }
       return $criteria;
  }
  public function get_all_criteria($courseid){
      $sql="select gbc.id as criteriaid,a.id as assignid,cm.id as coursemodid,a.name,gbc.shortname from {assign} as a 
              join {course_modules} as cm on cm.instance=a.id 
              join {context} as ctx on ctx.instanceid=cm.id 
              join {grading_areas} as ga on ga.contextid=ctx.id 
              JOIN {grading_definitions} AS gd ON ga.id = gd.areaid 
              JOIN {gradingform_btec_criteria} AS gbc ON (gbc.definitionid = gd.id) 
              and cm.module=1 and cm.course=? order by a.name";
        global $DB;
        $records = $DB->get_records_sql($sql, array($courseid));
        return $records;
                
}
    
public function letter_to_num($letter) {
        $num = 0;
        switch ($letter) {
            case 'N';
                $num=1;
            case 'P';
                $num = 2;
                break;
            case 'M';
                $num = '3';
                break;
            case 'D';
                $num = 4;
                break;
        }
        return $num;
    }

    
    public function get_max_criteria($courseid) {

        $sql = "select a.id,cm.id as cmid,a.name,shortname from  {gradingform_btec_criteria} as gbcout 
join  {grading_definitions} gdef on gdef.id=gbcout.definitionid 
join  {grading_areas} ga on ga.id=gdef.areaid 
join  {context} con on con.id=ga.contextid 
join  {course_modules} cm on cm.id=con.instanceid 
join  {assign} a on a.id=cm.instance
where shortname=(select min(shortname)from  {gradingform_btec_criteria} as gbcin 
where gbcin.definitionid=gbcout.definitionid)";

        global $DB;
        $records = $DB->get_records_sql($sql, array($courseid));
        return $records;
    }

  
    public function get_grades($courseid) {
        global $DB;

        $sql = "select gg.id,u.id as userid,u.firstname,u.lastname,gi.itemname, gg.rawgrade from   {grade_grades}  as gg 
           join  {user}  u on u.id=gg.userid 
           join  {grade_items} gi on gi.id=gg.itemid 
           join  {scale}  s on s.id=gi.scaleid
           join  {course}  c on c.id=gi.courseid
           join  {course_modules}  cm on cm.instance=gi.iteminstance 
           join  {modules}  on {modules}.id=cm.module
	   where gg.rawgrade is not null 
	   and {modules}.name='assign'
	   and gi.itemtype is not null
           and gi.itemmodule='assign'
           and s.name='BTEC'
           and cm.course=?";
		   

        $records = $DB->get_records_sql($sql, array($courseid));
        return $records;

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

class usergrade {

    public $grade = "N";
    public $assignid = 0;
    public $user;
    public $modulegrade;
    private $grades = array();
 
    public function addgrade($grade,$maxgrade) {
        $this->grades[]['grade'] = $grade;
        $this->grades[]['maxgrade'] = $maxgrade;
        if ($grade < $maxgrade){
            $this->modulegrade=$grade;
        }

        
    }

    public function usergrade($user) {
        $this->user = $user;
        $this->modulegrade=4;
    }

}
