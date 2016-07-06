<?php
include "inc.php";
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
#header('Content-type: application/json');
echo '<pre>';

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
				//->where('a.ha1_sequence NOT LIKE ?', '%X%')
				->where('LENGTH(a.ha1_sequence) > 320')
				->where('subtype = ?', 'H3N2')
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
		$fixation[$v['year']][$v['aa']] = number_format(($v['total']/$gly_years[$v['year']]) * 100,2);
		arsort($fixation[$v['year']], SORT_NUMERIC);
	}
	
	//remove x's
	foreach($fixation as $year => $aminos) {
		foreach($aminos as $aa => $total) {
			if($aa == 'X') {
				$fixation[$year][key($aminos)] = $fixation[$year][key($aminos)] + $total;
				unset($fixation[$year][$aa]);
			}
		}
		//print_r($aa);
		/*if($aa == 'X') {
			print_r($v);
			die();
		}*/
	}
	
	return array('fixations' => $fixation);
}

function get_totals() {
	global $db;
	$data = $db->select()
				->from(array('a' => 'aasequence'), array('total' => 'COUNT(*)'))
				->join(array('s' => 'strain'), 's.strain_id = a.strain_id', array('s.year'))
				->where('a.ha1_sequence NOT LIKE ?', '%-%')
				//->where('a.ha1_sequence NOT LIKE ?', '%X%')
				->where('LENGTH(a.ha1_sequence) > 320')
				->where('subtype = ?', 'H3N2')
				->group('s.year')
				->query()
				->fetchall();

	$gly_years = array();
	foreach($data as $k => $v) {
		$gly_years[$v['year']] = $v['total'];
	}
	return $gly_years;
}

function get_switches($fixations) {
	print_r($fixations['fixations']);
	//find dominant amino acids
	$dominant_amino = array();
	foreach($fixations['fixations'] as $year => $aminos) {
		$dominant_amino[$year] = key($aminos);
	}
	
	$switches = array();
	foreach($dominant_amino as $year => $amino) {
		//get the year before if there is one
		if($year != 1968) {
			if( $dominant_amino[$year] != $dominant_amino[$year-1] ) {
				$tmp_array = array();
				$tmp_array['start_year'] =$year-1;
				$tmp_array['end_year'] = $year;
				$tmp_array['start_amino'] = $dominant_amino[$year-1];
				$tmp_array['end_amino'] = $dominant_amino[$year];
				$switches[] = $tmp_array;
			}
		}
	}
	print_r($switches);
	die();
}

$gly_years = get_totals();
$residue = 13;
echo $residue;
$fixations = get_fixations($residue);
$switches = get_switches($fixations);

/*

array of 
year from to

fixation - amino acid which switches and mainains over 80% dominacne
polymorphism - amino acid which was not a switcher but dominant amino was 80% or less


*/

print_r($fixations);
die();

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