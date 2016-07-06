<?
header('Content-type: application/json');
$periods = json_decode(file_get_contents('../tools/periods.json'), true);
$matches = json_decode(file_get_contents('../tools/matches.json'), true);
include "inc.php";

function get_closest_glycosylation_sites($residue) {
	global $db, $cutoff;
	
	$gly_residues  = array(8, 22, 38, 63, 122, 126, 133, 165, 246, 285);
	$data = $db->select()->from(array('n' => 'dist_matrix'))
				->where('residue1 LIKE ?', $residue)
				->where('residue2 IN (?)', $gly_residues)
				->where('chain1 LIKE ?', 'A')
				->where('chain2 LIKE ?', 'A')
				->query()
				->fetchall();
				
				
	#Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	#Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	#$db->getProfiler()->setEnabled(false);
	return $data;
}

$file = file_get_contents('../tools/vari.json');
$data = json_decode($file, true);

$binary_major = array();
$binary = array();
foreach($data as $residue => $variances) {
	foreach($variances as $i => $value) {
		$bool = 0;
		$bool_major = 0;
		if($value > 15)
			$bool = 1;
		if($value > 15)
			$bool_major = 1;
		$binary[$residue][$i] = $bool;
		$binary_major[$residue][$i] = $bool_major;
	}
}


//every 2 years
$years_span = 0;
$activity = array();
$all_residues = array();
#print_r(($periods));die();
for($i = 0; $i <= 43; $i=$i+1 ) {
	#echo $i."\n";
	#die();
	$tmp = array();
	foreach($binary as $residue => $period) {
		foreach($period as $year => $value) {
		
		//var_dump($i);
			if($year >= $i && $year <= $i+$years_span) {
			#var_dump($year);
				if($value > 0) {//bool
					$tmp[] = $residue;
					$all_residues[] = $residue;
				}
			}
		}
	}
	
	$activity[$i+1968] = array_unique($tmp);
			/*echo "$i\n";
		print_r($activity);
		die();*/

}

$res_dists = array();
/*foreach($all_residues as $k => $residue) {
	$data = get_closest_glycosylation_sites($residue);
	$res_dists[$residue] = $data;
}*/

echo json_encode(array('residues' => $activity, 'res_dists' => $res_dists));
?>