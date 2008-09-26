<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id: pagecontroller.lib.php 201 2007-05-24 12:01:58Z hamster $
***********************************************************************************/



//===========================================================================
class colesoControllerExecResult
{
  public $headers;
  public $content;
  public $cookies=array();

  function __construct($content='',$headers=array())
  {
    $this->headers=$headers;
    $this->content=$content;
  }
}
//===========================================================================
class colesoControllerRedirect extends colesoControllerExecResult
{
  function __construct($redirect)
  {
    $this->headers=array('Location: '.$redirect);
    $this->content='';
  }
}
//===========================================================================
class colesoGeneralController
{
  protected $Environment;
  protected $parameters;
  protected $callingLevel=0;
  protected $applicationPath='/';

  function __construct()
  {
    $this->Environment=colesoApplication::getEnvironment();
    $this->parameters=new colesoDataSet;
  }
//------------------------------------------------
  function addParameters($parameters=NULL)
  {
    $this->parameters->addArray($parameters);
  }
//------------------------------------------------
  function run()
  {
    //should return the colesoControllerExecResult object!
  }
} 
?>