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
 * BTEC Progress report.
 *
 * @package   report_btecprogress
 * @author    Marcus Gree 
 * @credits   Techniques used are strongly influenced by the My Feedback report by Jessica Gramp
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

$plugins = array(
    'buttons_foundation'        => array('files'=>array('Buttons-1.1.0/js/buttons.foundation.js')),
    'dataTables'     => array('files' => array('dataTables.js')),
    'buttons_css'    => array('files'=>array('Buttons-1.1.0/css/buttons.dataTables.css')),
    'buttons_js'     => array('files'=>array('Buttons-1.1.0/js/dataTables.buttons.js')),
    'html5_js'       => array('files'=>array('Buttons-1.1.0/js/buttons.html5.js')),
    'jquery_ui'      => array('files'=>array('Buttons-1.1.0/js/buttons.jqueryui.js')
   ));
