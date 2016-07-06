<?php
include "inc.php";
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

$residue = $_GET['residue'];


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
	$results[] = (float) @number_format(($gly_sites[$i]/$gly_years[$i])*100, 2) ;
}
echo json_encode($results);
?>