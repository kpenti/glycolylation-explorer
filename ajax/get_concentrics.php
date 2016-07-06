<?
/*

	get start and end year

	get fixations -> highlight
	get polymorphisms -> highlight
	get intermediates -> highlight
	get invariants -> highlight

*/

include "inc.php";
error_reporting(E_ALL);
ini_set('display_errors', 0);
function get_mutabilities($from, $to) {
	global $db;
	$data = $db->select()
				->from('gly_mutability')
				->where('start_year >= ?', $from)
				->where('end_year <= ?', $to)
				->order('residue ASC')
				->query()
				->fetchall();
	return $data;
}

function get_residue_by_type($type, $mutabilities, $exclude = array()) {
	$residues = array();
	foreach($mutabilities as $mutability) {
		if(in_array($mutability['residue'], $exclude))
			continue;
		if($mutability['mutability'] == $type) {
			$residues[] = $mutability['residue'];
		}
	}
	return array_values(array_unique($residues));
}


function get_coords() {
	$row = 1;
	$points = array();
	if (($handle = fopen("pdb_coords.csv", "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$num = count($data);
			$row++;
			$tmp_points = array();
			$tmp_points['name'] = array('name' => $data[0]);
			$tmp_points['x'] = (float) $data[1];
			$tmp_points['y'] = (float) $data[2];
			$points[$data[0]] = $tmp_points;
		}
		fclose($handle);
	}
	return $points;
}

function split_points($points, $results) {
	$data = array();
	
	foreach($points as $point => $coords) {		
		if(in_array($point, $results['fixations'])) {
			$data['fixations'][$point] = $coords;
		}		
		if(in_array($point, $results['polymorphisms'])) {
			$data['polymorphisms'][$point] = $coords;
		}		
		if(in_array($point, $results['intermediates'])) {
			$data['intermediates'][$point] = $coords;
		}		
		if(in_array($point, $results['invariants'])) {
			$data['invariants'][$point] = $coords;
		}
	}
	$data['fixations']=array_merge(array(),$data['fixations']);
	$data['polymorphisms']=array_merge(array(),$data['polymorphisms']);
	$data['intermediates']=array_merge(array(),$data['intermediates']);
	$data['invariants']=array_merge(array(),$data['invariants']);
	
	
	/*$data['fixations']['name']['color'] = 'rgba(223, 83, 83, .5)';
	$data['polymorphisms']['name']['color'] = 'rgba(119, 152, 191, .5)';
	$data['intermediates']['name']['color'] = 'rgba(226, 237, 106, .5)';
	$data['invariants']['name']['color'] = 'rgba(211, 211, 211, .5)';
	*/
	return $data;
}


function gly_points($points, $gly_sites) {
	$gly_sites = array_keys($gly_sites);
	$res = array();
	foreach($gly_sites as $k => $v) {
		$tmp_points = array();
		$tmp_points['name'] = array('name' => $v);
		$tmp_points['x'] = (float) $points[$v]['x'];
		$tmp_points['y'] = (float) $points[$v]['y'];
		$res[$v] = $tmp_points;
	}
	$res = array_merge(array(),$res);
	return $res;
}

function get_glycosylation_total_years() {
	global $db;
	$data = $db->select()
				->from(array('a' => 'aasequence'), array('total' => 'COUNT(*)'))
				->join(array('s' => 'strain'), 's.strain_id = a.strain_id', array('s.year'))
				->where('a.ha1_sequence NOT LIKE ?', '%-%')
				->where('a.ha1_sequence NOT LIKE ?', '%X%')
				->where('LENGTH(a.ha1_sequence) > 320')
				->group('s.year')
				->query()
				->fetchall();

	$gly_years = array();
	foreach($data as $k => $v) {
		$gly_years[$v['year']] = $v['total'];
	}
	
	return $gly_years;
}
function get_glycosylation_population_years($residue) {
	global $db, $gly_years;
	
	#print_r($gly_years);
	$data = $db->select()->from(array('g' => 'glycosylation_sites'), array('g.position_aa', 'total' => 'COUNT(*)'))
				->join(array('a' => 'aasequence'), 'a.sequence_id = g.sequence_id', '')
				->join(array('s' => 'strain'), 's.strain_id = a.strain_id', 's.year')
				->where('a.ha1_sequence NOT LIKE ?', '%-%')
				->where('a.ha1_sequence NOT LIKE ?', '%X%')
				->where('g.position_aa = ?', $residue)
				->where('LENGTH(a.ha1_sequence) > 320')
				->group(array('g.position_aa', 's.year'))
				->order(array('s.year ASC', 'g.position_aa DESC'))
				->query()
				->fetchall();
	$gly_sites = array();
	foreach($data as $k => $v) {
		$gly_sites[$v['year']] = $v['total'];
	}
	
	$results = array();
	for($i = 1968; $i < 2012; $i ++) {
		$results[$i] = (float) @number_format(($gly_sites[$i]/$gly_years[$i])*100, 2);
	}
	return $results;
}
function get_glycosylation_population($residue,$population_years,$from, $to) {
	global $db, $gly_years;
	$results = array();
	for($i = $from; $i < $to; $i ++) {
		$results[$i] = (float) $population_years[$i];
	}
	return array_sum($results)/count($results);
}

$data = array();
$from = $_GET['from'];
$to = $_GET['to'];

$glycosylation_sites = array(8, 22, 38, 63, 81, 126, 122, 133, 144, 165, 246, 276, 285);

$gly_years = get_glycosylation_total_years();
if(file_exists('cache/population_years.json')){
	$population_years = file_get_contents('cache/population_years.json');
	$population_years = json_decode($population_years, true);
} else {
	$population_years = array();
	foreach($glycosylation_sites as $glycosylation_site) {
		$population_years[$glycosylation_site] = get_glycosylation_population_years($glycosylation_site);
	}
	file_put_contents('cache/population_years.json', json_encode($population_years));
}

$gly_data = array();
foreach($glycosylation_sites as $glycosylation_site) {
	$population = get_glycosylation_population($glycosylation_site, $population_years[$glycosylation_site], $from, $to);
	if($population > 75)
		$gly_data[$glycosylation_site] = $population;
}

$mutabilities = get_mutabilities($from, $to);
$fixations = get_residue_by_type('fixation', $mutabilities);
$polymorphisms = get_residue_by_type('polymorphic', $mutabilities, $fixations);
$intermediates = get_residue_by_type('intermediate', $mutabilities, array_merge($fixations,$polymorphisms));
$invariants = get_residue_by_type('invariant', $mutabilities, array_merge($fixations,$polymorphisms,$intermediates));

$results = array();
$points = get_coords();
$results['fixations'] = $fixations;
$results['polymorphisms'] = $polymorphisms;
$results['intermediates'] = $intermediates;
$results['invariants'] = $invariants;

$r_points = split_points($points, $results);
$r_points['gly'] = gly_points($points, $gly_data);
$results['points'] = $r_points;

header('Content-type: application/json');
echo json_encode($results);
?>