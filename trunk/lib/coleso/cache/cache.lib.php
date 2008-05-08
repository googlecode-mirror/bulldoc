<?php
require_once (colesoLibrarian::lib_lname("fileoperation"));

class colesoCacheRegistry
{
  var $config;

//------------------------------------------------------------------
  function colesoCacheRegistry($fromInstance='')
  {
    if ($fromInstance=='') colesoErrDie('This is singleton: use getInstance instead of constructor');
    $this->readConfig();
  }
//------------------------------------------------------------------
  static function getInstance($action='get')
  {
    static $instance=false;

    if ($action=='reset') {
      $instance=false;
      return $instance;
    }

    if (!$instance) {
      $instance = new colesoCacheRegistry('instance');
    }
    return $instance;
  }
//--------------------------------
  static function testReset()
  {
    $myVoid=colesoCacheRegistry::getInstance('reset');
  }
//--------------------------------
  function readConfig()
  {
    $configFile=colesoApplication::getConfigVal('/system/config').'cache_registry.inc.php';
    require ($configFile);
    $this->config=$CONFIG;
  }
//---------------------------
  static function clear($keyword)
  {
    $myRegistry = colesoCacheRegistry::getInstance();
    $myRegistry->clearInternal($keyword);
  }
//---------------------------
  function clearInternal($keyword)
  {
    foreach ($this->config as $k=>$v){
      if (in_array($keyword,$v['keywords'])){
        $cacheClass=$v['class'];
        $currentCache=new $cacheClass;
        $currentCache->clearCache();
      }
    }
  }
}

//============================
class colesoCache
{
  var $filePath;
  
  function colesoCache()
  {
    $this->filePath=colesoApplication::getConfigVal('/system/cacheDir');
  }
//----------------------------------
  function getContent($properties)
  {
    $fileName=$this->filePath.$this->buildFileName($properties);
    if (file_exists($fileName)){
      return file_get_contents($fileName);
    } else return NULL;
  }
//----------------------------------
  function setContent($properties,$content)
  {
    $fileName=$this->filePath.$this->buildFileName($properties);
    colesoRecursiveMkdir($this->filePath);
    file_put_contents($fileName,$content);
  }
//----------------------------------
  function buildFileName($properties)
  {
    ksort($properties);
    $fileName='';
    foreach($properties as $k=>$v){
      $fileName.=$k.'_'.$v.'_';
    }
    $fileName=rtrim($fileName,'_').'.txt';
    return $fileName;
  }
//----------------------------------
  function clearCache()
  {
    colesoClearDir($this->filePath);
  }
}
?>
