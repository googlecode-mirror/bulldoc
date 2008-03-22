<?php
class colesoError
{
  var $logEngine;

  function colesoError($logEngine=null)
  {
    if ($logEngine) {
      $this->logEngine=& $logEngine;
    } else {
      $this->logEngine= new colesoErrorLog;
    }
  }
//-----------------------------------------------------------------------
  static function trace()
  {
    //by bernyregeling AT hotmail DOT com
    $out='';
    if(!function_exists('debug_backtrace')){
        $out.='function debug_backtrace does not exists'."\r\n";
    }
    $out.="\r\n".'----------------'."\r\n";
    $out.='Debug backtrace:'."\r\n";
    $out.='----------------'."\r\n";
    $traceArray=debug_backtrace();
    $traceArray=array_reverse($traceArray);
    foreach($traceArray as $t){
      $out.="\t" . '@ ';
      if(isset($t['file'])) $out.=basename($t['file']) . ':' . $t['line'];
      $out.=' -- ';
      if(isset($t['class'])) $out.=$t['class'] . $t['type'];
      $out.=$t['function'];
      if(isset($t['args']) && sizeof($t['args']) > 0) $out.= '(...)';
      else $out.= '()';
      $out.="\r\n";
      if ($t['function']=='colesogeneralerrordispatch') break;
    }
    return $out;
  }
//=======================================================================
  function debug_bt($message='')
  {
    //Deprecated. Do not use.
    echo "<br>$message<br>";
    echo "<pre>".colesoError::trace()."</pre>";
    die;
  }
//=======================================================================
  function getDescriptionHash()
  {
    $errortype = array (
               E_ERROR           => "Error",
               E_WARNING         => "Warning",
               E_PARSE           => "Parsing Error",
               E_NOTICE          => "Notice",
               E_CORE_ERROR      => "Core Error",
               E_CORE_WARNING    => "Core Warning",
               E_COMPILE_ERROR   => "Compile Error",
               E_COMPILE_WARNING => "Compile Warning",
               E_USER_ERROR      => "User Error",
               E_USER_WARNING    => "User Warning",
               E_USER_NOTICE     => "User Notice",
               E_STRICT          => "Runtime Notice"
               // php5 ,E_STRICT          => "Runtime Notice"
               );
    if (version_compare(phpversion(), '5') >= 0) $errortype[E_STRICT]="Runtime Notice";
    return $errortype;
  }
//=======================================================================
  function generalErrorMessage($errno, $errmsg, $filename, $linenum, $vars)
  {
    $datestamp = date("Y-m-d H:i:s (T)");
    $errortype = $this->getDescriptionHash();
    if (colesoApplication::getConfigVal('/system/errorReporting/FormatStyle')=='HTML'){
      $err = "<b>{$errortype[$errno]}!</b>\n";
    }else{
      $err = "{$errortype[$errno]}!\n---\n";
    }
    $err .= "\tDate: " . $datestamp . "\n";
    $err .= "\tMessage: " . $errmsg . "\n";
    $err .= "\tFile: " . $filename . "\n";
    $err .= "\tLine: " . $linenum . "\n";
    return $err;
  }
//---------------------------------------------------------------------------
  function isCritical($errno)
  {
    $criticalErrors=array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR);
    if (in_array($errno,$criticalErrors)) return true;
    else return false;
  }
//---------------------------------------------------------------------------
  function serveError($errno, $errmsg, $filename, $linenum, $vars)
  {
    $isCritical=$this->isCritical($errno);
    $message=$this->generalErrorMessage($errno, $errmsg, $filename, $linenum, $vars);
    $this->logEngine->message($message,$isCritical,$errno);
    if ($isCritical) $this->logEngine->errdie($errno);
  }
//---------------------------------------------------------------------------

} //Class

//================================================================================

class colesoErrorLog
{
  var $reportLevel; // ALL | CRITICAL
//----------------------------------------
  function colesoErrorLog()
  {
    $this->reportLevel=colesoApplication::getConfigVal('/system/errorReporting/ReportingLevel');
  }
//----------------------------------------
  function getMessage($message,$isCritical)
  {
    if ($isCritical || $this->reportLevel=='ALL') return $message;
    return '';
  }
//----------------------------------------
  function message($message,$isCritical,$errNo)
  {
    //if ($errNo==E_STRICT) return; //we are not E_STRICT Compatible yet
    if (error_reporting()==0) return; //err messages either by config or by @ 
    $message=$this->getMessage($message,$isCritical);
    if ($message) {
      if (colesoApplication::getConfigVal('/system/errorReporting/Backtrace')){
        $message.=colesoError::trace();
      }
      if (colesoApplication::getConfigVal('/system/errorReporting/DisplayErrors')){
        echo "<pre>".$message."</pre>";
      }
      if (colesoApplication::getConfigVal('/system/errorReporting/LogErrors')){
        error_log($message);
      }
    }
  }
//----------------------------------------
  function errdie($errno=0)
  {
    if (!colesoApplication::getConfigVal('/system/errorReporting/DisplayErrors')){
      echo file_get_contents(colesoApplication::getConfigVal('/system/errorReporting/ErrTemplate'));
    }
    die();
  }
}

//=======================================================================
function colesoGeneralErrorDispatch($errno, $errmsg, $filename, $linenum, $vars)
{
  $myError= new colesoError();
  $myError->serveError($errno, $errmsg, $filename, $linenum, $vars);
}

//=======================================================================
function colesoGeneralErrorAssign()
{
  if(colesoApplication::getConfigVal('/system/errorReporting/HandleErrors')){
    $old_error_handler = set_error_handler("colesoGeneralErrorDispatch");
  }
}

//=======================================================================
function colesoErrDie($errmsg='')
{
  if (colesoApplication::getConfigVal('/system/errorReporting/ForceDie')){
    echo "<br><b>$errmsg</b><br>";
    echo "<pre>".colesoError::trace()."</pre>";
    die();
  }
  trigger_error($errmsg,E_USER_ERROR);
}

//=======================================================================
function colesoErrTrace($message='')
{
  echo "<br><b>$message</b><br>";
  echo "<pre>".colesoError::trace()."</pre>";
}
//=======================================================================
function colesoDebugDump($variable)
{
  echo "<pre>".htmlspecialchars (print_r($variable,true))."</pre>";
}
//=======================================================================
function colesoNotice($errmsg='')
{
  trigger_error($errmsg);
}
//=======================================================================
function colesoForceErrDie()
{
  colesoApplication::setConfigVal('/system/errorReporting/ForceDie',true);
}
?>