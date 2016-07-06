<?php
include "inc.php";
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');


$residue = $_REQUEST['residue'];
$gly_residue = $_REQUEST['gly_site'];

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
				->where('a.ha1_sequence NOT LIKE ?', '%X%')
				->where('LENGTH(a.ha1_sequence) > 320')
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
		$fixation[$v['year']][$v['aa']] = $v['total'];
	}
	return array('fixations' => $fixation, 'fixation_residues' => $fixation_residues);
}

function get_totals() {
	global $db;
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
	return $gly_years;
}

function calculate_simpson_index($different_amino_acids, $total_sequences = 100) {
	$total = 0;
	foreach($different_amino_acids as $k => $v) {
		$total = $total + ($v*($v-1));
	}
	@$variability = $total / ($total_sequences * ($total_sequences-1));
	if($variability == 0)
		$variability = 1;
	$variability = number_format(1-$variability, 2);
	return $variability;
}


$gly_years = get_totals();

function get_fixations_points($residue) {
	global $gly_years;
	$fixations = get_fixations($residue);

	$data_points = array();
	foreach($fixations['fixation_residues'] as $residue) {
		$data_points[$residue] = array();
		foreach($fixations['fixations'] as $y => $fixation) {
			$data_points[$residue][] = (@$fixation[$residue]/array_sum($fixation))*100;
		}
	}
	$fixations['data_points'] = $data_points;
	return $fixations;
}

$fixations = get_fixations_points($residue);

//for 

foreach($fixations['data_points'] as $amino => $counts) {
	
	for($i = 0; $i < count($counts); $i++) {
		$different_amino_acids[$i][$amino] = $fixations['data_points'][$amino][$i];
		
	}
	
}

$variations = array();
foreach($different_amino_acids as $year => $counts) {
	$variations[$year] = calculate_simpson_index($counts)*100;
}

#print_r($variations);


///gly
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
			->where('g.position_aa = ?', $gly_residue)
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
///gly

#print_r($results);
function calculate_correlation($x_data, $y_data) {
	$number_of_values = count($x_data);

	$table = array();
	for($i = 0; $i < count($x_data); $i++) {
		$tmp = array();
		$tmp['x'] = $x_data[$i];
		$tmp['y'] = $y_data[$i];	
		$tmp['xy'] = $tmp['x']*$tmp['y'];
		$tmp['x2'] = $tmp['x']*$tmp['x'];
		$tmp['y2'] = $tmp['y']*$tmp['y'];
		$table[] = $tmp;
	}

	$x_sum = 0;
	$y_sum = 0;
	$xy_sum = 0;
	$x2_sum = 0;
	$y2_sum = 0;

	foreach($table as $row) {
		$x_sum = $x_sum + $row['x'];
		$y_sum = $y_sum + $row['y'];
		$xy_sum = $xy_sum + $row['xy'];
		$x2_sum = $x2_sum + $row['x2'];
		$y2_sum = $y2_sum + $row['y2'];
	}
	$top = ($number_of_values * $xy_sum) - ($x_sum*$y_sum);
	$x2 = ($number_of_values * $xy_sum) - ($x_sum*$y_sum);

	@$r = (($number_of_values * $xy_sum) - ($x_sum*$y_sum)) / sqrt(  (($number_of_values * $x2_sum)  - ($x_sum*$x_sum)) *  (($number_of_values * $y2_sum)  - ($y_sum*$y_sum)) );
	return $r;
}
function mmmr($array, $output = 'mean'){
    if(!is_array($array)){
        return FALSE;
    }else{
        switch($output){
            case 'mean':
                $count = count($array);
                $sum = array_sum($array);
                $total = $sum / $count;
            break;
            case 'median':
                rsort($array);
                $middle = round(count($array) / 2);
                $total = $array[$middle-1];
            break;
            case 'mode':
                $v = array_count_values($array);
                arsort($v);
                foreach($v as $k => $v){$total = $k; break;}
            break;
            case 'range':
                sort($array);
                $sml = $array[0];
                rsort($array);
                $lrg = $array[0];
                $total = $lrg - $sml;
            break;
        }
        return $total;
    }
} 
function calculate_relevence($x_data, $y_data) {

	$gly_results = array();
	$gly_variations = array();	
	$non_gly_results = array();
	$non_gly_variations = array();
	$gly_point = null;
	foreach($y_data as $k => $v) {
		if( $v >= 75 ) {
			if(is_null($gly_point)) {
				$gly_point = $k;
			}
		}
	}
	
	$years = 8;
	$highlight = array();
	//take only 5 years either side
	foreach($y_data as $k => $v) {
		$highlight[$k] = 0;
		if( $k >= $gly_point && $k < $gly_point+$years ) {
			if( $x_data[$k] >= 0 ) {
				$gly_results[$k] = $v;
				$gly_variations[$k] = $x_data[$k];
			}
			$highlight[$k] = 100;
		}
		
		if( $k >= $gly_point-$years && $k < $gly_point ) {
			if( $x_data[$k] >= 0 ) {
				$non_gly_results[$k] = $v;
				$non_gly_variations[$k] = $x_data[$k];
			}
			$highlight[$k] = 100;
		}
		
	}
	
	if(empty($gly_variations))
		$gly_variations = array(0);
	if(empty($non_gly_variations))
		$non_gly_variations = array(0);
	$gly_r = mmmr($gly_variations);
	$non_gly_r = mmmr($non_gly_variations);
	$relevence = $non_gly_r - $gly_r;
	#print_r($gly_r);echo "\t";
	#print_r($non_gly_r);
	#echo "\n";
	#die();
	return array($relevence, $highlight);
}
#print_r($variations);
$r = calculate_correlation($variations, $results);
list($relevance,$highlight) = calculate_relevence($variations, $results);

$url = "http://3fx.us/bio/gly/ajax/get_glycosylation_sites.php?residue=$gly_residue";
$gly_area = file_get_contents($url);
$gly_area = json_decode($gly_area, true);

echo json_encode(array('variations' => $variations, 'gly' => $results, 'correlation' => (float) $r, 'relevance' => (float) $relevance, 'fixations' => $fixations, 'highlight' => $highlight, 'gly_area' => $gly_area));

?>