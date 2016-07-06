<?php
include "inc.php";
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');


function get_fixations($residue) {
	global $db, $gly_years;
	
	/*
	need array of
	year => unique residues, total aa, totals fo reach residue
	
	*/
	
	$data = $db->select()
				->from(array('a' => 'aasequence'), array('aa' => 'RIGHT(LEFT(ha1_sequence, '.$residue.'),1)', 'total' => 'COUNT(*)'))
				->join(array('s' => 'strain'), 's.strain_id = a.strain_id', array('s.year'))
				->where('a.ha1_sequence NOT LIKE ?', '%-%')
				->where('a.ha1_sequence NOT LIKE ?', '%X%')
				->where('LENGTH(a.ha1_sequence) > 320')
				->group('s.year')
				->group('RIGHT(LEFT(ha1_sequence, '.$residue.'),1)')
				->order('s.year ASC')
				->query()
				->fetchall();
	
	$fixations = array();
	$results = array();
	$fixation_residues = array();
	foreach($data as $v) {
		$fixation_residues[] = $v['aa'];
	}
	
	$fixation_residues = array_unique($fixation_residues);
	
	foreach($data as $v) {
		$fixation[$v['year']][$v['aa']] = $v['total'];
	}
	return array('fixations' => $fixation, 'fixation_residues' => $fixation_residues);
}

function get_totals() {
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

function get_most_popular_aa($proportions, $start_year) {
	$aas = [];
	foreach($proportions as $aa => $values) {
		$aas[$aa] = $values[$start_year];
	}
	arsort($aas);
	reset($aas);
	$first_key = key($aas);
	return $first_key;
}
	
function is_switch($proportions, $start_year, $end_year) {	
	$start_aa = get_most_popular_aa($proportions, $start_year);
	$end_aa = get_most_popular_aa($proportions, $end_year);
	return ($start_aa != $end_aa);
}

function get_proportions($proportions_list, $start_year, $end_year) {
	$data = [];
	foreach($proportions_list as $residue => $proportions ){
		$switch = is_switch($proportions, $start_year, $end_year);
		if($switch) {
			$data[] = $residue;
		}
	}
	print_r($data);
	die();
}

function get_proportions_list() {
	global $db;
	
	$json = json_decode( file_get_contents('annual_proportions.json'), true );
	return $json;

	
	$annual_proportions = get_totals();
	$annual_proportions = [];
	#print_r($annual_proportions);die();
	//find all residues that mutated that year
	for($i = 1; $i < 328; $i++) {
		$fixations = get_fixations($i);
		$data_points = array();
		foreach($fixations['fixation_residues'] as $residue) {
			$data_points[$residue] = array();
			foreach($fixations['fixations'] as $y => $fixation) {
				$data_points[$residue][$y] = (@$fixation[$residue]/array_sum($fixation))*100;
			}
		}
		$fixations['data_points'] = $data_points;
		$annual_proportions[$i] = $data_points;

	}
	
	$annual_proportions_str = json_encode($annual_proportions);
	$annual_proportions = file_put_contents('annual_proportions.json', $annual_proportions_str);
	
	print_r($annual_proportions);
	die();
}

$gly_years = get_totals();
$start_year = $_GET['start'];
$end_year = $_GET['end'];
$proportions_list = get_proportions_list();

$proportions = get_proportions($proportions_list, $start_year, $end_year);
print_r($proportions);die();
$data_points = array();
foreach($fixations['fixation_residues'] as $residue) {
	$data_points[$residue] = array();
	foreach($fixations['fixations'] as $y => $fixation) {
		$data_points[$residue][] = (@$fixation[$residue]/array_sum($fixation))*100;
	}
}
$fixations['data_points'] = $data_points;

echo json_encode($fixations);


?>