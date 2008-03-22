<?php
require_once(colesoLibrarian::lib_lname('fileoperation'));

/*
package_config.inc.php'
Конфиг Пакета
/dump
package_config.inc.php
$config=array(
  'keyword'=>'...',
  'name'=>'....',
  'hideFromList',
  'tables'=>array(...)
  //probably some action install here
  );
*/

//=============================================================================
class scaffoldingPackagesFinder extends colesoFileDirectoryProcessor
{
  var $packages;

  function scaffoldingPackagesFinder()
  {
    $this->packages=array();
  }
//------------------------------------------------
  function processFile($fileName)
  {
    if(basename($fileName)=='package_config.inc.php'){
      $this->readController($fileName);
    }
  }
//------------------------------------------------
  function readController($fileName)
  {
    $prefix=colesoApplication::getConfigVal('/system/db/tablePrefix');
    require($fileName);
    $this->packages[$CONFIG['keyword']]=array(
      'title'=>$CONFIG['name'],
      'keyword'=>$CONFIG['keyword'],
      'tables'=>$CONFIG['tables'],
      'file'=>$fileName,
      'dump'=>dirname($fileName).'/dump.sql'      
      );
  }
//------------------------------------------------
  function preProcessDir($dirName) 
  {
    $localName=basename($dirName);
    if ($localName=='.svn' || $localName=='deco' || $localName=='test' || $localName=='web') return false;
    return true;
  }
//------------------------------------------------
  function postProcessDir($dirName){}
}

?>
