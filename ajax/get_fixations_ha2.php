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
				->from(array('a' => 'aasequence'), array('aa' => 'RIGHT(LEFT(ha2_sequence, '.$residue.'),1)', 'total' => 'COUNT(*)'))
				->join(array('s' => 'strain'), 's.strain_id = a.strain_id', array('s.year'))
				->where('a.ha2_sequence NOT LIKE ?', '%-%')
				->where('a.ha2_sequence NOT LIKE ?', '%X%')
				->where('LENGTH(a.ha2_sequence) < 320')
				->group('s.year')
				->group('RIGHT(LEFT(ha2_sequence, '.$residue.'),1)')
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
	
	for($y = 1968; $y < 2012; $y++) {
		if(!isset($fixation[$y+1]) && $y != 2011) {
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
				->from(array('a' => 'aasequence'), array('total' => 'COUNT(*)'))
				->join(array('s' => 'strain'), 's.strain_id = a.strain_id', array('s.year'))
				->where('a.ha2_sequence NOT LIKE ?', '%-%')
				->where('a.ha2_sequence NOT LIKE ?', '%X%')
				->where('LENGTH(a.ha2_sequence) < 320')
				->group('s.year')
				->query()
				->fetchall();

	$gly_years = array();
	foreach($data as $k => $v) {
		$gly_years[$v['year']] = $v['total'];
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
		$data_points[$residue][] = (@$fixation[$residue]/array_sum($fixation))*100;
	}
}
$fixations['data_points'] = $data_points;

echo json_encode($fixations);


?>