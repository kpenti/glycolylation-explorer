<?
include "inc.php";
/*header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');/*/
error_reporting(0);

include("/www/htdocs/3fx.us/bio/libs/pChart2.1.3/class/pData.class.php");
include("/www/htdocs/3fx.us/bio/libs/pChart2.1.3/class/pDraw.class.php");
include("/www/htdocs/3fx.us/bio/libs/pChart2.1.3/class/pImage.class.php");

$main_residue = (int) $_GET['residue'];
$gly_residue = (int) $_GET['gly'];
$size = (int) $_GET['size'];
if(!$main_residue && !$gly_residue) {
	die();	
}

$file_save_path = "/www/htdocs/3fx.us/bio/gly/images/graphs/$main_residue-$gly_residue.h1n1.png";
/*if($size)
	$file_save_path = "/www/htdocs/3fx.us/bio/gly/images/graphs_large/$main_residue-$gly_residue.h1n1.png";
if(file_exists($file_save_path) && $size!=1){
	header("Content-Type: image/png");
	echo file_get_contents($file_save_path);
	die();
}
*/
/* Create and populate the pData object */
$MyData = new pData();
$glycosylation_sites = [];


/* Create and populate the pData object */
$str = 'http://3fx.us/bio/gly/ajax/h1n1.php?residue='.$main_residue;
$fixations = file_get_contents($str);
$fixations = json_decode($fixations, true);
/*echo "<pre>";
var_dump($fixations['data_points']);
die();*/

$fixations_data = array();
foreach($fixations['data_points'] as $k => $v) {
	$MyData->addPoints($v,$k);
}
 
#$MyData->addPoints($glycosylation_sites,"GLY");
foreach(array_keys($fixations['data_points']) as $k => $v) {
	$MyData->setSerieTicks($v,1);
	$MyData->setSerieWeight($v,1);
	$rand = $k;
}
 
 #$MyData->setSerieTicks("GLY",0);
  $color = array("R"=>255,"G"=>188,"B"=>64);
 $labels = array();
 foreach($fixations['data_points'][$rand] as $k => $v) {
	#$labels[] = $k+17;
 }
 for($i = 1918; $i < 2015; $i++) {
	$labels[] = $i;
 }
 #print_r($labels);die();
 $MyData->addPoints($labels,"Labels");
 $MyData->setSerieDescription("Labels","years");
 $MyData->setAbscissa("Labels");
$height = 150;
 /* Create the pChart object */
 $myPicture = new pImage(370,$height,$MyData);

 /* Turn of Antialiasing */
 $myPicture->Antialias = FALSE;

 /* Draw the background */ 
 $Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
 #$myPicture->drawFilledRectangle(0,0,700,230,$Settings); 

 /* Overlay with a gradient */ 
 $Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
 #$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$Settings); 
 
 /* Add a border to the picture */
 #$myPicture->drawRectangle(0,0,369,229,array("R"=>0,"G"=>0,"B"=>0));
 
 /* Write the chart title */ 
 $myPicture->setFontProperties(array("FontName"=>"/www/htdocs/3fx.us/bio/libs/pChart2.1.3/fonts/Forgotte.ttf","FontSize"=>11));
 $myPicture->drawText(20,35,"$main_residue",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 $myPicture->setFontProperties(array("FontName"=>"/www/htdocs/3fx.us/bio/libs/pChart2.1.3/fonts/pf_arma_five.ttf","FontSize"=>6));

 /* Define the chart area */
 $myPicture->setGraphArea(0,0,370,$height);

 /* Draw the scale */
 $AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100),1=>array("Min"=>10,"Max"=>20));
 $scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>255,"GridG"=>255,"GridB"=>255,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE, "Factors"=>array(1,2), "Mode"=>SCALE_MODE_MANUAL, "ManualScale"=>$AxisBoundaries);
 $myPicture->drawScale($scaleSettings);

 /* Write the chart legend */
 #$myPicture->drawLegend(45,35,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Turn on Antialiasing */
 $myPicture->Antialias = TRUE;


 /* Draw the area chart */
 #$MyData->setSerieDrawable("GLY",TRUE);
  foreach(array_keys($fixations['data_points']) as $k => $v) {
	 $MyData->setSerieDrawable($v,FALSE);
}
 $myPicture->drawAreaChart();

 /* Draw a line and a plot chart on top */
 #$MyData->setSerieDrawable("GLY",FALSE);
  foreach(array_keys($fixations['data_points']) as $k => $v) {
	 $MyData->setSerieDrawable($v,TRUE);
}
 #$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 $myPicture->drawLineChart();
 #$myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"PlotSize"=>3,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80));

 /* Render the picture (choose the best way) */
 $myPicture->render($file_save_path); 
 $myPicture->autoOutput($file_save_path); 
 #/www/htdocs/3fx.us/bio/gly/images/graphs
 die();

