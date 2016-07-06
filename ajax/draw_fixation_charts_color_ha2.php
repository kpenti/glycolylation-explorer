<?
#include "inc.php";
/*header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');/*/
error_reporting(E_ALL);

/*include("C:\wamp\www\bio\glycosylation\pChart2.1.3/class/pData.class.php");
include("C:\wamp\www\bio\glycosylation\pChart2.1.3/class/pDraw.class.php");
include("C:\wamp\www\bio\glycosylation\pChart2.1.3/class/pImage.class.php");
include("C:\wamp\www\bio\glycosylation\pChart2.1.3/class/pScatter.class.php");
*/
#error_reporting(0);

require_once ('jpgraph-3.0.7/src/jpgraph.php');
require_once ('jpgraph-3.0.7/src/jpgraph_line.php');
die();
$main_residue = (int) $_GET['residue'];
$gly_residue = (int) @$_GET['gly'];
#$size = (int) $_GET['size'];
/*if(!$main_residue && !$gly_residue) {
	die();	
}*/

$file_save_path = "graphs_bw_ha2/$main_residue-$gly_residue.png";
/*if(file_exists($file_save_path) && $size!=1){
	header("Content-Type: image/png");
	echo file_get_contents($file_save_path);
	die();
}*/

/* Create and populate the pData object */
#$MyData = new pData(); 
if($gly_residue ) {
	$str = 'http://3fx.us/bio/gly/ajax/get_glycosylation_sites_ha2.php?residue='.$gly_residue;
	$glycosylation_sites = file_get_contents($str);
	$glycosylation_sites = json_decode($glycosylation_sites, true);
	#trace($glycosylation_sites);
}

/* Create and populate the pData object */
#$glycosylation_data = new pData();  
#$glycosylation_data->addPoints($glycosylation_sites,"Glycosylation");
$str = 'http://3fx.us/bio/gly/ajax/get_fixations_ha2.php?residue='.$main_residue;
$fixations = file_get_contents($str);
$fixations = json_decode($fixations, true);

$ratio = 3.5;

$height = 165*$ratio;
$width = 970*$ratio;

$graph = new Graph($width,$height);

$graph->img->SetAntiAliasing(false); 
$graph->SetScale('intlin');
$graph->SetShadow();
 
// Setup margin and titles
$graph->SetMargin(180,280,120,40); //with gly
$graph->SetMargin(180,180,120,40); //withot gly legend
#$graph->title->Set('Residue '.$main_residue);
$graph->xaxis->title->Set('Years');
$graph->yaxis->title->Set('% strains');
$graph->yaxis->SetTitlemargin(120); 
$graph->title->SetFont(FF_ARIAL,FS_BOLD,18);
$graph->xaxis->title->SetFont(FF_ARIAL,FS_BOLD,36);
$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,36);
$graph->SetMarginColor('white');
$graph->SetShadow(false);


$txt = new Text('Residue ' . $main_residue. ' ');
$txt->SetFont(FF_ARIAL,FS_BOLD,48);
$txt->SetPos(370,0,'center','top');
#$txt->SetBox('yellow','black');
$graph->AddText($txt); 


$start = 1968;
foreach($fixations['data_points'] as $data_points) {
	foreach($data_points as $k => $v) {
		$tickPositions[] = $k;
		$tickLabels[] = substr($start + $k, -2);
	}
	break;
}
#print_r($tickLabels);die();
$graph->xaxis->SetMajTickPositions($tickPositions,$tickLabels);
$graph->xaxis->SetTextLabelInterval(5); 
$graph->xaxis->SetTextTickInterval(2,2); 
$graph->xaxis->SetFont(FF_ARIAL,FS_BOLD,48);
$graph->xaxis->scale->ticks->SupressMinorTickMarks();

//echo '<pre>';
$tickPositions = array(0,10,2,3,4,5,6,7,8,9,10);
$tickLabels = array(0,10,20,30,40,50,60,70,80,90,100);
$tickPositions = array(0,10,20,30,40,50,60,70,80,90,100);
/*print_r($tickPositions);
print_r($tickLabels);
die();*/
$graph->yaxis->SetFont(FF_ARIAL,FS_BOLD,48);
$graph->yaxis->SetMajTickPositions($tickPositions, $tickLabels); 
$graph->yaxis->SetTextLabelInterval(5); 
$graph->yaxis->SetTextTickInterval(1,3); 
$graph->yaxis->HideFirstTicklabel();
 
