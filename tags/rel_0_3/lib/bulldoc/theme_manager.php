<?php
require_once('coleso/theme_manager/theme_manager.php');

class bulldocDecoThemes extends colesoDecoThemes
{
  public function __construct($themeData=array())
  {
    parent::__construct('bulldoc',$themeData);
  }
}
?>
