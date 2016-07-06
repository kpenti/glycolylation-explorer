<?php
include "inc.php";
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');


function get_variances_range($from, $to) {
	global $db, $gly_years;
	
	/*
	need array of
	year => unique residues, total aa, totals fo reach residue
	
	*/
	
	$data = $db->select()
				->from('gly_yearly_variances', array('variance' => 'AVG(variability)', 'residue'))
				->where('year >= ?', $from)
				->where('year <= ?', $to)
				->group('residue')
				->query()
				->fetchall();
				
	return $data;
}
function get_variances_max($from, $to) {
	global $db, $gly_years;
	
	/*
	need array of
	year => unique residues, total aa, totals fo reach residue
	
	*/
	
	$data = $db->select()
				->from('gly_yearly_variances', array('variance' => 'AVG(variability)', 'residue'))
				->where('year >= ?', $from)
				->where('year <= ?', $to)
				->group('residue')
				->order('AVG(variability) DESC')
				->query()
				->fetch();
				
	return $data['variance'];
}
function get_colour($variance) {
	if($variance == 0) {
		$colour = 'white';
	}		
	if($variance > 0) {
		$colour = 'lightyellow';
	}	
	if($variance > 10) {
		$colour = 'papayawhip ';
	}
	if($variance > 20) {
		$colour = 'lightsalmon ';
	}
	if($variance > 30) {
		$colour = 'coral';
	}
	if($variance > 50) {
		$colour = 'darkorange';
	}
	if($variance > 60) {
		$colour = 'orangered';
	}	
	if($variance > 70) {
		$colour = 'firebrick';
	}	
	if($variance > 90) {
		$colour = 'red';
	}
	return $colour;
}

$from = $_GET['from'];
$to = $_GET['to'];
$max = get_variances_max($from, $to);
#var_dump($max);
$variances = get_variances_range($from, $to);
foreach($variances as $k => $v) {
	$variances[$k]['variance'] = ($v['variance']/$max)*100;
	$variances[$k]['colour'] = get_colour($v['variance']);
}
echo json_encode(array('variances' => $variances));

?>