$graph->legend->SetFillColor('white'); 
$graph->legend->SetPos(0.0005,0.005,'right','top');
$graph->legend->SetShadow(false);
$graph->legend->SetShadow(false);
$graph->legend->SetFont(FF_ARIAL,FS_NORMAL,42);
$graph->legend->SetFrameWeight(0); 
$graph->legend->SetMarkAbsSize(18);
$graph->legend->SetVColMargin(18);


$graph->SetFrame(true,'black',0);
 
$graph->ygrid->Show(false, false);
  

$colors_array = array();
$colors_array[] = array("R"=>0,"G"=>0,"B"=>0);
$colors_array[] = array("R"=>196,"G"=>196,"B"=>196);//pale blue
$colors_array[] = array("R"=>75,"G"=>75,"B"=>255);//pale blue
$colors_array[] = array("R"=>196,"G"=>196,"B"=>196);//pale blue
$colors_array[] = array("R"=>220,"G"=>130,"B"=>35);//pale blue
$colors_array[] = array("R"=>185,"G"=>0,"B"=>200);//pale blue
$colors_array[] = array("R"=>255,"G"=>255,"B"=>0);//pale blue
$colors_array[] = array("R"=>170,"G"=>255,"B"=>170);//pale blue
$colors_array[] = array("R"=>0,"G"=>165,"B"=>0);//pale blue
$colors_array[] = array("R"=>255,"G"=>0,"B"=>0);//red

$colors_array = array();
$colors_array[] = '009900';//red
$colors_array[] = '4B4BFF';//pale blue
$colors_array[] = 'DC8223';//pale blue
$colors_array[] = 'B900C8';//pale blue
$colors_array[] = 'C4C4FF';//pale blue
$colors_array[] = 'FF0000';//pale blue
$colors_array[] = '00FF66';//pale blue
$colors_array[] = '000000';//pale blue
$colors_array[] = 'AAFFAA';


$line_styles = array();
$line_styles[] = array('color' => $colors_array[0], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_FILLEDCIRCLE);
$line_styles[] = array('color' => $colors_array[1], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_DTRIANGLE);
$line_styles[] = array('color' => $colors_array[2], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_CROSS);
$line_styles[] = array('color' => $colors_array[3], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_STAR);
$line_styles[] = array('color' => $colors_array[4], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_SQUARE);
$line_styles[] = array('color' => $colors_array[5], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_DIAMOND);
$line_styles[] = array('color' => $colors_array[6], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_CIRCLE);
$line_styles[] = array('color' => $colors_array[7], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_UTRIANGLE);
$line_styles[] = array('color' => $colors_array[8], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_DTRIANGLE);
$line_styles[] = array('color' => $colors_array[9], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_STAR);

$line_styles = array();
$line_styles[] = array('color' => $colors_array[0], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_FILLEDCIRCLE);
$line_styles[] = array('color' => $colors_array[1], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_DTRIANGLE);
$line_styles[] = array('color' => $colors_array[2], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_CROSS);
$line_styles[] = array('color' => $colors_array[3], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_STAR);
$line_styles[] = array('color' => $colors_array[4], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_SQUARE);
$line_styles[] = array('color' => $colors_array[5], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_DIAMOND);
$line_styles[] = array('color' => $colors_array[6], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_CIRCLE);
$line_styles[] = array('color' => $colors_array[7], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_UTRIANGLE);
$line_styles[] = array('color' => $colors_array[8], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_DTRIANGLE);
$line_styles[] = array('color' => $colors_array[9], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_STAR);

if(isset($_GET['bw'])) {
	$colors_array = array();
	$colors_array[] = 'black';
	$colors_array[] = 'gray3';
	$colors_array[] = 'gray6';


	$line_styles = array();
	$line_styles[] = array('color' => $colors_array[0], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_SQUARE);
	$line_styles[] = array('color' => $colors_array[2], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_UTRIANGLE);
	$line_styles[] = array('color' => $colors_array[1], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_DTRIANGLE);
	$line_styles[] = array('color' => $colors_array[0], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_DIAMOND);
	$line_styles[] = array('color' => $colors_array[1], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_CIRCLE);
	$line_styles[] = array('color' => $colors_array[2], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_FILLEDCIRCLE    );
	$line_styles[] = array('color' => $colors_array[0], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_CROSS);
	$line_styles[] = array('color' => $colors_array[1], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_X);
	$line_styles[] = array('color' => $colors_array[1], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_STAR);
	$line_styles[] = array('color' => $colors_array[0], 'ticks' => 'solid', 'weight' => 2, 'shape' => MARK_FLASH );
}

