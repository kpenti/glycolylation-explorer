<?
ini_set('memory_limit','1600000M');
ini_set('max_execution_time', 300); 
/*
for each position get neighbours
for each neighbour get fixations
*/

//db connection
ini_set('include_path',
ini_get('include_path') . PATH_SEPARATOR  . '/www/htdocs/3fx.us/ZendFramework-1.11.11-minimal/library');
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();


require_once 'Zend/Db.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Mail.php';

try {
	$db = Zend_Db::factory('Pdo_Mysql', array(
		'host' =>'localhost',
		'username' =>'root',
		'password' =>'',
		'dbname' =>'antigens_update'
	));
	$db->getConnection();
} catch (Zend_Db_Adapter_Exception $e) {
	mail('snakebiter@gmail.com', '3FX.US MYSQL DOWN', 'service mysqld restart');
	die("Zend_Db_Adapter_Exception: ".$e->getMessage());
} catch (Zend_Exception $e) {
	die("Zend_Exception".$e->getMessage());
}
Zend_Registry::set('db', $db);

if (!function_exists('trace')) {
	function trace($object, $exit = false) {

			echo "<pre>";
			if($object) {
				print_r($object);
			} else {
				var_dump($object);
			}
			echo "</pre>";
			if($exit) {
				exit();
			}

	}
}
?>