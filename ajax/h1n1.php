<?
ini_set('memory_limit','1600000M');
ini_set('max_execution_time', 300); 
/*
for each position get neighbours
for each neighbour get fixations
*/

//db connection
ini_set('include_path',
ini_get('include_path') . PATH_SEPARATOR  . '/www/htdocs/3fx.us/ZendFramework-1.11.11-minimal/library');
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();


require_once 'Zend/Db.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Mail.php';

try {
	$db = Zend_Db::factory('Pdo_Mysql', array(
		'host' =>'178.62.64.162',
		'username' =>'kpenti',
		'password' =>'Tapa5Hung',
		'dbname' =>'ncbi_flu'
	));
	$db->getConnection();
} catch (Zend_Db_Adapter_Exception $e) {
	mail('snakebiter@gmail.com', '3FX.US MYSQL DOWN', 'service mysqld restart');
	die("Zend_Db_Adapter_Exception: ".$e->getMessage());
} catch (Zend_Exception $e) {
	die("Zend_Exception".$e->getMessage());
}
Zend_Registry::set('db', $db);

if (!function_exists('trace')) {
	function trace($object, $exit = false) {

			echo "<pre>";
			if($object) {
				print_r($object);
			} else {
				var_dump($object);
			}
			echo "</pre>";
			if($exit) {
				exit();
			}

	}
}



function get_fixations($residue) {
	global $db, $gly_years;
	
	/*
	need array of
	year => unique residues, total aa, totals fo reach residue
	
	*/
	
	$data = $db->select()
				->from(array('a' => 'sequences'), array('aa' => 'RIGHT(LEFT(ha1_sequence, '.$residue.'),1)', 'total' => 'COUNT(*)', 'year'))
				->where('a.ha1_sequence NOT LIKE ?', '%-%')
				->where('a.ha1_sequence NOT LIKE ?', '%X%')
				->where('LENGTH(a.ha1_sequence) > 320')
				->group('a.year')
				->group('RIGHT(LEFT(ha1_sequence, '.$residue.'),1)')
				->order('a.year ASC')
				->query()
				->fetchall();
	
	$fixations = array();
	$results = array();
	$fixation_residues = array();
	foreach($data as $v) {
		$fixation_residues[] = $v['aa'];
	}
	
	$fixation_residues = array_unique($fixation_residues);
	#$fixation[1917] = 0;
	foreach($data as $v) {
		if((int) $v['year'] > 1917)
			$fixation[(int) $v['year']][$v['aa']] = (int) $v['total'];
	}
	
	for($y = 1917; $y < 2015; $y++) {
		if(!isset($fixation[$y+1])) {
			$fixation[$y+1] = $fixation[$y];
		}
	}
	/*foreach($fixation as $k => $v) {
		if(!isset($fixation[$k+1])) {
			$fixation[$k+1] = $fixation[$k];
		}
	}*/
	ksort($fixation);
	#trace($fixation);die();
	
	return array('fixations' => $fixation, 'fixation_residues' => $fixation_residues);
}

function get_totals() {
	global $db;
	$data = $db->select()
				->from(array('a' => 'sequences'), array('total' => 'COUNT(*)', 'year'))
				->where('a.ha1_sequence NOT LIKE ?', '%-%')
				->where('a.ha1_sequence NOT LIKE ?', '%X%')
				->where('LENGTH(a.ha1_sequence) > 320')
				->group('a.year')
				->query()
				->fetchall();

	#print_r($data);die();

	$gly_years = array();
	foreach($data as $k => $v) {
		$gly_years[(int) $v['year']] = $v['total'];
	}
	return $gly_years;
}

$gly_years = get_totals();
$residue = $_GET['residue'];
$fixations = get_fixations($residue);

$data_points = array();
foreach($fixations['fixation_residues'] as $residue) {
	$data_points[$residue] = array();
	foreach($fixations['fixations'] as $y => $fixation) {
		@$data_points[$residue][] = @(@$fixation[$residue]/array_sum($fixation))*100;
	}
}
$fixations['data_points'] = $data_points;

echo json_encode($fixations);