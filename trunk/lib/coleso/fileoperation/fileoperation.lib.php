<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id: fileoperation.lib.php 239 2007-08-17 05:53:02Z hamster $
***********************************************************************************/

class colesoFileOperation
{
  var $recurseMode=true;

function traverseDirectoryTree($dirnm,&$processor)
{
  $dirnm=rtrim($dirnm,'\\/');
  if (!file_exists ($dirnm)) {
		//die ('the directory \''.$dirnm.'\' doest not exist');
  } elseif (is_dir ($dirnm)) {
	  if (!$processor->preProcessDir($dirnm)) return;
	  $handle=opendir($dirnm);
		while ($file = readdir($handle)) {
		  if($file=='.'||$file=='..') continue;
			if(is_dir($dirnm.'/'.$file)) {
			  if  ($this->recurseMode) $this->traverseDirectoryTree($dirnm.'/'.$file,$processor);
			  else {
      	  $processor->preProcessDir($dirnm.'/'.$file);
      	  $processor->postProcessDir($dirnm.'/'.$file);
			  }
			} else {
			  $processor->processFile($dirnm.'/'.$file);
      }
    }
	  closedir($handle);
	  $processor->postProcessDir($dirnm);
  } else {
	  die  ('the file with this name allready exists, -- it is a file not a directory');
  }
}

} //class
//===========================================
class colesoFileDirectoryProcessor
{
  //interface class
  function preProcessDir($dirName)
  {
    die ('Abstract class do not use directly');
  }

  function processFile($fileName)
  {
    die ('Abstract class do not use directly');
  }

  function postProcessDir($dirName)
  {
    die ('Abstract class do not use directly');
  }

}

//===========================================
class colesoFileDirectoryWalker extends colesoFileDirectoryProcessor
{
  var $fromPath;

  function colesoFileDirectoryWalker($fromPath='')
  {
    $this->fromPath=$fromPath;
  }
//------------------------------------------------
  function preProcessDir($dirName)
  {
    //Nothing to do -- we need first remove files in this dir
    return true;
  }
//------------------------------------------------
  function processFile($fileName)
  {
    //nop
  }
//------------------------------------------------
  function postProcessDir($dirName)
  {
    //nop
  }
}


//===========================================
class colesoFileDirectoryEraser extends colesoFileDirectoryProcessor
{
  var $fromPath;

  function colesoFileDirectoryEraser($fromPath='')
  {
    $this->fromPath=$fromPath;
  }
//------------------------------------------------
  function preProcessDir($dirName)
  {
    //Nothing to do -- we need first remove files in this dir
    return true;
  }
//------------------------------------------------
  function processFile($fileName)
  {
    unlink ($fileName);
  }
//------------------------------------------------
  function postProcessDir($dirName)
  {
    if ($this->fromPath!=$dirName) rmdir ($dirName);
  }
}

//===========================================
class colesoDirectoryCopier extends colesoFileDirectoryProcessor
{
  var $pathFrom;
  var $pathTo;
  var $ignoreParent;

  var $_firstFlag=true;
  var $parentPathLength;

  function colesoDirectoryCopier($pathFrom,$pathTo,$ignoreParent=true)
  {
    $this->pathFrom=$pathFrom;
    $this->pathTo=rtrim($pathTo,'/\\');
    $this->ignoreParent=$ignoreParent;
    $this->parentPathLength=strlen($pathFrom);
  }

//------------------------------------------------
  function preProcessDir($dirName)
  {
    if ($this->_firstFlag) {
      $this->_firstFlag=false;
      return true;
    }
    $dirname=$this->pathTo.'/'.$this->removeParentPath($dirName);
    colesoRecursiveMkdir($dirname);
    return true;
  }
//------------------------------------------------
  function processFile($fileName)
  {
    $target=$this->pathTo.'/'.$this->removeParentPath($fileName);
    copy ($fileName,$target);
  }
//------------------------------------------------
  function postProcessDir($dirName)
  {
    //nop -- nothing to do
  }
//------------------------------------------------
  function removeParentPath($path)
  {
    return (substr($path,$this->parentPathLength));
  }
}

//==========================================================================
function colesoClearDir($path,$mode='keepSource')
{
  $path=rtrim($path,'\\/');
  $fromPathVar=($mode=='keepSource')? $path:'';
  $myProcessorDel= new colesoFileDirectoryEraser($fromPathVar);
  $myWalker=new colesoFileOperation();
  $myWalker->traverseDirectoryTree($path,$myProcessorDel);
}

//==========================================================================
function colesoRecursiveMkdir($path,$mode=0777)
{
  if (!file_exists($path)){
    colesoRecursiveMkdir(dirname($path),$mode);
    mkdir($path, $mode);
  }
}

//==========================================================================
function colesoRecursiveCopyFolders($source,$dest)
{
  $extLibFolder=$source;
  $myProcessorCopy=new colesoDirectoryCopier($extLibFolder,$dest);
  $myWalker=new colesoFileOperation();
  $myWalker->traverseDirectoryTree($extLibFolder,$myProcessorCopy);
}

//==========================================================================
//compatibility. should be removed in future
if ( !function_exists('file_put_contents') && !defined('FILE_APPEND') ) {
  define('FILE_APPEND', 1);
  function file_put_contents($n, $d, $flag = false) {
    $mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
    $f = @fopen($n, $mode);
    if ($f === false) {
        return 0;
    } else {
        if (is_array($d)) $d = implode($d);
        $bytes_written = fwrite($f, $d);
        fclose($f);
        return $bytes_written;
    }
  }
}

?>