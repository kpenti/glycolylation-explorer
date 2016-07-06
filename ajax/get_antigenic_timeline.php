<?php

include "inc.php";


function format_year($year) {
	return substr($year, 2, 2);
}

function get_antigenic_clusters() {
	global $db;
	$sql = "SELECT DISTINCT gly_cluster FROM aasequence WHERE gly_cluster IS NOT NULL AND gly_cluster != ''";
	$data = $db->query($sql)->fetchall();
	
	$gly_clusters = array();
	foreach($data as $row) {
		$gly_clusters[] = $row['gly_cluster'];
	}
	return $gly_clusters;
}

function make_antigenic_timeline() {
	global $db;
	$changes_min = 0;
	$changes_max = 2147483647;
	if(isset($_REQUEST['changes_min']) && !empty($_REQUEST['changes_min'])) {
		$changes_min = $_REQUEST['changes_min'];
	}
	if(isset($_REQUEST['changes_max']) && !empty($_REQUEST['changes_max'])) {
		$changes_max = $_REQUEST['changes_max'];
	}
	
	$ad_min = 0;
	$ad_max = 2147483647;
	if(isset($_REQUEST['ad_min']) && !empty($_REQUEST['ad_min'])) {
		$ad_min = $_REQUEST['ad_min'];
	}
	if(isset($_REQUEST['ad_max']) && !empty($_REQUEST['ad_max'])) {
		$ad_max = $_REQUEST['ad_max'];
	}
	
	$clusters = get_antigenic_clusters();
	if(isset($_REQUEST['clusters']) && !empty($_REQUEST['clusters'])) {
		$clusters = $_REQUEST['clusters'];
	}
	
	$clusters = "'".implode("','", $clusters)."'";
	#var_dump($clusters);
	$sql = "SELECT s1.fullname as s1_name, s2.fullname as s2_name, s1.year as s1_year, s2.year as s2_year, ABS(d.antiserum_al) AS antiserum_al, ABS(d.antisera_al) AS antisera_al, d.no_changes, a1.gly_cluster as s1_gly_cluster, a2.gly_cluster as s2_gly_cluster, a1.gly_sites as s1_gly_sites, a2.gly_sites as s2_gly_sites, d.aa_diffs
FROM gly_dist AS d
JOIN aasequence AS a1 ON a1.sequence_id = d.sequence1_id
JOIN aasequence AS a2 ON a2.sequence_id = d.sequence2_id
JOIN strain AS s1 ON s1.strain_id = d.strain1_id
JOIN strain AS s2 ON s2.strain_id = d.strain2_id
WHERE d.no_changes >= $changes_min AND d.no_changes <= $changes_max
AND ABS(d.antisera_al) >= $ad_min AND ABS(d.antisera_al) <= $ad_max
AND a1.gly_cluster IN ($clusters) AND a2.gly_cluster IN ($clusters)
AND s1.year < 2000 AND s2.year < 2000 
ORDER BY IF(a1.gly_cluster != a2.gly_cluster, -ABS(d.antisera_al), ABS(d.antisera_al)) 
	";
	#GROUP BY IF(d.strain1_id >= d.strain2_id, CONCAT_WS('-',d.strain1_id, d.strain2_id), CONCAT_WS('-',d.strain2_id, d.strain1_id))
	#ORDER BY d.no_changes ASC, s1.year ASC, s2.year ASC, d.antiserum_al ASC, d.antisera_al ASC, s1.year ASC, s1.fullname ASC, s2.year ASC, s2.fullname ASC
	#echo $sql;	die();
	$data = $db->query($sql)->fetchall();
	return $data;
}
$data = make_antigenic_timeline();


?>
<table id="thetable" class="table table-bordered table-striped">
	<thead>
		<? foreach($data as $row) : ?>
		<tr>
			<td style="width: 300px;display: block;">Strain</td>
			<td>Start</td>
			<td>End</td>
			<td>Strain 1 cluster</td>
			<td>Strain 2 cluster</td>
			<?/*<td>Strain 1 sites</td>
			<td>Strain 2 sites</td>*/?>
			<td>No. changes</td>
			<td>AD (antiserum)</td>
			<td>AD (antisera)</td>
			<?/*<? for($i = 1968; $i < 2012; $i++) : ?>
				<td><?= format_year($i) ?></td>
			<? endfor; ?>*/?>
		</tr>
		<? break; ?>
		<? endforeach; ?>
	</thead>
	<tbody>
		<? foreach($data as $row) : ?>
		<?
		$row['aa_diffs'] = str_replace(",", " ", $row['aa_diffs']);
		?>
		<tr>
			<td><?= $row['s1_name'] ?> - <?= $row['s2_name'] ?></td>
			<? $start_year = ($row['s1_year'] < $row['s2_year'])?$row['s1_year']:$row['s2_year']; ?>
			<? $end_year = ($row['s1_year'] > $row['s2_year'])?$row['s1_year']:$row['s2_year']; ?>
			<td><?= $start_year ?></td>
			<td><?= $end_year ?></td>
			<td><?= $row['s1_gly_cluster'] ?></td>
			<td><?= $row['s2_gly_cluster'] ?></td>
			<?/*<td><?= $row['s1_gly_sites'] ?></td>
			<td><?= $row['s2_gly_sites'] ?></td>*/?>
			<td style="font-weight: bold;" class="changes" data-content="<?= $row['aa_diffs'] ?>" rel="popover"><a href="#"><?= $row['no_changes'] ?></a></td>
			<td style="background: #bbddff"><?= $row['antiserum_al'] ?></td>
			<td style="background: #bbddff"><?= $row['antisera_al'] ?></td>
		</tr>
		<tr>
			<td colspan="10">
			<ul>
			<? for($i = 1968; $i < 2012; $i++) : ?>
				<li>
					<a <? if($i >= $start_year && $i <= $end_year) : ?>style="background: #333333; color: #fff;"<? endif; ?> href="#"><?= format_year($i) ?></a>
				</li>
			<? endfor; ?>
			</ul>
			</td>
		</tr>
		<? endforeach; ?>
	</tbody>
</table>
<h3>Count : <?= count($data) ?></h3>