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
 *
*
* @package    local
* @subpackage reportesalumnos
* @copyright  2015  <Rodolfo Li
* 				
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once(dirname(__FILE__) . '/../../config.php');

global $PAGE, $CFG, $OUTPUT, $DB;

require_login();

$cmid = optional_param('cmid',0,PARAM_INT);


$url = new moodle_url('/local/actividadSocial/index.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

$title ="Tiempo promedio de actividades";

$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
		
			echo "<h1>Tareas</h1>";
			$params = array(1,1,$cmid,$USER->id);		
			
			$sql_assing = "SELECT asub.id, a.name, asub.timecreated, asub.timemodified
						 	FROM {course_modules} as cm INNER JOIN {modules} as m ON (cm.module = m.id) 
						   		INNER JOIN {assign} as a ON (a.course = cm.course) 
    					   		INNER JOIN {assign_submission} as asub ON ( asub.assignment = a.id) 
    							INNER JOIN {user} as us ON (us.id = asub.userid) 
						 	WHERE m.name in ('assign') 
								AND cm.visible = ? 
    							AND m.visible = ?
    							AND cm.course = ?
								AND us.id = ?
							ORDER BY asub.timemodified DESC,asub.id";
			$lastassings = $DB->get_records_sql($sql_assing, $params);
			$table_assign = new html_table();
			$table_assign->head = array('Nombre','Inicio tarea','Ultima modificación','Tiempo promedio');
			foreach($lastassings as $assing){
				$timecreated = date('d-m-Y  H:i',$assing->timecreated);
				$timemodified = date('d-m-Y  H:i',$assing->timemodified);
				$duracion = date('H:i',($assing->timemodified -$assing->timecreated ));
				$table_assign->data[] = array($assing->name, $timecreated,$timemodified, $duracion);
			}
			echo html_writer::table($table_assign);

			echo "<h1>Recursos</h1>";
			$sql_resources = "SELECT log.id, r.name, log.timecreated
						 	FROM {course_modules} as cm INNER JOIN {modules} as m ON (cm.module = m.id)
						   		INNER JOIN {resource} as r ON (r.course = cm.course)
								INNER JOIN {logstore_standard_log} as log ON (log.objectid = r.id)
								INNER JOIN {user} as us ON (us.id = log.userid)
						 	WHERE m.name in ('resource')
								AND log.objecttable = 'resource'
								AND cm.visible = ?
    							AND m.visible = ?
    							AND cm.course = ?
								AND us.id = ?
    					  	ORDER BY log.timecreated DESC, log.id";
			$lastresources = $DB->get_records_sql($sql_resources, $params);
			$table_resource = new html_table();
			$table_resource->head = array('Name', 'Time view');
			foreach($lastresources as $resource){
				$timeview = date('d-m-Y  H:i',$resource->timecreated);
				$table_resource->data[] = array($resource->name, $timeview);
			}
			$lastresources = $DB->get_records_sql($sql_resources, $params);
			echo html_writer::table($table_resource);
			$buttonback = new moodle_url('../../course/view.php', array('id'=>$cmid));
			echo $OUTPUT->single_button($buttonback,"Back");


echo $OUTPUT->footer();

