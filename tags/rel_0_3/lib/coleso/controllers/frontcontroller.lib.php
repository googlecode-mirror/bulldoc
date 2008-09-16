<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id: frontcontroller.lib.php 241 2007-08-28 14:19:21Z hamster $
***********************************************************************************/

require_once ('lib/coleso/controllers/pagecontroller.lib.php');


class colesoFrontController  extends colesoGeneralController
{
  protected $commands;
  protected $outputFilters;
  protected $action;
  protected $controllerArrayKey='controller'; //used when reading commands from commands file
                                              //so you can define different controllers groups in
                                              //one file.
                                              //for front end and back end for instance
  protected $routedPath='/';
  
  protected $params;
  protected $defaultPageName=array('index.html','index.htm','index.php');

  public function __construct()
  {
    parent::__construct();
    $this->commands=array();
    $this->outputFilters=array();
  }
//-----------------------------------------------------
  protected function setup()
  {
    //abtsract function -- should be implemented in real controller
    // We should add controllers
    // We also should add output filters
    
    //$this->addCommand($action,$file,$className,$params=array());
    //$this->readCommandsFromFile($fileName,$arrayName='MENU');
    //$this->addOutputFilter($file,$className,$params=array());
  }
//-----------------------------------------------------
  protected function buildEnvironment()
  {
    //abstract function should be implemented in the real controller
    //should set up $this->action;
    //$this->parameters=array(.....)
  }
//-----------------------------------------------------
  protected function addCommand($action,$file,$className,$params=array())
  {
    $this->commands[$action]=array('file'=>$file,'className'=>$className,'params'=>$params);
  }
//-----------------------------------------------------
  protected function addOutputFilter($file,$className,$params=array())
  {
    $this->outputFilters[]=array('file'=>$file,'className'=>$className,'params'=>$params);
  }
//-----------------------------------------------------
  protected function readCommandsFromFile($fileName,$arrayName='MENU')
  {
    include ($fileName);
    foreach ($$arrayName as $key => $params){
      if (isset($params['link']) || is_string ($params)) continue;
      $data=$params[$this->controllerArrayKey];
      $this->addSingleCommandFromConfig($key,$data);
    }
  }
//-----------------------------------------------------
  protected function addSingleCommandFromConfig($key,$data)
  {
    $this->addCommand($key,$data['commandFile'], $data['class'], $data['params']);
  }
//-----------------------------------------------------
  protected function setArrayControlKey($key)
  {
    $this->controllerArrayKey=$key;
  }
//-----------------------------------------------------
  protected function initModule($module)
  {
    require_once ($module['file']);
    $myCommand=new $module['className'];
    $myCommand->addParameters($module['params']);
    $myCommand->addParameters($this->parameters);
    $myCommand->applicationPath=$this->routedPath;
    $myCommand->callingLevel=$this->callingLevel+1;
    return $myCommand;
  }
//-----------------------------------------------------
  function processOutput($content)
  {
    foreach ($this->outputFilters as $filter){
      $filterObject=$this->initModule($filter);
      $filterObject->addParameters('content',$content);
      $content=$filter->run();
    }
    return $content;
  }
//-----------------------------------------------------
  function runCommand($action)
  {
    if (array_key_exists($action,$this->commands)) {
      $command=$this->initModule($this->commands[$action]);
      return $command->run();
    } else {
      return $this->invalidAction();
    }
  }
//-----------------------------------------------------
  function invalidAction()
  {
      die ('Invalid Action');
  }
//-----------------------------------------------------
  function sendHeaders($result)
  {
    foreach($result->headers as $header) {
      $this->Environment->header($header);
    }
  }
//-----------------------------------------------------
  function run()
  {
    $this->buildEnvironment();
    $this->setup();
    if ($this->action=='_no_controller_') $coomandResult=new colesoControllerExecResult();
    else $coomandResult=$this->runCommand($this->action);
    
    $coomandResult->content=$this->processOutput($coomandResult->content);
    return $coomandResult;
  }
//-----------------------------------------------------
  function display()
  {
    $coomandResult=$this->run();
    $this->sendHeaders($coomandResult);
    print $coomandResult->content;
    exit;
  }
//-----------------------------------------------------
  function getRoutingPath()
  {
    $rootURL=rtrim(colesoApplication::getConfigVal('/system/urlRoot'),'\\/');
    $currentURL=$this->Environment->getReqVar('colesoRequestPath');
    $trimmedURL=str_replace($rootURL,'',$currentURL); //only needed if mod_rewrite is not used
    $trimmedURL=str_replace($this->defaultPageName,'',$trimmedURL);

    $trimmedURL=ltrim($trimmedURL,'\\/');
    $URL='/'.$trimmedURL;
    return $URL;
  }
} //class


//=============================================================
class colesoUserLoginManager
{
  var $loginControl;
  var $loginDataObject;
  var $Environment;
  //-----------------------------------------------------
  function colesoUserLoginManager($authClass)
  {
    $this->Environment=colesoApplication::getEnvironment();
    $this->loginControl=new $authClass;
    $loginDataStructure=array(
                          'tablename' => 'pseudo_table',
                          'fields'=>array(
                              'id'=> array('datatype'=>'int'),
                              'login'=> array('datatype'=>'string'),
                              'passwd'=> array('datatype'=>'string')
                          ),
                          'notEmptyList' =>array(),
                          'idField'=>'id'
                        );
    colesoSchemaConfigPool::setConfig('/sys/login',$loginDataStructure);
    $this->loginDataObject= new colesoDomainObject('/sys/login');
    $this->Environment->runtimeObjectPool['sessionHandler']=& $this->loginControl;
  }
  //-----------------------------------------------------
  function performAuth()
  {
    $this->loginDataObject->getValidatedFieldsFromEnv();
    $login=$this->loginDataObject->getField('login');
    $passwd=$this->loginDataObject->getField('passwd');
    $isAllreadyLogged=$this->loginControl->manage_session();
    $this->Environment->authorized=$isAllreadyLogged || $this->loginControl->performLogin ($login,$passwd);
  }
  //-----------------------------------------------------
  function loginProcess()
  {
    $this->performAuth();
    if ($this->Environment->authorized){
      $this->Environment->userLogin=$this->loginControl->login;
      $this->Environment->userID=$this->loginControl->uid;
      $this->Environment->runtimeObjectPool['loginControlObject']=& $this->loginControl; //obsolete ref
      return true;
    } else {
      if ($this->loginDataObject->getField('login')) {
        $this->loginDataObject->notes->set('message','Неправильный логин или пароль');
      }
      $this->Environment->runtimeObjectPool['loginObject']=& $this->loginDataObject; //obsolete ref
      return false;
    }
  }
}
?>