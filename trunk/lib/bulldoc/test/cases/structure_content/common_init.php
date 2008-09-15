<?php
require_once('coleso/phptemplate/phptemplate.php');
require_once('coleso/toolkit/toolkit.php');
require_once ('spyc/spyc.php5');
require_once('bulldoc/build_on_toc.php');
require_once('bulldoc/default_highlight.php');
require_once('bulldoc/theme_manager.php');
require_once('bulldoc/toc_manipulation.php');

//======================================================================================
class structureHolderLoader extends buildOnToc
{
  public function getHolder()
  {
    return $this->structureHolder;
  }
}
?>
