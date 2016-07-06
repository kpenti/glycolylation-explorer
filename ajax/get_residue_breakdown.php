<?php

include "inc.php";

/*

 - residue breakdown
 
 

*/



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
	$sql = "SELECT s.cluster, COUNT(*), IF( RIGHT(s.cluster, 2) ='02' ,'99',RIGHT(s.cluster, 2))
FROM smith_strains AS s
WHERE s.cluster IS NOT NULL
GROUP BY s.cluster
ORDER BY IF( RIGHT(s.cluster, 2) ='02' ,'99',RIGHT(s.cluster, 2))
			";
	$data = $db->query($sql)->fetchall();
	return $data;
}
function find_important_amino_acids_antigenic($cluster) {
	global $db, $important_amino_acids;
	$sql = "SELECT * FROM smith_strains AS s
					JOIN aasequence AS a ON a.strain_id = s.strain_id
					WHERE a.ha1_sequence IS NOT NULL AND LENGTH(a.ha1_sequence) > 320
					AND s.cluster = '$cluster'
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
		$residue = $k +1;
		if(!in_array($residue, $important_amino_acids)) {
			$tmp = array_count_values($v);
			$tmp_counts = array();
			foreach($tmp as $tk => $tv) {
				$tmp_counts[$tk] = ( $tv/array_sum($tmp) ) * 100;
			}
			$count_diffs[$residue] = $tmp_counts;
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
		if( count($v) == 1 ) {
			$cl_prop[$k] = $aa;
		}
	}
	
	foreach($cluster_2 as $k => $v){
		list($aa, $aa_count) = get_first($v);
		if( count($v) == 1 ) {
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

$important_amino_acids = find_important_amino_acids();

//now for each antigenic cluster find the most import amino acids
$antigenic_clusters = find_antigenic_clusters();
$antigenic_clusters = array();
$antigenic_clusters[] = 'WU95';
$antigenic_clusters[] = 'SY97';

foreach($antigenic_clusters as $cluster) {
	#if($cluster['cluster'] == '')
	#	continue;
	$cluster_residues[$cluster] = find_important_amino_acids_antigenic($cluster);
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
		
		$x_axis = array_keys($c1);
		break;	
	}

}

$list = file('amino_list');
$aas = array();
foreach($list as $k => $v) {
	$aa_data = explode("\t", $v);
	$aas[] = $aa_data[0];
}
#print_r($aas);
$data_points = array();
$trans_data = $transitions[$antigenic_clusters[0].'-'.$antigenic_clusters[1]];
/*print_r($trans_data);die();
foreach($trans_data as $cluster => $amino_acids) {
	#$data_points[$cluster] = array();
	foreach($amino_acids as $residue => $v) {
		$points = array();
		foreach($v as $ak => $av) {
			$points[] = $av;
		}
		$data_points[] = array('name' => $residue, 'stack' => $cluster, 'residues' => $points);
	}
}*/
foreach($aas as $amino_acid) {
	$points = array();
	/*foreach($aas as $ak => $av) {
		$points[] = (float) @$v[$av];
	}*/
	/*foreach($v as $ak => $av) {
		$points[] = $av;
	}*/
	foreach($trans_data as $cluster => $amino_acids) {
		foreach($amino_acids as $res => $counts) {
			$points[] = (float) @$counts[$amino_acid];
		}
		#print_r($points);die();
		$data_points[$cluster.$amino_acid] = array('name' => $amino_acid, 'stack' => $cluster, 'residues' => $points);
	}
}
#print_r($data_points);
#die();

/*
name: '133',
			data: [133d, 3, 4, 7, 2],
			stack: 'WU97'*/
$results = array();
$results['x_axis'] = $x_axis;
#$results['x_axis'] = $aas;
$results['data_points'] = $data_points;

echo json_encode($results);
die();


?>