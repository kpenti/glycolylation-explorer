<?php
include "inc.php";
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');


function get_neighbours($gly_residue, $radius) {
	global $db, $cutoff;
	
	#$db->getProfiler()->setEnabled(true);
	
	/*$data = $db->select()->from(array('n' => 'gly_neighbours'))
				->join(array('v' => 'gly_variability'), 'n.neighbour_residue = v.residue', array('v.variability'))
				->where('gly_residue LIKE ?', 'A'.$gly_residue)
				->where('neighbour_chain LIKE ?', 'A')
				->where('cutoff LIKE ?', 10000)
				->where('dist <= ?', $radius)
				->order('v.variability DESC')
				->query()
				->fetchall();	*/
	$data = $db->select()->from(array('n' => 'dist_matrix'))
				->join(array('v' => 'gly_variability'), 'n.residue2 = v.residue', array('v.variability'))
				->where('residue1 LIKE ?', $gly_residue)
				->where('chain1 LIKE ?', 'A')
				->where('chain2 LIKE ?', 'A')
				->where('dist <= ?', $radius)
				->order('v.variability DESC')
				->query()
				->fetchall();
				
				
	#Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	#Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	#$db->getProfiler()->setEnabled(false);
	return $data;
}
function get_neighbours_dist($gly_residue, $radius) {
	global $db, $cutoff;
	
	#$db->getProfiler()->setEnabled(true);
	/*
	$data = $db->select()->from(array('n' => 'gly_neighbours'))
				->join(array('v' => 'gly_variability'), 'n.neighbour_residue = v.residue', array('v.variability'))
				->where('gly_residue LIKE ?', 'A'.$gly_residue)
				->where('neighbour_chain LIKE ?', 'A')
				->where('cutoff LIKE ?', 10000)
				->where('dist <= ?', $radius)
				->order('n.dist ASC')
				->query()
				->fetchall();
	*/
	$data = $db->select()->from(array('n' => 'dist_matrix'))
		->join(array('v' => 'gly_variability'), 'n.residue2 = v.residue', array('v.variability'))
		->where('residue1 LIKE ?', $gly_residue)
		->where('chain1 LIKE ?', 'A')
		->where('chain2 LIKE ?', 'A')
		->where('dist <= ?', $radius)
		->order('n.dist ASC')
		->query()
		->fetchall();		
	#Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	#Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	#$db->getProfiler()->setEnabled(false);
	return $data;
}

$gly_residue = $_GET['residue'];
$radius = $_GET['radius'];
$neighbours = get_neighbours($gly_residue, $radius);
$neighbours_dist = get_neighbours_dist($gly_residue, $radius);
echo json_encode(array('variability' => $neighbours, 'distances' => $neighbours_dist));

?>