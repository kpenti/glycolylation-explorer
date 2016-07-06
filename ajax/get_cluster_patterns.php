<?php

include "inc.php";
#header('Cache-Control: no-cache, must-revalidate');
#header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
#header('Content-type: application/json');
error_reporting(0);

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

//go through each sequence and find important residues only
function find_important_amino_acids() {
	global $db;
	$sql = "SELECT * FROM smith_strains AS s
					JOIN aasequence AS a ON a.strain_id = s.strain_id
					WHERE a.ha1_sequence IS NOT NULL AND LENGTH(a.ha1_sequence) > 320
			";
	$data = $db->query($sql)->fetchall();

	$diffs = array();
	foreach($data as $row) {
		for($i = 0; $i < 327; $i++) {
			@$diffs[$i][] = $row['ha1_sequence'][$i];
		}
		
	}

	$count_diffs = array();
	foreach($diffs as $k => $v) {
		$count_diffs[$k +1] = array_count_values($v);
	}
	
	$important_aminos = array();
	foreach($count_diffs as $k => $v) {
		foreach($v as $aa => $aa_count) {
			if($aa_count == 260) {
				$important_aminos[] = $k;
			}
		}
	}
	
	
	return $important_aminos;
}

function find_antigenic_clusters() {
	global $db;
	$sql = "SELECT s.cluster, COUNT(*)
FROM smith_strains AS s
WHERE s.cluster IS NOT NULL
GROUP BY s.cluster
ORDER BY IF( RIGHT(s.cluster, 2) ='02' ,'99',RIGHT(s.cluster, 2))
			";
	$data = $db->query($sql)->fetchall();
	return $data;
}


function fetch_important_amino_acids_cluster($cluster) {
	global $db, $important_amino_acids;
	$sql = "SELECT * FROM smith_strains AS s
					JOIN aasequence AS a ON a.strain_id = s.strain_id
					WHERE a.ha1_sequence IS NOT NULL AND LENGTH(a.ha1_sequence) > 320
					AND s.cluster = '$cluster'
			";
	$data = $db->query($sql)->fetchall();
	return $data;
}
function find_important_amino_acids_cluster($sequences) {
	global $db, $important_amino_acids;

	$data = $sequences;

	$diffs = array();
	foreach($data as $row) {
		for($i = 0; $i < 327; $i++) {
			$diffs[$i][] = $row['ha1_sequence'][$i];
		}
		
	}

	$count_diffs = array();
	foreach($diffs as $k => $v) {
		$residue = $k +1;
		if(!in_array($residue, $important_amino_acids)) {
			$count_diffs[$residue] = array_count_values($v);
		}
	}
	
	return $count_diffs;
}

function get_first($cluster) {
	foreach($cluster as $k => $v){
		return array($k, $v);
	}
}

function find_cluster_differences($cluster_1, $cluster_2) {
	
	$cl_prop = array();
	foreach($cluster_1 as $k => $v){
		list($aa, $aa_count) = get_first($v);
		if( $aa_count == array_sum($v) ) {
			$cl_prop[$k] = $aa;
		}
	}
		
	foreach($cluster_2 as $k => $v){
		list($aa, $aa_count) = get_first($v);
		if( $aa_count == array_sum($v) ) {
			$c2_prop[$k] = $aa;
		}
	}
	
	//ignore residues that are the same between the two clusters
	$ignore_residues = array();
	foreach($cl_prop as $residue_i => $aa_i) {
		foreach($c2_prop as $residue_j => $aa_j) {
			if($residue_i == $residue_j) {
				if($cl_prop[$residue_i] == $c2_prop[$residue_j]) {
					//remove those that are the same
					$ignore_residues[] = $residue_i;
				}
			}
		}
	}
	
	//remove ignored residues
	foreach($cluster_1 as $k => $v){
		if( in_array( $k, $ignore_residues) ) {
			unset($cluster_1[$k]);
		}
	}	
	
	foreach($cluster_2 as $k => $v){
		if( in_array( $k, $ignore_residues) ) {
			unset($cluster_2[$k]);
		}
	}
	
	return array($cluster_1, $cluster_2);
}


