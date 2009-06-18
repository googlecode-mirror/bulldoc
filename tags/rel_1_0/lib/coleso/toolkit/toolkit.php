<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id$
***********************************************************************************/


//obsolete do not use!
class colesoImageFile
{
  var $fileName;
//--------------------------------------------------------
  function colesoImageFile($fileName)
  {
    $this->fileName=$fileName;
  }
//--------------------------------------------------------
  function isNotEmpty()
  {
    return ($this->fileName);
  }
//--------------------------------------------------------
  function getExt()
  {
    $ext=strtolower($this->getOriginalExt());
    return $ext;
  }
//--------------------------------------------------------
  function getOriginalExt()
  {
    if ($this->isNotEmpty()) {
      $path_parts = pathinfo($this->fileName);
      $ext=isset ($path_parts['extension']) ? $path_parts['extension']: '';
      return $ext;
    } else {
      return '';
    }
  }
//--------------------------------------------------------
} //class

//==========================================================
function colesoStripSectionFromPath($path)
{
  $path=rtrim($path,'\\/');
  $parts=explode('/',$path);
  $c=count($parts);
  if ($c==1) return '';
  unset($parts[$c-1]);
  $path=implode('/',$parts).'/';
  return $path;
}
//========================================================================
function colesoFileExt($filename)
{
  $path_parts = pathinfo($filename);
  if (!isset($path_parts['extension'])) return '';
  return $path_parts['extension'];
}
//========================================================================
function colesoConvertedFileExt($filename)
{
  return colesoFileExt(colesoIMGExtConv($filename));
}
//========================================================================
function colesoIMGExtConv($fileName)
{
  $fileName=preg_replace('/\.gif$/i','.png',$fileName);
  $fileName=preg_replace('/\.jpe?g$/i','.jpg',$fileName);
  $fileName=preg_replace('/\.bmp$/i','.jpg',$fileName);
  $fileName=preg_replace('/\.png$/i','.png',$fileName);
  return $fileName;
}

//======================================================================================================
class colesoUrlManipulator
{
  private $varPool=array();
  private $urlParts;
  private $originalURL;

//----------------------------------
  public function __construct($url='')
  {
    $this->originalURL=$url;
    $this->urlParts=parse_url($url);
    if (isset($this->urlParts['query'])) {
      parse_str($this->urlParts['query'], $output);
      $this->varPool=$output;
    }
  }
//-----------------------------------
  public function buildUrl()
  {
    $result='';
    if (isset($this->urlParts['scheme'])) $result.=$this->urlParts['scheme'].'://';
    if (isset($this->urlParts['user'])) $result.=$this->urlParts['user'];
    if (isset($this->urlParts['pass'])) $result.=':'.$this->urlParts['pass'];
    if (isset($this->urlParts['user']) || isset($this->urlParts['pass'])) $result.=':';
    if (isset($this->urlParts['host'])) $result.=$this->urlParts['host'];
    if (isset($this->urlParts['path'])) $result.=$this->urlParts['path'];
    if (count($this->varPool)>0) $result.=$this->getQuery();
    if (isset($this->urlParts['fragment'])) $result.='#'.$this->urlParts['fragment'];
    return $result;
  }
//-----------------------------------
  protected function getQuery()
  {
    $result='';
    if (count($this->varPool)>0) foreach($this->varPool as $k=>$v) $result='&'.$k.'='.urlencode($v);
    $result='?'.ltrim($result,'&');
    return $result;
  }
//-----------------------------------
  public function cdUp($level=1)
  {
    $parts=explode('/',trim($this->urlParts['hostname'],'/'));
    for ($i=0;$i < $level;$i++) array_pop($parts);
    $this->urlParts['hostname']='/'.implode($parts);
  }
//-----------------------------------
  public function resetQuery()
  {
    $this->varPool=array();
  }
//-----------------------------------
  function setVariable($key,$val)
  {
    $this->varPool[$key]=$val;
  }
//---------------------------------------------------------
  function getVariable($key)
  {
    return isset($this->varPool[$key])? $this->varPool[$key]:'';
  }
//---------------------------------------------------------
  function addPool($pool)
  {
    $this->varPool=array_merge($this->varPool, $pool);
  }
//---------------------------------------------------------
  function removeVar($varName)
  {
    unset($this->varPool[$varName]);
  }
}
?>