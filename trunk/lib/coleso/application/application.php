<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id$
***********************************************************************************/
require_once ('coleso/dataset/dataset.php');
require_once ('coleso/environment/environment.php');

class colesoApplication {
  private static $dataStack=array();
  
  private static $Environment=NULL;
//  private static $Messages;    //Sys message array
  public static $Messages;    //Sys message array
  private static $configVar;     //Application Config var

//-----------------------------------------------------------
  public static function init()
  {
    self::$configVar=new colesoDataSet();
    colesoFormControl::initFormVal();
  }  
//-----------------------------------------------------------
  public static function push()
  {
    $data=array(
      'Environment'=>self::$Environment,
      'SysMessage'=>self::$SysMessages,
      'configVar'=>self::$configVar
      );
    array_push(self::$dataStack,$data);
  }  
//-----------------------------------------------------------
  public static function pop()
  {
    $data=array_pop(self::$dataStack);
    self::$Environment=$data['Environment'];
    self::$Messages=$data['SysMessage'];
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
  static function getMessageSection($section)
  {
    return isset(self::$Messages[$section])? self::$Messages[$section]:null;
  }
//-----------------------------------------------------------
  static function getMessage($section,$key)
  {
    return isset(self::$Messages[$section][$key])? self::$Messages[$section][$key]:'';
  }
//-----------------------------------------------------------
  static function setMessage($section,$key,$text)
  {
    self::$Messages[$section][$key]=$text;
  }
//------------------------------------------------------------------------------
  static function loadMessages($msg_path,$languageIgnore=false)
  {
    if (!$languageIgnore) {
      $msg_file=rtrim($msg_path,'\\/').'/'.colesoApplication::getConfigVal('/system/language').'_msg.ini';
    } else $msg_file=$msg_path;
    $messages=parse_ini_file($msg_file, true);
    foreach($messages as  $section=>$set){
      foreach ($set as $k=>$v) self::$Messages[$section][$k]=$v;
    }
  }
  
//-------------------------------------------
  static function setLanguage($language,$locale=null)
  {
    colesoApplication::setConfigVal('/system/language',$language);
    colesoApplication::loadMessages("coleso/messages");
    if (is_null($locale)) $locale=colesoApplication::getMessage('system','default_locale');
    $localeData=parse_ini_file('coleso/locales/'.$locale.'.ini');
    colesoApplication::setConfigVal('/system/lngEncoding',$localeData['encoding']);
    colesoApplication::setConfigVal('/system/localeData',$localeData);
    if (isset($localeData['locale_name'])) setlocale(LC_ALL, $localeData['locale_name']);
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