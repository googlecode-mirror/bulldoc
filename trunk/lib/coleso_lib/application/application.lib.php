<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id: application.lib.php 242 2007-09-11 14:33:22Z hamster $
***********************************************************************************/
require_once (realpath(dirname(__FILE__).'/..').'/dataset/dataset.lib.php');
require_once (realpath(dirname(__FILE__).'/..').'/environment/environment.lib.php');

class colesoApplication {
  private static $dataStack=array();
  
  private static $Environment=NULL;
  private static $SysMessage;    //Sys message array
  private static $configVar;     //Application Config var

//-----------------------------------------------------------
  public static function init()
  {
    self::$configVar=new colesoDataSet();
	  self::$SysMessage=parse_ini_file(dirname(__FILE__).'/msg_main.msg.ini',true);
    colesoFormControl::initFormVal();
  }  
//-----------------------------------------------------------
  public static function push()
  {
    $data=array(
      'Environment'=>self::$Environment,
      'SysMessage'=>self::$SysMessage,
      'configVar'=>self::$configVar
      );
    array_push(self::$dataStack,$data);
  }  
//-----------------------------------------------------------
  public static function pop()
  {
    $data=array_pop(self::$dataStack);
    self::$Environment=$data['Environment'];
    self::$SysMessage=$data['SysMessage'];
    self::$configVar=$data['configVar'];
  }  
//-----------------------------------------------------------
  static function setEnvironment($Env)
  {
    self::$Environment=$Env;
  }
//-----------------------------------------------------------
  static function getEnvironment()
  {
    if (!self::$Environment) self::setEnvironment(new colesoEnvironment());
    return self::$Environment;
  }
//-----------------------------------------------------------
  static function getSysMessage($section,$key)
  {
    return self::$SysMessage[$section][$key];
  }
//-----------------------------------------------------------
  static function setSysMessage($section,$key,$text)
  {
    self::$SysMessage[$section][$key]=$text;
  }
//------------------------------------------------------------------------------
  static function loadSysMessages($msg_file)
  {
    $messages=parse_ini_file($msg_file,true);
    foreach($messages as  $section=>$set){
      foreach ($set as $k=>$v) self::$SysMessage[$section][$k]=$v;
    }
  }
//------------------------------------------------------------------------------
  static function loadConfigFile($fileName)
  {
    include ($fileName);
    foreach ($CONFIG as $k=>$v) {
      self::$configVar->set($k,$v);
    }
  }
//------------------------------------------------------------------------------
  static function setConfigVal($name,$val)
  {
    self::$configVar->set($name,$val);
  }
//------------------------------------------------------------------------------
  static function getConfigVal($name)
  {
    return self::$configVar->get($name);
  }
//-----------------------------------------------------------------------------
} //Class

//=================================================
function colesoForvard404()
{
  $myEnv=colesoApplication::getEnvironment();
  $myEnv->generalVarPool->set('/system/not_found',true);
}

//=================================================
class colesoLibrarian
{
  static function getDocRoot ()
	{
  	return colesoApplication::getConfigVal('/system/docRoot');
	}
  //- - - - - - - - - - - - - - - - - - -
  static function lib_lname ($libname)
	{
  	if (preg_match("/\/?(\w+)$/",$libname,$matches)==0) die ('no such a library');
	  $libf=$matches[1];
  	return colesoApplication::getConfigVal('/system/libDir').$libname.'/'.$libf.'.lib.php';
	}
//- - - - - - - - - - - - - - - - - - -
  static function lib_fname ($libname,$filename)
	{
  	return colesoApplication::getConfigVal('/system/libDir').$libname.'/'.$filename;
	}
//- - - - - - - - - - - - - - - - - - -
  static function get_deco_file($deco_file)
	{
	  return colesoApplication::getConfigVal('/system/decoDir').$deco_file;
	}
//- - - - - - - - - - - - - - - - - - -
  static function getDecoDir()
	{
	  $myApp=colesoApplication::getInstance();
	  return colesoApplication::getConfigVal('/system/decoDir');
	}
//- - - - - - - - - - - - - - - - - - -
  static function get_msg_main()
	{
	  colesoApplication::loadSysMessages(colesoApplication::getConfigVal('/system/msg_dir').
                                      colesoApplication::getConfigVal('/system/main_msg_file'));
	}
//- - - - - - - - - - - - - - - - - - -
  static function getExternalLib($libPath)
  {
    return colesoApplication::getConfigVal('/system/extLibs').$libPath;
  }
//- - - - - - - - - - - - - - - - - - -
  static function getModule($module='')
  {
    return colesoApplication::getConfigVal('/system/moduleDir').$module;
  }
} //Librarian Class


//========================================================
//========================================================
//========================================================
class colesoFormControl
{
  static function registerForm($name,$htmlOut)
  {
    colesoApplication::setConfigVal('/form/regFormList/'.$name,$htmlOut);
  }
  //---------------------------------------------------------------------------
  static function getRegForm($name)
  {
    return colesoApplication::getConfigVal('/form/regFormList/'.$name);
  }
  //---------------------------------------------------------------------------
  static function initFormVal()
  {
    colesoFormControl::formSetYearInterval(2004,2008);
    colesoFormControl::formSetDayInterval();
    colesoFormControl::formSetMonthInterval(array('','Jan','Feb','Mar','Apr','May','Jun',
                                                          'Jul','Aug','Sep','Oct','Nov','Dec'));
    colesoApplication::setConfigVal('/form/FormDateOrder','dmy');
  }
  //............................................................................
  static function formSetMonthInterval($names)
  {
    $monthsHash=array();
    foreach ($names as $k) $daysHash[]=$k;
    colesoApplication::setConfigVal('/form/monthList',$daysHash);
  }
  //............................................................................
  static function formSetDayInterval()
  {
    $daysRange=range(1,31);
    $daysHash=array(0=>'');
    foreach ($daysRange as $k) $daysHash[$k]=$k;
    colesoApplication::setConfigVal('/form/dayList',$daysHash);
  }
  //............................................................................
  static function formGetYearHash($yfrom,$yto)
  {
    $yearsRange=range($yfrom,$yto);
    $yearsHash=new colesoDataset(array('0000'=>''));
    foreach ($yearsRange as $k) $yearsHash[(string) $k]=$k;
    return $yearsHash;
  }
  //............................................................................
  static function formSetYearInterval($yfrom,$yto)
  {
    colesoApplication::setConfigVal('/form/yearList',colesoFormControl::formGetYearHash($yfrom,$yto));
  }
  
  //............................................................................
  static function formSetDateOrder($order)
  {
    colesoApplication::setConfigVal('/form/FormDateOrder',$order);
  }
  //............................................................................
  static function formGetDateOrder()
  {
    return colesoApplication::getConfigVal('/form/FormDateOrder');
  }
}
?>