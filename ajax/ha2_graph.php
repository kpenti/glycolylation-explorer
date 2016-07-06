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

$file_save_path = "/www/htdocs/3fx.us/bio/gly/images/graphs_large_ha2/$main_residue-$gly_residue.png";
if(file_exists($file_save_path) && $size!=1){
	header("Content-Type: image/png");
	echo file_get_contents($file_save_path);
	die();
}

 /* Create and populate the pData object */
 $MyData = new pData(); 
 
 $str = 'http://3fx.us/bio/gly/ajax/get_glycosylation_sites_ha2.php?residue='.$gly_residue;
$glycosylation_sites = file_get_contents($str);
$glycosylation_sites = json_decode($glycosylation_sites, true);
#$glycosylation_sites = [];
#trace($glycosylation_sites);die();


/* Create and populate the pData object */
#$glycosylation_data = new pData();  
#$glycosylation_data->addPoints($glycosylation_sites,"Glycosylation");
 $str = 'http://3fx.us/bio/gly/ajax/get_fixations_ha2.php?residue='.$main_residue;
$fixations = file_get_contents($str);
$fixations = json_decode($fixations, true);
#trace($fixations);die();
$fixations_data = array();
foreach($fixations['data_points'] as $k => $v) {
	$MyData->addPoints($v,$k);
	#$fixations_data[$k]->addPoints($v,$k);
	#trace($v);
}
 #$MyData->addPoints(array(4,2,10,12,8,3),"Probe 1");
 

 $MyData->addPoints($glycosylation_sites,"GLY");
 foreach(array_keys($fixations['data_points']) as $k => $v) {
	$MyData->setSerieTicks($v,1);
	$MyData->setSerieWeight($v,1);
}
 
 $MyData->setSerieTicks("GLY",0);
 $MyData->setAxisName(0,"Percentage");
  $color = array("R"=>255,"G"=>188,"B"=>64);
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
$height = 165;
 /* Create the pChart object */
 $myPicture = new pImage(970,$height,$MyData);

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
 $myPicture->setGraphArea(0,0,970,$height-15);

 /* Draw the scale */
 $AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100),1=>array("Min"=>10,"Max"=>20));
 $scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>255,"GridG"=>255,"GridB"=>255,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE, "Factors"=>array(1,2), "Mode"=>SCALE_MODE_MANUAL, "ManualScale"=>$AxisBoundaries);
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
	 $MyData->setSerieDrawable($v,TRUE);
}
 #$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 $myPicture->drawLineChart();
 #$myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"PlotSize"=>3,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80));

 
  $myPicture->drawLegend(945,10,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_CIRCLE));
 
 
 /* Render the picture (choose the best way) */
 $myPicture->render($file_save_path); 
 $myPicture->autoOutput($file_save_path); 
 #/www/htdocs/3fx.us/bio/gly/images/graphs

?>