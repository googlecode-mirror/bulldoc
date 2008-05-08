<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id: environment.lib.php 207 2007-05-28 16:01:27Z hamster $
***********************************************************************************/


class colesoEnvironment
{
  var $Request;
  var $GetRequest;
  var $PostRequest;
  var $Cookie;
  var $sessionStarted=false;
  var $testSession;

  var $FileUploaded;
  var $runMode='application';   //application | test

  var $runtimeObjectPool; //an array contains link to the runtime objects such as forms
  var $generalVarPool;
  var $action='';          //current event for the front controller
  var $authorized=false;
  var $userLogin='';
  var $userID=-1;
  var $userGroup=-1;

  var $requestURL;
  
  var $redirectURL='';

//------------------------------------------------------------------------
function colesoEnvironment()
{
    $this->Request=new colesoDataSet($_REQUEST);
    $this->PostRequest=new colesoDataSet($_POST);
    $this->GetRequest=new colesoDataSet($_GET);
    $this->FileUpload=new colesoDataSet($_FILES);
    $this->Cookie=new colesoDataSet($_COOKIE);
    $this->testSession=new colesoDataSet();
    $this->runtimeObjectPool=array();
    $this->generalVarPool=new colesoDataSet;
    $this->requestURL=$_SERVER["REQUEST_URI"];
}
//------------------------------------------------------------------------
function getReqVar($name,$def='')
{
  return $this->Request->get($name,$def);
}
//------------------------------------------------------------------------
function setReqVar($name,$def='')
{
  $this->Request->set($name,$def);
}
//------------------------------------------------------------------------
function setMode($mode)
{
  $this->runMode=$mode;
  if ($this->runMode=='test'){
    //reset data
    $this->Request=new colesoDataSet();
    $this->PostRequest=new colesoDataSet();
    $this->GetRequest=new colesoDataSet();
    $this->FileUpload=new colesoDataSet();
    $this->Cookie=new colesoDataSet();
    
    $tokenKey=colesoApplication::getConfigVal('/system/tokenName');
    $tokenTimeKey=colesoApplication::getConfigVal('/system/tokenTimeName');
    $this->testSession->set($tokenKey,'test_env_token');
    $this->testSession->set($tokenTimeKey,time());
  }
}
//------------------------------------------------------------------------
function setTestTokenReq()
{
  $tokenKey=colesoApplication::getConfigVal('/system/tokenName');
  $this->setReqVar($tokenKey,'test_env_token');
}
//------------------------------------------------------------------------
function moveUploadedFile($fromLoc,$toLoc)
{
  if ($this->runMode=='application'){
    return move_uploaded_file($fromLoc, $toLoc);
  } else {
    return rename($fromLoc, $toLoc);
  }
}
//------------------------------------------------------------------------
function getRequestURL()
{
  return $this->requestURL;
}
//------------------------------------------------------------------------
  function getCookieVar($cookieName)
  {
    return $this->Cookie->get($cookieName);
  }
//------------------------------------------------------------------------
  function setCookieVar($cookieName,$value,$timePeriod=3600)
  {
    $this->Cookie->set($cookieName,$value);
    if ($this->runMode=='application'){
      setcookie($cookieName,$value,time() + $timePeriod);
    } else {
      //nop
    }
  }
//------------------------------------------------------------------------
  function touchSession()
  {
    if (!$this->sessionStarted){
      session_start ();
      $this->sessionStarted=true;
    }
  }
//------------------------------------------------------------------------
  function setSessionVar($key,$val)
  {
    if ($this->runMode=='application'){
      $this->touchSession();
      $_SESSION[$key] = $val;
    } else {
      $this->testSession->set($key,$val);
    }
  }
//------------------------------------------------------------------------
  function getSessionVar($key)
  {
    if ($this->runMode=='application'){
      $this->touchSession();
      return isset($_SESSION[$key])? $_SESSION[$key]:'';
    } else {
      return $this->testSession->get($key);
    }
  }
//------------------------------------------------------------------------
  function redirect($url)
  {
    $this->redirectURL=$url;
    if ($this->runMode=='application') {
      header("Location: ".$url);
      exit();
    }
  }
} //Class
?>