$str = 'http://3fx.us/bio/gly/ajax/get_glycosylation_sites.php?residue=246';
$glycosylation_sites = file_get_contents($str);
$glycosylation_sites = json_decode($glycosylation_sites, true);
#trace($glycosylation_sites);


/* Create and populate the pData object */
$glycosylation_data = new pData();  
$glycosylation_data->addPoints($glycosylation_sites,"Glycosylation");
/*foreach($glycosylation_sites as $k => $v){
	$glycosylation_data->addPoints($glycosylation_sites,"Glycosylation");
}*/


$str = 'http://3fx.us/bio/gly/ajax/get_fixations.php?residue=124';
$fixations = file_get_contents($str);
$fixations = json_decode($fixations, true);

$fixations_data = array();
foreach($fixations['data_points'] as $k => $v) {
	$glycosylation_data->addPoints($v,"Series ".$k);
	#$fixations_data[$k]->addPoints($v,$k);
	#trace($v);
}
$glycosylation_data->setSerieTicks("Glycosylation",4);
$glycosylation_data->setAxisName(0,"Percentage");
$color = array("R"=>255,"G"=>188,"B"=>64);
$glycosylation_data->setPalette("Serie1", $color);
/*
foreach(array_keys($fixations['data_points']) as $key) {
	$fixations_data[$k]->setSerieTicks("Probe 2",4);
	$fixations_data[$k]->setAxisName(0,"Percentage");
	$color = array("R"=>0,"G"=>255,"B"=>255);
	$fixations_data[$k]->setPalette("Serie1", $color);
}*/
//die();
 

/*
for($i=0;$i<=30;$i++) {
	$MyData->addPoints(rand(1,15),"Probe 1");
}*/

/* Create the pChart object */
$myPicture = new pImage(700,230,$glycosylation_data);

/* Turn of Antialiasing */
$myPicture->Antialias = FALSE;

/* Add a border to the picture */
/*$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100));
$myPicture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20));*/

/* Add a border to the picture */
$myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

/* Write the chart title */ 
$myPicture->setFontProperties(array("FontName"=>"/www/htdocs/3fx.us/bio/libs/pChart2.1.3/fonts/Forgotte.ttf","FontSize"=>11));
#$myPicture->drawText(150,35,"Average temperature",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>"/www/htdocs/3fx.us/bio/libs/pChart2.1.3/fonts/pf_arma_five.ttf","FontSize"=>6));

/* Define the chart area */
$myPicture->setGraphArea(60,40,650,200);

/* Draw the scale */
$scaleSettings = array("XMargin"=>0,"YMargin"=>0,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"GridAlpha"=>100,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
$myPicture->drawScale($scaleSettings);

/* Write the chart legend */
$myPicture->drawLegend(640,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Enable shadow computing */
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Draw the area chart */
$Threshold = "";
$Threshold[] = array("Min"=>0,"Max"=>80,"R"=>255,"G"=>188,"B"=>64,"Alpha"=>100);
$Threshold[] = array("Min"=>80,"Max"=>100,"R"=>255,"G"=>188,"B"=>64,"Alpha"=>100);
#$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));
#$myPicture->drawAreaChart(array("Threshold"=>$Threshold));

/* Draw a line chart over */
$myPicture->drawLineChart(array("ForceColor"=>TRUE,"ForceR"=>0,"ForceG"=>0,"ForceB"=>0));

/* Draw a plot chart over */
#$myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-255,"BorderAlpha"=>80));

/* Write the thresholds */
$myPicture->drawThreshold(80,array("WriteCaption"=>TRUE,"Caption"=>"80%","Alpha"=>70,"Ticks"=>2,"R"=>0,"G"=>0,"B"=>255));

/* Render the picture (choose the best way) */
$myPicture->autoOutput("example.drawAreaChart.threshold.png"); 

//draw chart for residue 246

//glycosylation

//percentage of strains

?>