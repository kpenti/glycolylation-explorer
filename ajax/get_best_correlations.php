<?php
include "inc.php";
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');


function get_best_correlations($gly_residue) {
	global $db, $cutoff;
	
	#$db->getProfiler()->setEnabled(true);
	
	$data = $db->select()->from(array('n' => 'gly_relevances'), array('*', 'ROUND( relevance, 2 ) as relevance'))
				->join(array('m' => 'dist_matrix'),'m.residue1 = n.gly_site', array('dist'))
				->where('gly_site = ?', $gly_residue)
				->where('relevance > ?', 10)
				->where('m.residue2 = n.residue')
				->where('m.chain1 = ?', 'A')
				->where('m.chain2 = ?', 'A')
				->order('relevance DESC')
				->query()
				->fetchall();
				
	#Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	#Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	#$db->getProfiler()->setEnabled(false);
	return $data;
}

$gly_residue = $_GET['residue'];
$neighbours = get_best_correlations($gly_residue);
echo json_encode(array('variability' => $neighbours));

?>