#var_dump($line_styles);die();
if($gly_residue) {

	$lineplot_gly = new LinePlot($glycosylation_sites);
	$lineplot_gly->SetWeight( 50 );   // Two pixel wide

	$lineplot_gly->SetColor("#FAE2B9");
	#$lineplot_gly->SetFillColor("gray@0.5");
	$lineplot_gly->SetFillColor("#FFEECE@0.5");
	$lineplot_gly->SetWeight(8);
	$lineplot_gly->mark->SetSize(0); 
	#$lineplot_gly->SetLegend('GS'.$gly_residue);

	if(isset($_GET['bw'])) {
		$lineplot_gly->SetWeight( 4 );
		$lineplot_gly->SetColor("black");
		$lineplot_gly->SetFillColor("gray@0.5");
	}

	$graph->Add($lineplot_gly);
}
$fixations_data = array();
$lineplot = array();
$i = 0;
foreach($fixations['data_points'] as $k => $v) {
	// Create the first data series
	$lineplot[$k] = new LinePlot($v);
	
	$lineplot[$k]->SetColor('#'.$line_styles[$i]['color']);
	#$lineplot[$k]->SetColor('#FAE2B9');
	$lineplot[$k]->SetStyle($line_styles[$i]['ticks']);
	$lineplot[$k]->SetWeight( 8 );   // Two pixel wide
	$lineplot[$k]->mark->SetType($line_styles[$i]['shape'],1,0.6);
	$lineplot[$k]->mark->SetColor('#'.$line_styles[$i]['color']);
	$lineplot[$k]->mark->SetFillColor('#'.$line_styles[$i]['color']);
	$lineplot[$k]->mark->SetSize(10); 
	$lineplot[$k]->SetLegend($k);
	#var_dump($lineplot[$k]);die();
	$graph->Add($lineplot[$k]);

	if(isset($_GET['bw'])) {
		$lineplot[$k]->SetWeight( 4 );
		if($line_styles[$i]['shape'] == MARK_FILLEDCIRCLE)
			$lineplot[$k]->mark->SetSize(10);
		else
			$lineplot[$k]->mark->SetSize(15);
	}

	#$MyData->addPoints($v, $k);
	#$fixations_data[$k]->addPoints($v,$k);
	#trace($v);
	$i++;
}
$graph->legend->SetLineWeight(8);

#$graph->Stroke();
$fileName = "custom_php/gp.png";

#$graph->img->Stream($fileName);
die();
#$MyData->addPoints(array(4,2,10,12,8,3),"Probe 1");

$colors_array = array();
$colors_array[] = array("R"=>0,"G"=>0,"B"=>0);
$colors_array[] = array("R"=>208,"G"=>208,"B"=>208);
$colors_array[] = array("R"=>128,"G"=>128,"B"=>128);

$line_styles = array();
$line_styles[] = array('color' => $colors_array[0], 'ticks' => 1, 'weight' => 1, 'shape' => SERIE_SHAPE_FILLEDCIRCLE);
$line_styles[] = array('color' => $colors_array[0], 'ticks' => 5, 'weight' => 1, 'shape' => SERIE_SHAPE_FILLEDTRIANGLE);
$line_styles[] = array('color' => $colors_array[0], 'ticks' => 10, 'weight' => 1, 'shape' => SERIE_SHAPE_FILLEDSQUARE);
$line_styles[] = array('color' => $colors_array[1], 'ticks' => 10, 'weight' => 1, 'shape' => SERIE_SHAPE_FILLEDDIAMOND);
$line_styles[] = array('color' => $colors_array[2], 'ticks' => 1, 'weight' => 1, 'shape' => SERIE_SHAPE_CIRCLE);
$line_styles[] = array('color' => $colors_array[2], 'ticks' => 10, 'weight' => 1, 'shape' => SERIE_SHAPE_TRIANGLE);
$line_styles[] = array('color' => $colors_array[2], 'ticks' => 10, 'weight' => 1, 'shape' => SERIE_SHAPE_SQUARE);
$line_styles[] = array('color' => $colors_array[2], 'ticks' => 10, 'weight' => 1, 'shape' => SERIE_SHAPE_DIAMOND);

$MyData->addPoints($glycosylation_sites,"GLY");

foreach(array_keys($fixations['data_points']) as $k => $v) {
	$MyData->setSerieTicks($v, $line_styles[$k]['ticks']);
	$MyData->setSerieWeight($v, $line_styles[$k]['weight']);
	$MyData->setPalette($v, $line_styles[$k]['color']);
	#$MyData->setSerieShape($v,$line_styles[$k]['shape']);
}

