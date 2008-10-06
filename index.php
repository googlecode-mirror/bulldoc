<?php
require_once('bootstrap.php');
require_once('bulldoc/controller.php');

//============================================================================
function exception_handler($exception) {
  echo "Sorry, page not found, or error occured";
}
//set_exception_handler('exception_handler');

//============================================================================
umask(0);
$myController=new bulldocFrontController();
$myController->display();
?>