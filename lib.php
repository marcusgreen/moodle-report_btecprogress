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
 *			  <richard.havinga@ulcc.ac.uk>. The code for using an external database is taken from Juan leyva's
 *			  <http://www.twitter.com/jleyvadelgado> configurable reports block.
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
    $url = new moodle_url('/report/btecprogress/index.php' , array('userid' => $user->id));
    $navigation->add(get_string('pluginname', 'report_btecprogress'), $url, navigation_node::TYPE_SETTING, null, null,
            new pix_icon('i/report', ''));
}

function report_btecprogress_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/myreport:view', $context)) {
        $url = new moodle_url('/report/btecprogress/index.php', array('id' => $course->id));
        $navigation->add(get_string('pluginname', 'report_btecprogress'), $url, navigation_node::TYPE_SETTING, null, null,
                new pix_icon('i/report', ''));
    }
}

class report_btecprogress {

	/**
	 * Initialises the report and sets the title
	 */
	public function init() {
		$this->title = get_string('my_feedback', 'report_btecprogress');
	}
	
	/**
	 * Sets up global $DB moodle_database instance
	 *
	 * @global stdClass $CFG The global configuration instance.
	 * @see config.php
	 * @see config-dist.php
	 * @global stdClass $DB The global moodle_database instance.
	 * @return void|bool Returns true when finished setting up $DB. Returns void when $DB has already been set.
	 */
	function setup_ExternalDB() {
	    global $CFG, $DB, $remotedb;
	    
	    // Use a custom $remotedb (and not current system's $DB) if set - code sourced from configurable
	    // Reports plugin.
	    $remotedbhost = get_config('report_btecprogress', 'dbhost');
	    $remotedbname = get_config('report_btecprogress', 'dbname');
	    $remotedbuser = get_config('report_btecprogress', 'dbuser');
	    $remotedbpass = get_config('report_btecprogress', 'dbpass');
	    if (empty($remotedbhost) OR empty($remotedbname) OR empty($remotedbuser)) {
	                $remotedb = $DB;

	                setup_DB();
	    }else{
            //
    	    if (!isset($CFG->dblibrary)) {
    	        $CFG->dblibrary = 'native';
    	        // use new drivers instead of the old adodb driver names
    	        switch ($CFG->dbtype) {
    	            case 'postgres7' :
    	                $CFG->dbtype = 'pgsql';
    	                break;
    	
    	            case 'mssql_n':
    	                $CFG->dbtype = 'mssql';
    	                break;
    	
    	            case 'oci8po':
    	                $CFG->dbtype = 'oci';
    	                break;
    	
    	            case 'mysql' :
    	                $CFG->dbtype = 'mysqli';
    	                break;
    	        }
    	    }
    	
    	    if (!isset($CFG->dboptions)) {
    	        $CFG->dboptions = array();
    	    }
    	
    	    if (isset($CFG->dbpersist)) {
    	        $CFG->dboptions['dbpersist'] = $CFG->dbpersist;
    	    }
    	
    	    if (!$remotedb = moodle_database::get_driver_instance($CFG->dbtype, $CFG->dblibrary)) {
    	        throw new dml_exception('dbdriverproblem', "Unknown driver $CFG->dblibrary/$CFG->dbtype");
    	    }
    	
    	    try {
    	        $remotedb->connect($remotedbhost, $remotedbuser, $remotedbpass, $remotedbname, $CFG->prefix, $CFG->dboptions);
    	    } catch (moodle_exception $e) {
    	        if (empty($CFG->noemailever) and !empty($CFG->emailconnectionerrorsto)) {
    	            $body = "Connection error: ".$CFG->wwwroot.
    	            "\n\nInfo:".
    	            "\n\tError code: ".$e->errorcode.
    	            "\n\tDebug info: ".$e->debuginfo.
    	            "\n\tServer: ".$_SERVER['SERVER_NAME']." (".$_SERVER['SERVER_ADDR'].")";
    	            if (file_exists($CFG->dataroot.'/emailcount')){
    	                $fp = @fopen($CFG->dataroot.'/emailcount', 'r');
    	                $content = @fread($fp, 24);
    	                @fclose($fp);
    	                if((time() - (int)$content) > 600){
    	                    //email directly rather than using messaging
    	                    @mail($CFG->emailconnectionerrorsto,
    	                            'WARNING: Database connection error: '.$CFG->wwwroot,
    	                            $body);
    	                    $fp = @fopen($CFG->dataroot.'/emailcount', 'w');
    	                    @fwrite($fp, time());
    	                }
    	            } else {
    	                //email directly rather than using messaging
    	                @mail($CFG->emailconnectionerrorsto,
    	                        'WARNING: Database connection error: '.$CFG->wwwroot,
    	                        $body);
    	                $fp = @fopen($CFG->dataroot.'/emailcount', 'w');
    	                @fwrite($fp, time());
    	            }
    	        }
    	        // rethrow the exception
    	        throw $e;
    	    }
    	
    	    $CFG->dbfamily = $remotedb->get_dbfamily(); // TODO: BC only for now
    	
    	    return true;
    	}
    	return false;
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

	/**
	 * Gets whether or not an online pdf feedback file has been generated
	 *
	 * @param str $gradeid The gradeid
	 * @return book true if there's a pdf feedback file for the submission, otherwise false
	 */
	public function has_pdf_feedback_file($gradeid) {
		global $remotedb;
		// Is there some online pdf feedback?
		if ($remotedb->record_exists('assignfeedback_editpdf_annot', array('gradeid' => $gradeid
		))) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get a user's quiz attempts for a particular quiz
	 *
	 * @param int $quizid The id of the quiz which comes from the gi.iteminstance
	 * @param int $userid The id of the user
	 * @param int $quizurlid The id of the quiz that can be used to access the quiz via the URL
	 * @return str Any comments left by a marker on a Turnitin Assignment via the Moodle Comments
	 *         feature (not in Turnitin), each on a new line
	 */
	public function get_quiz_attempts_link($quizid, $userid, $quizurlid) {
		global $CFG, $remotedb;
		$sqlcount = "SELECT count(attempt) as attempts
						FROM {quiz_attempts} qa
						WHERE quiz=? and userid=?";
		$params = array($quizid, $userid
		);
		$attemptcount = $remotedb->count_records_sql($sqlcount, $params, $limitfrom = 0,
				$limitnum = 0);
		$out = array();
		if ($attemptcount > 0) {
			$url = $CFG->wwwroot . "/mod/quiz/view.php?id=" . $quizurlid;
			$attemptstext = ($attemptcount > 1) ? get_string('attempts', 'report_btecprogress') : get_string(
					'attempt', 'report_btecprogress');
			$out[] = html_writer::link($url,
					get_string('review', 'report_btecprogress') . " " . $attemptcount . " " .
					$attemptstext);
		}
		$br = html_writer::empty_tag('br');
		return implode($br, $out);
	}

	/**
	 * Get group assignment submission date - since it won't come through for a user in a group
	 * unless they were the one's to upload the file
	 *
	 * @param int $userid The id of the user
	 * @param int $contextid The context of the file
	 * @return str submission dates, each on a new line if there are multiple
	 */
	public function get_group_assign_submission_date($userid, $contextid) {
		global $remotedb;
		// Group submissions.
		$sql = "SELECT su.timemodified
				FROM {files} f
				JOIN {assign_submission} su ON f.itemid = su.id
		   LEFT JOIN {groups_members} gm ON su.groupid = gm.groupid AND gm.userid = ?
				WHERE contextid=? AND filesize > 0 AND su.groupid <> 0";
		$params = array($userid, $contextid
		);
		$files = $remotedb->get_recordset_sql($sql, $params, $limitfrom = 0, $limitnum = 0);
		$out = array();
		foreach ($files as $file) {
			$out[] = $file->timemodified;
		}
		$br = html_writer::empty_tag('br');
		return implode($br, $out);
	}


}
