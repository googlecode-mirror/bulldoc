<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id: toolkit.lib.php 236 2007-07-18 09:08:00Z hamster $
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
?>