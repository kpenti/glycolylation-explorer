<?
/*

	get start and end year

	get fixations -> highlight
	get polymorphisms -> highlight
	get intermediates -> highlight
	get invariants -> highlight

*/

include "inc.php";

function get_mutabilities($from, $to) {
	global $db;
	$data = $db->select()
				->from('gly_mutability')
				->where('start_year >= ?', $from)
				->where('end_year <= ?', $to)
				->order('residue ASC')
				->query()
				->fetchall();
	return $data;
}

function get_residue_by_type($type, $mutabilities, $exclude = array()) {
	$residues = array();
	foreach($mutabilities as $mutability) {
		if(in_array($mutability['residue'], $exclude))
			continue;
		if($mutability['mutability'] == $type) {
			$residues[] = $mutability['residue'];
		}
	}
	return array_values(array_unique($residues));
}

$from = $_GET['from'];
$to = $_GET['to'];

$mutabilities = get_mutabilities($from, $to);
$fixations = get_residue_by_type('fixation', $mutabilities);
$polymorphisms = get_residue_by_type('polymorphic', $mutabilities, $fixations);
$intermediates = get_residue_by_type('intermediate', $mutabilities, array_merge($fixations,$polymorphisms));
$invariants = get_residue_by_type('invariant', $mutabilities, array_merge($fixations,$polymorphisms,$intermediates));
/*echo "fixations\n";
print_r($fixations);
echo "polymorphisms\n";
print_r($polymorphisms);
echo "intermediates\n";
print_r($intermediates);
echo "invariants\n";
print_r($invariants);*/

$results = array();
$results['fixations'] = $fixations;
$results['polymorphisms'] = $polymorphisms;
$results['intermediates'] = $intermediates;
$results['invariants'] = $invariants;

echo json_encode($results);
?>