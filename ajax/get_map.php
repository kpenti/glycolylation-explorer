<?php
include "inc.php";
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');


function get_glycosylation_sites($strain) {
	global $db;
	$sql = "SELECT s.fullname, a.gly_cluster, a.gly_sites FROM strain as s JOIN aasequence as a ON s.strain_id = a.strain_id WHERE s.fullname LIKE '%$strain%' AND ha1_sequence IS NOT NULL AND a.gly_cluster IS NOT NULL";
	$data = $db->query($sql)->fetchall();
	return $data;
}

function get_smith_strains() {
	global $db;
	$sql = "SELECT a.gly_sites, a.gly_cluster, s.* FROM smith_strains AS s
JOIN aasequence AS a ON a.strain_id = s.strain_id
WHERE ha1_sequence IS NOT NULL AND a.gly_cluster IS NOT NULL AND s.cluster IS NOT NULL";
	$data = $db->query($sql)->fetchall();
	return $data;
}

//read text file of co-ordinates
$strains = get_smith_strains();
$clusters = array();
$clusters_antigenic = array();
foreach($strains as $k => $v) {
	$coords = array();
	$coords_y = $v['y'];
	$coords_x = $v['x'];
	#$clusters['test']['info'][] = $v;
	$cluster = $v['gly_cluster'];
	if($cluster == '')
		continue;
	$clusters[$cluster][] = array('x'=>(float)$v['x'], 'y'=>(float)$v['y'], 'name' => $v);
	$clusters_antigenic[$v['cluster']][] = array('x'=>(float)$v['x'], 'y'=>(float)$v['y'], 'name' => $v);
}
$response = array();
$response['clusters_gly'] = $clusters;
$response['clusters_antigenic'] = $clusters_antigenic;
echo json_encode($response);
	
?>