<?php
//deprecated!
die('deprecated language control');
//=========================================================================
class colesoLanguageControl
{
  private static $languageList;
  private static $defaultLang='eng';
  private static $currentLang;
  
  private static $dataStack=array();


  private static function init()
  {
    if (isset(self::$languageList)) return;
    if (is_array(colesoApplication::getConfigVal('/system/supportedLanguages'))){
        self::$languageList=colesoApplication::getConfigVal('/system/supportedLanguages');
    } else self::$languageList=array(self::$defaultLang);
    self::$currentLang=self::obtainCurrentLanguage();
  }
//---------------------------------------------------------------------
  public static function push()
  {
    if (isset(self::$languageList)) return;
    $data=array(
      'languageList'=>self::$languageList,
      'currentLang'=>self::$currentLang
      );
    array_push(self::$dataStack,$data);
  }  
//---------------------------------------------------------------------
  public static function pop()
  {
    if (count(self::$dataStack)==0) return; //probably should throw Error
    $data=array_pop(self::$dataStack);
    self::$languageList=$data['languageList'];
    self::$currentLang=$data['currentLang'];
  } 
//---------------------------------------------------------------------
  private static function obtainCurrentLanguage()
  {
    if (colesoApplication::getConfigVal('/system/language')){
      return colesoApplication::getConfigVal('/system/language');
    }
    
    $language=self::$defaultLang;
    $environment=colesoApplication::getEnvironment();
    $url=$environment->getRequestURL();
    foreach (self::$languageList as $language) {
      if (preg_match('/^\/'.$language.'/',$url)) return $language;
    }
    return $language;
  }
//---------------------------------------------------------------------
  public static function getCurrentLanguage()
  {
    self::init();
    return self::$currentLang;
  }
//---------------------------------------------------------------------
  public static function setCurrentLanguage($lang)
  {
    self::init();
    if (!in_array($lang,self::$languageList)) throw new Error($lang.' is not in Language list');
    self::$currentLang=$lang;
  }
//---------------------------------------------------------------------
  public static function getLabels($languageDir,$language=null,$section=null)
  {
    self::init();
    if (is_null($language)) $language=self::getCurrentLanguage();
    $labels=parse_ini_file(rtrim($languageDir,'\\/').'/msg_'.$language.'.ini',true);
    if (!is_null($section)) $labels=$labels[$section];
    return $labels;
  }
}
?>
