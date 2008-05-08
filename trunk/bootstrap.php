<?php
$docRoot=realpath(dirname(__FILE__)).'/';
$urlRoot=rtrim(dirname($_SERVER['SCRIPT_NAME']),'\\/').'/';

set_include_path($docRoot);
require_once('lib/coleso_lib/application/application.lib.php');
colesoApplication::init();
colesoApplication::setConfigVal('/system/docRoot',$docRoot);
colesoApplication::setConfigVal('/system/libDir',$docRoot.'lib/coleso_lib/');
colesoApplication::setConfigVal('/system/extLibs',$docRoot.'lib/');
colesoApplication::setConfigVal('/system/urlRoot',$urlRoot);
colesoApplication::setConfigVal('/system/moduleDir',$docRoot.'packages/');
colesoApplication::setConfigVal('/system/moduleUrl',$urlRoot.'packages/');
colesoApplication::setConfigVal('/system/config',$docRoot.'config/');
colesoApplication::setConfigVal('/system/cacheDir',$docRoot.'cache/');
date_default_timezone_set('UTC');  

colesoApplication::setConfigVal('/system/errorReporting/HandleErrors',true);
colesoApplication::setConfigVal('/system/errorReporting/ReportingLevel','ALL');
colesoApplication::setConfigVal('/system/errorReporting/DisplayErrors',true);
colesoApplication::setConfigVal('/system/errorReporting/LogErrors',false);
colesoApplication::setConfigVal('/system/errorReporting/FormatStyle','html');
colesoApplication::setConfigVal('/system/errorReporting/Backtrace',true);
require_once (colesoLibrarian::lib_lname("error"));
colesoGeneralErrorAssign();

colesoApplication::setConfigVal('/docgen/useStandaloneTheme',true);
colesoApplication::loadSysMessages($docRoot.'messages/docgen.msg.ini');


require_once('config/docgen_config.ini.php');
require_once('packages/docgen/exceptions.php');
require_once('packages/docgen/page_builder.class.php');
require_once('packages/docgen/book_loader.class.php');

//=================================================================================
function detectAbsolutePath($path)
{
  if (preg_match('/^[a-zA-Z]:(\\\\|\/)/',$path)) return true;
  if (preg_match('/^\//',$path)) return true;
  return false;
}
?>
