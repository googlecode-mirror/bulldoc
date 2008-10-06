<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id$
***********************************************************************************/


class colesoEnvironment
{
  public $Request;
  public $GetRequest;
  public $PostRequest;
  public $Cookie;
  public $method='GET';
  
  private $sessionStarted=false;
  private $testSession;

  private $FileUploaded;
  private $runMode='application';   //application | test

  public $generalVarPool;
  
  public $authorized=false;
  public $userLogin='';
  public $userID=-1;
  public $userGroup=-1;

  public $requestURL;
  
  protected $headers=array();

//------------------------------------------------------------------------
  function __construct()
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
    $this->method=$_SERVER['REQUEST_METHOD'];
  }
//------------------------------------------------------------------------
  function getReqVar($name,$def='')
  {
    return $this->Request->get($name,$def);
  }
//------------------------------------------------------------------------
  function setReqVar($name,$value)
  {
    $this->Request->set($name,$value);
  }
//------------------------------------------------------------------------
  function getGetVar($name,$def='')
  {
    return $this->GetRequest->get($name,$def);
  }
//------------------------------------------------------------------------
  function setGetVar($name,$value)
  {
    $this->GetRequest->set($name,$value);
  }
//------------------------------------------------------------------------
  function getPostVar($name,$def='')
  {
    return $this->PostRequest->get($name,$def);
  }
//------------------------------------------------------------------------
  function setPostVar($name,$value)
  {
    $this->PostRequest->set($name,$value);
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
  function header($header)
  {
    if ($this->runMode=='application'){
      header($header);
    } else {
      $this->headers[]=$header;
    }
  }
//------------------------------------------------------------------------
  function checkRedirect()
  {
    foreach ($headers as $header){
      if (preg_match('/Location:\s+(.*?)/',$header,$matches)) return $matches[1];
    }
    return null;
  }
//------------------------------------------------------------------------
  function redirect($url)
  {
    $this->header('Location: '.$url);
  }
} //Class
?>