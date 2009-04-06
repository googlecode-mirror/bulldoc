<?php
require_once(dirname(__FILE__).'/../utils/debug_tools.php');

abstract class colesoBaseTest extends UnitTestCase
{
  protected $debugMode;
  protected $Environment;
  protected $savedEnvironment;
  protected $savedSystemCachePath;
  protected $cachePath=null;         //mylib/myfile/  will be placed in /cache/test/mylib/myfile
  protected $sourceFilesDir;    //$this->loadDump(dirname(__FILE__).'/fixture/');
  protected $fullCachePath;
  protected $systemPaths;       //actual url part wich could differ depending of developer's computer
                                //we should strip this parts, to obtain invariant part

//----------------------------------------------------------------
  public function __construct($title)
  {
    //$this->cachePath and $sourceFilesDir should be specified inside the child's constructor
    parent::__construct($title);
    $this->fullCachePath=colesoApplication::getConfigVal('/system/cacheDir').'test/'.$this->cachePath;
    $this->savedEnvironment=colesoApplication::getEnvironment();

    if (isset($_GET['mode']) && $_GET['mode']=='debug') {
      $this->debugMode='debug';
      colesoEchoDebugHeader();
    }
  }
//--------------------------------------------------------
  public function setUp()
  {
    $this->Environment=new colesoEnvironment();
    $this->Environment->setMode('test');
    colesoToken::testReset();
    colesoApplication::setEnvironment($this->Environment);
    $this->riseFixture();
    if ($this->cachePath) {
      $this->savedSystemCachePath=colesoApplication::getConfigVal('/system/cacheDir');
      colesoApplication::setConfigVal('/system/cacheDir',$this->fullCachePath);
    }
  }
//--------------------------------------------------------
  public function tearDown()
  {
    colesoApplication::setEnvironment($this->savedEnvironment);
    colesoToken::testReset();
    if ($this->cachePath) {
      colesoApplication::setConfigVal('/system/cacheDir',$this->savedSystemCachePath);
    }
  }
//------------------------------------------------------
  public function riseFixture()
  {
    if (is_null($this->cachePath)) return;
    $targetPath=rtrim($this->fullCachePath,'\\/');
    $sourcePath=rtrim($this->sourceFilesDir,'\\/');

    directoryClear($targetPath);
    directoryCopy($sourcePath,$targetPath);
  }
//-------------------------------------------------------
  protected function showMessage($message,$forceLinefeed=false)
  {
    if ($this->debugMode=='debug') {
      echo $message;
      if ($forceLinefeed) echo '<br/>';
    }
  }
//-------------------------------------------------------
  protected function getContentExaminator($content)
  {
    $examinator=new colesoContentDebug($content,$this->debugMode,$this->systemPaths);
    return $examinator;
  }
}

?>