$MyData->setSerieTicks("GLY",0);
$MyData->setAxisName(0,"Percentage");
$color = array("R"=>56,"G"=>56,"B"=>56);
$MyData->setPalette("GLY", $color);
$labels = array();
foreach($glycosylation_sites as $k => $v) {
	$label = $k+68;
	if($label > 99)
		$label = str_pad($label-100, 2, "0", STR_PAD_LEFT);
	$labels[] = $label;
}

$MyData->addPoints($labels,"Labels");
$MyData->setSerieDescription("Labels","years");
$MyData->setAbscissa("Labels");



/* Create the pChart object */
$myPicture = new pImage($width,$height,$MyData);

/* Turn of Antialiasing */
$myPicture->Antialias = TRUE;

/* Draw the background */ 
$Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
#$myPicture->drawFilledRectangle(0,0,700,230,$Settings); 

/* Overlay with a gradient */ 
$Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
#$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$Settings); 

/* Add a border to the picture */
#$myPicture->drawRectangle(0,0,369,229,array("R"=>0,"G"=>0,"B"=>0));

/* Write the chart title */ 
$myPicture->setFontProperties(array("FontName"=>"pChart2.1.3/fonts/Forgotte.ttf","FontSize"=>20*$ratio));
$myPicture->drawText(50*$ratio,70,"$main_residue",array("FontSize"=>20*$ratio,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>"pChart2.1.3/fonts/calibri.ttf","FontSize"=>8*$ratio));

/* Define the chart area */
$myPicture->setGraphArea((30*$ratio),0,(925*$ratio),$height-(15*$ratio));

/* Draw the scale */
$AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100),1=>array("Min"=>10,"Max"=>20));
$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>255,"GridG"=>255,"GridB"=>255,"DrawSubTicks"=>FALSE,"CycleBackground"=>FALSE, "Factors"=>array(1,2), "Mode"=>SCALE_MODE_MANUAL, "ManualScale"=>$AxisBoundaries);
$myPicture->drawScale($scaleSettings);

/* Write the chart legend */
#$myPicture->drawLegend(45,35,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;


/* Draw the area chart */
$MyData->setSerieDrawable("GLY",TRUE);
foreach(array_keys($fixations['data_points']) as $k => $v) {
	$MyData->setSerieDrawable($v,FALSE);
}
$myPicture->drawAreaChart();

/* Draw a line and a plot chart on top */
$MyData->setSerieDrawable("GLY",FALSE);
foreach(array_keys($fixations['data_points']) as $k => $v) {
	$MyData->setSerieDrawable($v, TRUE);
}
#$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
$myPicture->drawLineChart();



foreach(array_keys($fixations['data_points']) as $k => $v) {
	$MyData->setSerieWeight($v, $line_styles[$k]['weight']*5);
	$MyData->setSerieShape($v,$line_styles[$k]['shape']);
}
$myPicture->drawPlotChart();
#$myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"PlotSize"=>3,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80));

$myPicture->setFontProperties(array("FontName"=>"pChart2.1.3/fonts/calibri.ttf","FontSize"=>22,"R"=>0,"G"=>0,"B"=>0));
#$myPicture->drawLegend((945*$ratio),(10*$ratio),array("Mode"=>LEGEND_VERTICAL, "BoxSize"=>6,"R"=>255,"G"=>255,"B"=>255,"Surrounding"=>20,"Family"=>LEGEND_FAMILY_LINE));

foreach(array_keys($fixations['data_points']) as $k => $v) {
	$MyData->setSerieWeight($v, $line_styles[$k]['weight']*1);
	$MyData->setSerieShape($v,$line_styles[$k]['shape']);
}
 
/* Write a legend box */ 
$myPicture->setFontProperties(array("FontName"=>"pChart2.1.3/fonts/calibri.ttf","FontSize"=>24));
$myPicture->drawLegend((925*$ratio),(10*$ratio),array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_VERTICAL, "R"=>255,"G"=>255,"B"=>255,"BoxHeight"=>50, "BoxWidth"=>90,"Surrounding"=>20,"Family"=>LEGEND_FAMILY_LINE));
 


/* Render the picture (choose the best way) */
$myPicture->render($file_save_path); 
$myPicture->autoOutput($file_save_path); 
#/www/htdocs/3fx.us/bio/gly/images/graphs
?>