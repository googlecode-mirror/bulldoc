<?php
require_once('bootstrap.php');
require_once('packages/docgen/controller.class.php');

//============================================================================
$myController=new onlineDocController();
echo $myController->run();
?>