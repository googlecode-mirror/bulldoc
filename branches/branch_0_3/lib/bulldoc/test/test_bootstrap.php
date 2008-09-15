<?php
require_once(dirname(__FILE__).'/../../coleso/config_loader/config_loader.php');
$loader=new colesoConfigLoader(dirname(__FILE__).'/../../../local_config.inc.php','rus');
$loader->loadCore();
require_once('bulldoc/package_bootstrap.inc.php');
require_once(dirname(__FILE__).'/../../simpletest_extensions/bootstrap.php');
?>
