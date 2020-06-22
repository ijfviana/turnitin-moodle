<?php

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');

require_once($CFG->dirroot.'/plagiarism/turnitin/lib.php');
require_once($CFG->dirroot.'/plagiarism/turnitin/vendor/autoload.php');
require_once($CFG->libdir.'/clilib.php');      

use Integrations\PhpSdk\TurnitinAPI;
use Integrations\PhpSdk\TiiClass;
use Integrations\PhpSdk\TiiAssignment;

$usage = "\n\nCambia la fecha de expiración de las clases en turnitin asociadas a cursos moodle. Para cada curso en moodle, la fecha de finalización de su clase asociada se calculará de la siguiente manera:
 1) será la fecha de finalización más tardía de las actividades creadas 2) si hay actividades sin fecha de finalización, se les asigna una fecha de finalización de 4 meses a contar desde
 la fecha de ejecuciión del script 3) en ningún caso la fecha de finalización podrá ser posterior a la indicada en el parámetro max_duedate..
 
Usage:
    # php change_duedates.php [--max_duedate=<value>] [--test|-t] [--postfix=<value>]
    # php change_duedates.php [--help|-h] 
 
Options:
    -h --help               Print this help.
    --max_duedate=<value>   Fecha máxima de finalización, debe estar en formato d-m-Y H:i:s
    -t 			   Indica la fecha que pondría pero no la asigna.
    --postfix=<value>	   Cadena que debe contener los nombres de las clases a las que cambieremos la fecha. Valor por defecto (Moodle PP).

Examples:

\$sudo -u www-data /usr/bin/php plagiarism/turnitin/cli/change_duedates.php 
\$sudo -u www-data /usr/bin/php plagiarism/turnitin/cli/change_duedates.php --max_duedate=12-05-2010 23:26:01 --postfix='(Moodle PP)'
\$sudo -u www-data /usr/bin/php plagiarism/turnitin/cli/change_duedates.php --max_duedate=12-05-2010 23:26:01 -t
";


list($options, $unrecognised) = cli_get_params([
    'help' => false,
    'test' => false,
    'postfix' => '(Moodle PP)',
    'max_duedate' => null,
], [
    'h' => 'help',
    't' => 'test'
]);
 
if ($unrecognised) {
    $unrecognised = implode(PHP_EOL.'  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}
 
if ($options['help']) {
    cli_writeln($usage);
    exit(2);
}

if (!empty($options['max_duedate']) && ($max_duedate = strtotime($options['max_duedate'])) === false) {
	cli_error('Formato de fecha incorrecto para el parámetro max_duedate .', 2);
}

if (!empty($max_duedate) && $max_duedate < time() + 60*5)
{
	cli_error('Debe indicar fechas futuras en max_duedate.', 2);
}

if (!empty($options['postfix'])) {
	$title = $options['postfix'];
}

if ($options['test']) {
	print("Class Id; Name Id; Current Due Date; New Due Date \n");
}


$config = plagiarism_plugin_turnitin::plagiarism_turnitin_admin_config();

$tiiapiurl = (substr($config->plagiarism_turnitin_apiurl, -1) == '/') ? substr($config->plagiarism_turnitin_apiurl, 0, -1) : $config->plagiarism_turnitin_apiurl;
$tiiintegrationid = 12;
$tiiaccountid = $config->plagiarism_turnitin_accountid;
$tiisecretkey = $config->plagiarism_turnitin_secretkey;

$api = new TurnitinAPI($tiiaccountid, $tiiapiurl, $tiisecretkey,
                                $tiiintegrationid);


$class = new TiiClass();
$class->setTitle( $title );
$response = $api->findClasses( $class );
$findclass = $response->getClass();
$classids = $findclass->getClassIds();

$class2 = new TiiClass();
$class2->setClassIds( $classids );

$response = $api->readClasses( $class2 );
$readclasses = $response->getClasses();


foreach ( $readclasses as $readclass ) {

	// para las aulas aún activas
	
	if (date("U",strtotime($readclass->getEndDate())) >= time())
	{
		// la fecha de expiración será la de la tarea que acabe mas tarde
		
		$assignment = new TiiAssignment();
	  	$assignment->setClassId( $readclass->getClassId() );
		 
	   	$response = $api->findAssignments( $assignment );
	   	$readassignments = $response->getAssignment();
	   	
	   	$findassignmentids = $readassignments->getAssignmentIds();
	   	
	   	$duedates= array();
	   	
	   	foreach ( $findassignmentids as $a ) {

			$assignment = new TiiAssignment();
     		$assignment->setAssignmentId( $a );	   	     	
	   	 	$response = $api->readAssignment( $assignment );
	     	$readassignment = $response->getAssignment();
	
			if (!empty($readassignment->getDueDate()))
				$duedates[] = date("U",strtotime($readassignment->getDueDate()));
			else
				$duedates[] = time() + 2592000;						
		}
		
		// Si ninguna de las tareas tiene fecha de finalizacion, la fecha sera 4 meses desde que se creo la clase
		
		if (empty($duedates))
		{
		 	$duedates[] = time() + 90000;	
		}  
		
		// la fecha de finalización debe ser siempre una fecha futura

		$new_duedate = max(array(max($duedates),time() + 90000));
		
		if (!empty($max_duedate) && $new_duedate > $max_duedate)
			$new_duedate = $max_duedate;
				
		$new_duedate = date ('c',$new_duedate);		
		
		if ($options['test']) {
			print($readclass->getClassId() . ";" . $readclass->getTitle() . ";". $readclass->getEndDate() . ";" . $new_duedate ."\n");
		}
		else {
			$readclass->setEndDate( $new_duedate );
     			$response = $api->updateClass( $readclass );
		}
	}	
}

?>
