<?
include "inc.php";
if(isset($_GET['from']) && !empty($_GET['from'])) {
	$from = (int) $_GET['from'];
}
if(isset($_GET['to']) && !empty($_GET['to'])) {
	$to = (int) $_GET['to'];
}
if(isset($_GET['residue']) && !empty($_GET['residue'])) {
	$centroid_residue = (int) $_GET['residue'];
}

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

$mutabilities = get_mutabilities($from, $to);
$fixations = get_residue_by_type('fixation', $mutabilities);
$polymorphisms = get_residue_by_type('polymorphic', $mutabilities, $fixations);
$intermediates = get_residue_by_type('intermediate', $mutabilities, array_merge($fixations,$polymorphisms));
$invariants = get_residue_by_type('invariant', $mutabilities, array_merge($fixations,$polymorphisms,$intermediates));

$results = array();
$results['fixations'] = $fixations;
$results['polymorphisms'] = $polymorphisms;
$results['intermediates'] = $intermediates;
$results['invariants'] = $invariants;

//plot each on pml
$fixations_array = array();
foreach($fixations as $fixation) {
	if($fixation != $centroid_residue)
		$fixations_array[] = preg_replace('/\D/', '', $fixation);
}
$fixations_str = implode('+', $fixations_array);

$polymorphisms_array = array();
foreach($polymorphisms as $polymorphism) {
	if($polymorphism != $centroid_residue)
		$polymorphisms_array[] = preg_replace('/\D/', '', $polymorphism);
}
$polymorphisms_str = implode('+', $polymorphisms_array);

$intermediates_array = array();
foreach($intermediates as $intermediate) {
	if($intermediate != $centroid_residue)
		$intermediates_array[] = preg_replace('/\D/', '', $intermediate);
}
$intermediates_str = implode('+', $intermediates_array);

//read template
$template = file_get_contents('/www/htdocs/3fx.us/bio/gly/pymol/template_fixations.pml');

if(strlen($fixations_str) > 0)
	$template = str_replace('<fixations>',"select fixations, resi $fixations_str and chain a\ncolor blue, fixations\n", $template);
else
	$template = str_replace('<fixations>',"\n", $template);

if(strlen($polymorphisms_str) > 0)
	$template = str_replace('<polymorphisms>',"select polymorphisms, resi $polymorphisms_str and chain a\ncolor red, polymorphisms\n", $template);
else
	$template = str_replace('<polymorphisms>',"\n", $template);
	
if(strlen($intermediates_str) > 0)
	$template = str_replace('<intermediates>',"select intermediates, resi $intermediates_str and chain a\ncolor pink, intermediates\n", $template);
else
	$template = str_replace('<intermediates>',"\n", $template);

$year_range = $from.'_'.$to;
$template = str_replace('<centroid_residue>',$centroid_residue, $template);
$template = str_replace('<output>',$centroid_residue.'_'.$year_range, $template);

//get correct view
$tpl_view = file_get_contents('/www/htdocs/3fx.us/bio/gly/pymol/residues/'.$centroid_residue.'.tpl');
$template = str_replace('<tpl_view>',$tpl_view, $template);

$tpl_file = $centroid_residue.'_'.$year_range.'.pml';
file_put_contents('/www/htdocs/3fx.us/bio/gly/pymol/cache_fixations/'.$tpl_file, $template);
#echo "done\n";

$pymol_str = 'pymol -c /www/htdocs/3fx.us/bio/gly/pymol/cache_fixations/'.$tpl_file.'';
if(isset($_GET['cache'])) {
	header('Content-type: application/txt');
	header('Content-Disposition: attachment; filename="'.$tpl_file.'"');
	echo file_get_contents('/www/htdocs/3fx.us/bio/gly/pymol/cache_fixations/'.$tpl_file);
	die();
}
if(isset($_GET['gen'])) {
	exec($pymol_str, $r);
	print_r($r);
}

#header("Content-Type: image/png");
echo file_get_contents('/www/htdocs/3fx.us/bio/gly/pymol/images_fixations/'.$centroid_residue.'_'.$year_range.'.png');


?>