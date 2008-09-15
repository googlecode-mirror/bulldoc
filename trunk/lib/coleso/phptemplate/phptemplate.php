<?php
/*

$Id: phptemplate.lib.php 219 2007-06-13 08:37:00Z hamster $
*/

class colesoPHPTemplate
{
//---------------------------------------------
  static function parseFile($_filename,$_data='')
  {
    if (is_array($_data)) extract($_data);
    ob_start();
    require $_filename;
    $_content = ob_get_contents();
    ob_end_clean();
    return $_content;
  }
}
//==========================================================
class colesoPHPStringTemplate
{
  var $template;

  function colesoPHPStringTemplate($template)
  {
    $this->template=$template;
  }
//---------------------------------------------
  function parse($_data)
  {
    if (!is_array($_data)) {
      colesoErrDie('Strange non array value passed: '.$_data);
    }
    extract($_data);
    ob_start();
    eval($this->encloseTemplateString($this->template));
    $_content = ob_get_contents();
    ob_end_clean();
    return $_content;
  }
//---------------------------------------------
  function encloseTemplateString($tstring)
  {
    $before=' ?'.'>';
    $after='<'.'?php ';
    return $before.$tstring.$after;
  }
}
//==========================================================
class colesoPHPTemplateSet
{
  private $curKey;
  private $namedTemplates;
  private $globalTemplateData;

//---------------------------------------------
  function __construct($templateFileName='')
  {
    if ($templateFileName) $this->readTemplateSet($templateFileName);
    $this->globalTemplateData=array();
  }
//---------------------------------------------
  function setGlobalData($globalData)
  {
    $this->globalTemplateData=array_merge($this->globalTemplateData,$globalData);
  }
//---------------------------------------------
  function getGlobalData()
  {
    return $this->globalTemplateData;
  }
//---------------------------------------------
  function setGlobalVar($name,$value)
  {
    $this->globalTemplateData[$name]=$value;
  }
//---------------------------------------------
  function getGlobalVar($name)
  {
    return isset($this->globalTemplateData[$name])? $this->globalTemplateData[$name]:'';
  }
//---------------------------------------------
  function encloseTemplateString($tstring)
  {
    $before=' ?'.'>';
    $after='<'.'?php ';
    return $before.$tstring.$after;
  }
//---------------------------------------------
  function parseString($_templateContent,$_data)
  {
    extract($_data);
    if (is_array($this->globalTemplateData)) extract($this->globalTemplateData);
    ob_start();
    eval($this->encloseTemplateString($_templateContent));
    $_content = ob_get_contents();
    ob_end_clean();
    return $_content;
  }
//---------------------------------------------
  function addSection($key,$string)
  {
    $this->namedTemplates[$key]=$string;
  }
//---------------------------------------------
  function addString($tstring)
  {
    if ($this->curKey){
      $this->namedTemplates[$this->curKey].=$tstring;
    } else {
      colesoErrDie('No key string provided before content');
    }
  }
//---------------------------------------------
  function setSectionTemplate($section,$tstring)
  {
    $this->namedTemplates[$section]=$tstring;
  }
//---------------------------------------------
  function includeTemplateSet($filename,$key='')
  {
    $templateDir=dirname($filename);
    if (!file_exists($filename)) colesoErrDie("File $filename does not exist");
    $handle = fopen($filename, "r");
    $this->curKey=$key;
    if ($key) $this->namedTemplates[$key]='';
    while (!feof($handle)) {
      $buffer = fgets($handle, 4096);
      if (preg_match("/<!-- #include <(.*?)># -->/",$buffer,$matches)){
        $this->includeTemplateSet($templateDir.'/'.$matches[1]);
      } elseif (preg_match("/<!-- #(.*?)# -->/",$buffer,$matches)) {
        $this->curKey=$matches[1];
        $this->namedTemplates[$this->curKey]='';
      } else {
        $this->addString($buffer);
      }
    }
    fclose($handle);
  }
//---------------------------------------------
  function readTemplateSet($filename)
  {
    $this->namedTemplates=array();
    $this->includeTemplateSet($filename);
  }
//---------------------------------------------
  function parseItem($itemName,$data='')
  {
    if (!isset($this->namedTemplates[$itemName])) return '';
    if (is_array($data)) return $this->parseString($this->namedTemplates[$itemName],$data);
    else return $this->parseString($this->namedTemplates[$itemName],array('content'=>$data));
  }
}
?>
