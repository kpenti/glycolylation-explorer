<?php
include "inc.php";
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
error_reporting(0);
function get_sequence($strain_id) {
	global $db;
	$sql = "SELECT s.fullname, a.strain_id, a.ha1_sequence FROM strain as s JOIN aasequence as a ON s.strain_id = a.strain_id WHERE s.strain_id = $strain_id AND ha1_sequence IS NOT NULL";
	$data = $db->query($sql)->fetch();
	return $data;
}

function find_differences($seq1, $seq2) {
	$diffs = array();
	for($i = 0; $i <= strlen($seq1); $i++) {
		if($seq1[$i] == '-' || $seq2[$i] == '-' || $seq1[$i] == ' ' || $seq2[$i] == ' '|| $seq1[$i] == '' || $seq2[$i] == '')
			continue;		
		if($seq1[$i] != $seq2[$i]) {
			$diffs[] = $seq1[$i].($i+1).$seq2[$i];
		}
	}
	return $diffs;
}

function get_distance($x1, $y1, $x2, $y2) {
	return sqrt(pow(($x2-$x1),2)+pow(($y2-$y1),2));
}
function find_distance($strain1_id, $strain2_id) {
	global $db;
	$sql = "SELECT * FROM smith_strains WHERE strain_id = $strain1_id";
	$row1 = $db->query($sql)->fetch();
	
	$sql = "SELECT fullname, a.strain_id, `x`, `y`, a.ha1_sequence,gly_cluster,gly_sites FROM smith_strains AS s JOIN aasequence AS a ON a.strain_id = s.strain_id WHERE a.strain_id = $strain2_id";
	$row2 = $db->query($sql)->fetch();

	return get_distance($row1['x'],  $row1['y'],  $row2['x'],  $row2['y']);
}
$selection = json_decode($_GET['selection'], true);
foreach($selection as $strain) {
	$sequence_data[] = get_sequence($strain['strain_id']);
	$seq[] = $strain['strain_id'];
}
$diffs = find_differences($sequence_data[0]['ha1_sequence'], $sequence_data[1]['ha1_sequence']);
$results['diffs'] = $diffs;

$distance = find_distance($sequence_data[0]['strain_id'], $sequence_data[1]['strain_id']);
$results['distance'] = $distance;

echo json_encode($results);
?>