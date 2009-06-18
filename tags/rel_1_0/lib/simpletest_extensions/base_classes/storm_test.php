<?php
require_once('lib/simpletest_extensions/utils/debug_tools.php');

//Needs Storm library
abstract class colesoDbTest extends  colesoBaseTest
{
  protected $sqliteDbFileName='db';
  protected $dbConn;

//----------------------------------------------------------------
  public function __construct($title)
  {
    parent::__construct($title);
    $sourceFile=$this->sourceFilesDir.$this->sqliteDbFileName;
    colesoApplication::setConfigVal('/system/db/source/dbType','Pdo_Sqlite');
    colesoApplication::setConfigVal('/system/db/source/dbHost','localhost');
    colesoApplication::setConfigVal('/system/db/source/dbName',$sourceFile);
    //$this->buildTestDB();
  }
//--------------------------------------------------------
  public function setUp()
  {
    parent::setUp();
    $this->registerTestConn();
  }
//--------------------------------------------------------
  public function tearDown()
  {
    parent::tearDown();
    $this->dbConn->closeConnection();
  }
//------------------------------------------------------
  protected function registerTestConn()
  {
    $targetFile=colesoApplication::getConfigVal('/system/cacheDir').'test/'.$this->cachePath.$this->sqliteDbFileName;
    
    colesoApplication::setConfigVal('/system/db/default/dbType','Pdo_Sqlite');
    colesoApplication::setConfigVal('/system/db/default/dbHost','localhost');
    colesoApplication::setConfigVal('/system/db/default/dbName',$targetFile);
    
    $this->dbConn=colesoDB::getConnection();
  }
//----------------------------------------------------------
  protected function loadDump($dumpFile)
  {
    $dbFile=$this->sourceFilesDir.$this->sqliteDbFileName;
    @(unlink($dbFile));
    $dbConn=colesoDB::getConnection('source');
    $loader=new colesoSQLBatch();
    $loader->loadDump($dumpFile, $dbConn);
  }
//-------------------------------------------------------
  protected function getRecordExaminator($sql)
  {
    $examinator=new colesoRecordsetExaminator($sql,$this->debugMode,$this->dbConn);
    return $examinator;
  }
//----------------------------------------------------------
  protected function showRecords($sql,$comment='') 
  {
    if ($this->debugMode=='debug') {
      echo $comment ? $comment.'<br/>' : '';
      echo colesoTestShowReport($sql,$this->dbConn);
    }
  }
//----------------------------------------------------------
  abstract function buildTestDB();
  //$this->loadDump(dirname(__FILE__).'/dbdump.sql');
}

