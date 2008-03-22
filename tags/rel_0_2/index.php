<?php
require_once('bootstrap.php');
require_once(colesoLibrarian::getModule('docgen/controller.class.php'));

//============================================================================
$myController=new onlineDocController();
echo $myController->run();
?>