<? include_once "ajax/inc.php" ?>
<?
//check mysql	
?>
<? include "layouts/header.php" ?>
<?
$body = 'home';
if(isset($_GET['p1']) && !empty($_GET['p1'])) {
	$body = $_GET['p1'];
}
if($body  == 'index') {
	$body = 'home';
}
?>
<? include "contents/$body.php" ?>
<? include "layouts/footer.php" ?>
