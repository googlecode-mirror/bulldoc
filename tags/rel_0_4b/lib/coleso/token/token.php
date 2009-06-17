<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id$
***********************************************************************************/

class colesoToken
{
  var $Environment;
  var $tokenKey;
  var $tokenTimeKey;

//------------------------------------------------------------------
  function colesoToken($fromInstance='')
  {
    if ($fromInstance=='') colesoErrDie('This is singleton: use getInstance instead of constructor');
    $this->Environment= colesoApplication::getEnvironment();
    $this->tokenKey=colesoApplication::getConfigVal('/system/tokenName','colesoToken');
    $this->tokenTimeKey=colesoApplication::getConfigVal('/system/tokenTimeName','colesoTokenTime');
  }
//------------------------------------------------------------------
  static function & getInstance($action='get')
  {
    static $instance=false;
    
    if ($action=='reset') {
      $instance=false;
      return $instance;
    }
    if (!$instance) {
      $instance = new colesoToken('instance');
    }
    return $instance;
  }
//------------------------------------------------------------------
  function instGetToken()
  {
    $token=$this->Environment->getSessionVar($this->tokenKey);
    if (!$token) {
      $token = md5(uniqid(rand(), TRUE));
      $this->Environment->setSessionVar($this->tokenKey,$token);
      $this->Environment->setSessionVar($this->tokenTimeKey,time());
    }
    return $token;
  }
//--------------------------------
  function instCheckValid()
  {
    $reqToken=$this->Environment->getReqVar($this->tokenKey);
    $sessToken=$this->Environment->getSessionVar($this->tokenKey);
    $tokenTime=$this->Environment->getSessionVar($this->tokenTimeKey);
    if (colesoApplication::getConfigVal('/system/tokenTimeCheck') 
        && (time()>$tokenTime+colesoApplication::getConfigVal('/system/tokenTimeCheckInterval')))
        return false;
    
    return $reqToken==$sessToken;
  }
//------------------------------------------------------------------
  static function getToken()
  {
    $myTokenControl=& colesoToken::getInstance();
    return $myTokenControl->instGetToken();
  }
//--------------------------------
  static function checkValid()
  {
    $myTokenControl=& colesoToken::getInstance();
    return $myTokenControl->instCheckValid();
  }
//--------------------------------
  static function getTokenKey()
  {
    return colesoApplication::getConfigVal('/system/tokenName','colesoToken');
  }
//--------------------------------
  static function testReset()
  {
    $myVoid=& colesoToken::getInstance('reset');
  }
}
?>
