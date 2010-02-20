<?php
//bootstrap sample:

/*
//local_config.ini.php is outside svn!

$loader=new colesoConfigLoader(dirname(__FILE__)),'rus');
$loader->loadCore();
$loader->loadAll(); //Core + DB
*/

//===============================================================================
class colesoConfigLoader
{
  private $localConfigFile;
  private $language;
  private $locale;
  
  function __construct($localConfigFile,$lang='eng')
  {
    $this->localConfigFile=$localConfigFile;
    $this->language=$lang;
  }
//-------------------------------------------
  public function loadAll()
  {
    $this->loadCore();
    $this->setUpDB();
  }
//-------------------------------------------
  public function loadCore()
  {
    require_once($this->localConfigFile);
    $docRoot=dirname(realpath($this->localConfigFile)).'/';
    
    set_include_path(
      get_include_path().PATH_SEPARATOR.
      rtrim($docRoot,'\\/').PATH_SEPARATOR.
      $docRoot.'lib');
    
    require_once('coleso/application/application.php');
    colesoApplication::init();
    date_default_timezone_set('UTC');
    $this->CONFIG=new colesoDataSet($CONFIG);

    colesoApplication::setConfigVal('/system/localConfig',$this->CONFIG);
    $this->setCorePaths($docRoot);
    $this->loadCoreMessages();
    $this->setUpToken();
    $this->setUpLogin();
    $this->setUpError();
  }
//-------------------------------------------
  protected function autodetectRootUrl($docRoot)
  {
    if ($this->CONFIG['urlRoot']) return $this->CONFIG['urlRoot'];
    /*
    $rootUrl=substr($docRoot,strlen($_SERVER['DOCUMENT_ROOT']));
    $rootUrl='/'.ltrim($rootUrl,'/\\');
    return $rootUrl;
    */
    return dirname($_SERVER['PHP_SELF']).'/';
  }
//-------------------------------------------
  protected function setCorePaths($docRoot)
  {
    $urlRoot=str_replace('\\','/',$this->autodetectRootUrl($docRoot));
    colesoApplication::setConfigVal('/system/docRoot',$docRoot);
    colesoApplication::setConfigVal('/system/urlRoot',$urlRoot);
    colesoApplication::setConfigVal('/system/libUrlRoot',$urlRoot.'lib/');

    colesoApplication::setConfigVal('/system/media/upload',$docRoot.'data/media/');
    colesoApplication::setConfigVal('/system/media/url',$urlRoot.'data/media/');
    colesoApplication::setConfigVal('/system/config',$docRoot.'config/');
    colesoApplication::setConfigVal('/system/cacheDir',$docRoot.'cache/');
  }
//-------------------------------------------
  function getLanguage()
  {
    if ($this->CONFIG->get('language')) {
      colesoApplication::setConfigVal('/system/language',$this->CONFIG->get('language'));
    } else {
      colesoApplication::setConfigVal('/system/language',$this->language);
    }
    
    if (isset($this->CONFIG['supportedLanguages'])){
      colesoApplication::setConfigVal('/system/language_list', $this->CONFIG['supportedLanguages']); 
    } else {
      colesoApplication::setConfigVal('/system/language_list', array('rus','utf8_rus','eng')); 
    }
  }
//-------------------------------------------
  protected function loadCoreMessages()
  {
    $this->getLanguage();
    colesoApplication::loadMessages("coleso/messages");
    $this->getLocale();

    colesoApplication::setConfigVal('/system/lngEncoding',$this->locale['encoding']);
    colesoApplication::setConfigVal('/system/localeData',$this->locale);

    if (isset($this->locale['locale_name'])) setlocale(LC_ALL, $this->locale['locale_name']);
  }
//-------------------------------------------
  function getLocale()
  {
    if ($this->CONFIG->get('locale')) $locale=$this->CONFIG->get('locale');
    else $locale=colesoApplication::getMessage('system','default_locale');
    
    $this->locale=parse_ini_file('coleso/locales/'.$locale.'.ini');
  }
//-------------------------------------------
  function setUpToken()
  {
    colesoApplication::setConfigVal('/system/tokenTimeCheck',$this->CONFIG->get('tokenTimeCheck'),false);
    colesoApplication::setConfigVal('/system/tokenTimeCheckInterval',$this->CONFIG->get('tokenTimeCheckInterval'),600);
  
    colesoApplication::setConfigVal('/system/tokenName','colesoToken');
    colesoApplication::setConfigVal('/system/tokenTimeName','colesoTokenTime');
  }
//-------------------------------------------
  function setUpLogin()
  {
    colesoApplication::setConfigVal('/system/loginMode','simple');
    colesoApplication::setConfigVal('/system/loginPasswordType','encrypted');
  }
//-------------------------------------------
  function setUpError()
  {
    colesoApplication::setConfigVal('/system/errorReporting/ErrTemplate','coleso/fatal_error.tpl.phtml');
    
    colesoApplication::setConfigVal('/system/errorReporting/HandleErrors',$this->CONFIG->get('HandleErrors',true));
    colesoApplication::setConfigVal('/system/errorReporting/ReportingLevel',$this->CONFIG->get('ReportingLevel','ALL'));
    colesoApplication::setConfigVal('/system/errorReporting/DisplayErrors',$this->CONFIG->get('DisplayErrors',true));
    colesoApplication::setConfigVal('/system/errorReporting/LogErrors',$this->CONFIG->get('LogErrors',false));
    colesoApplication::setConfigVal('/system/errorReporting/FormatStyle',$this->CONFIG->get('FormatStyle','html'));
    colesoApplication::setConfigVal('/system/errorReporting/Backtrace',$this->CONFIG->get('Backtrace',true));
    
    require_once ('coleso/error/error.php');
    colesoGeneralErrorAssign();
  }
//-------------------------------------------
  function setUpDB()
  {
    colesoApplication::setConfigVal('/system/db/default/dbHost',$this->CONFIG['dbHost']);
    colesoApplication::setConfigVal('/system/db/default/dbLogin',$this->CONFIG['dbLogin']);
    colesoApplication::setConfigVal('/system/db/default/dbPassword',$this->CONFIG['dbPassword']);
    colesoApplication::setConfigVal('/system/db/default/dbBaseName',$this->CONFIG['dbBaseName']);
    colesoApplication::setConfigVal('/system/db/default/DBType',$this->CONFIG['DBType']);
    colesoApplication::setConfigVal('/system/db/default/Encoding',$this->locale['db_encoding']);
    colesoApplication::setConfigVal('/system/db/default/tablePrefix',$this->CONFIG->get('tablePrefix','htp_'));
  }
}