function get_sequence_perms($sequences, $perms) {
	$sequence_patterns = array();
	foreach($sequences as $sequence) {
		
		$pattern = array();
		for($i = 0; $i < strlen($sequence['ha1_sequence']); $i++) {
			if(in_array($i+1, $perms)) {
				$pattern[$i+1] = ($sequence['ha1_sequence'][$i]);
			}
		}
		$sequence_patterns[] = implode('', $pattern);		
	}
	$sequence_patterns = array_count_values($sequence_patterns);
	arsort($sequence_patterns);
	return $sequence_patterns;
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
function get_brightness($hex) {
// returns brightness value from 0 to 255

// strip off any leading #
$hex = str_replace('#', '', $hex);

$c_r = hexdec(substr($hex, 0, 2));
$c_g = hexdec(substr($hex, 2, 2));
$c_b = hexdec(substr($hex, 4, 2));

return (($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000;
}
function get_style($aa) {
	$list = file('amino_list');
	foreach($list as $k => $v) {
		$aa_data = explode("\t", $v);
		if($aa_data[0] == $aa) {
			#print_r($aa_data);
			$color = $aa_data[2];
			break;
		}
	}
	$text = get_brightness($color);
	if($text > 130) {
		$text_color = '#000';
	}else {
		$text_color = '#fff';
	}
	$style = "background:#".$color.";color:$text_color;$text";
	return $style;
}

function get_class_gly($residue) {
	global $main_gly, $close_gly;
	
	if(in_array($residue, $main_gly)) {
		$color = 'gly_main';
	}	
	
	if(in_array($residue, $close_gly)) {
		$color = 'gly_close';
	}
	
	return $color;
	
}
#background: #<?= 

#$important_amino_acids = find_important_amino_acids();
$important_amino_acids = array();
#print_r($important_amino_acids);die();
//now for each antigenic cluster find the most import amino acids
$antigenic_clusters = find_antigenic_clusters();
#print_r($antigenic_clusters);die();
$cluster = $_GET['cluster'];
$variation = $_GET['variation'];
$sequences = fetch_important_amino_acids_cluster($cluster);
$important_cluster_residues = find_important_amino_acids_cluster($sequences);
foreach($important_cluster_residues as $v) {
	$total = array_sum($v);
	break;
}

$permutation_residues = array();
foreach($important_cluster_residues as $k => $v) {
	$variability = calculate_simpson_index($v, $total);
	if($variability<$_GET['variation']) {
		unset($important_cluster_residues[$k]);
	}
}
#print_r($important_cluster_residues);
$permutation_residues = array_keys($important_cluster_residues);
//now align all sequences and only extract those permutation_residues

$sequence_patterns = get_sequence_perms($sequences, $permutation_residues);

$main_gly = array(8,63,81,122,126,133,144,246,276);
$close_gly = array();
foreach($main_gly as $residue) {
	for($i = $residue+1; $i < $residue+4; $i++) {
		$close_gly[] = $i;
	}
}

?>
<table class="table table-bordered table-striped">
        <thead>
          <tr>
		  <? foreach($permutation_residues as $residue): ?>
			<th class="<?= get_class_gly($residue); ?>"><?= $residue ?></th>
		  <? endforeach; ?>
		  <th>Total</th>
          </tr>
        </thead>
        <tbody>
		  <? foreach($sequence_patterns as $sequence => $count): ?>
          <tr>
		    <? for($i = 0; $i < strlen($sequence); $i++): ?>
            <td style="<?= get_style($sequence[$i]) ?>"><?= $sequence[$i] ?></td>
			<? endfor; ?>
			<td style=""><?= $count ?></td>
          </tr>
		  <? endforeach; ?>
        </tbody>
      </table>

<?
die();

foreach($antigenic_clusters as $cluster) {
	if($cluster['cluster'] == '')
		continue;
	$cluster_residues[$cluster['cluster']] = find_important_amino_acids_antigenic($cluster['cluster']);
}

//find important changes between clusters
$transitions = array();
foreach($cluster_residues as $cluster_i => $aas_i) {
	//find the one after
	$continue = true;
	foreach($cluster_residues as $cluster_j => $aas_j) {
		if($cluster_i == $cluster_j) {
			$continue = false;
		}
		if($continue)
			continue;
		if($cluster_i == $cluster_j) {
			continue;
		}
		list($c1, $c2) = find_cluster_differences($aas_i, $aas_j);
		$transitions[$cluster_i.'-'.$cluster_j][$cluster_i] = $c1;
		$transitions[$cluster_i.'-'.$cluster_j][$cluster_j] = $c2;
		
		break;	
	}

}
?>