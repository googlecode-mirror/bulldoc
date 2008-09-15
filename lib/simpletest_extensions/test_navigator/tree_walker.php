<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id: tree_walker.php 32 2006-04-16 19:43:23Z hamster $
***********************************************************************************/

require_once ('lib/coleso/fileoperation/fileoperation.php');
require_once ('lib/coleso/toolkit/toolkit.php');
require_once ('lib/coleso/phptemplate/phptemplate.php');


class colesoTestTreeVisitor extends colesoFileDirectoryProcessor
{
  var $templateEngine;
  var $rootDir;
  var $rootURL;
  var $htmlContent;
  var $runAllURL;

  function colesoTestTreeVisitor($templateFileName)
  {
    $this->templateEngine=new colesoPHPTemplateSet($templateFileName);
  }
//-----------------------------------------------------
  function init()
  {
    $this->htmlContent='';
    $data['url']=$this->runAllURL;
    $this->htmlContent=$this->templateEngine->parseItem('allTest',$data);
  }
//-----------------------------------------------------
  function setPath($dirPath,$urlRoot,$runAllURL='all_test.php')
  {
    $this->rootDir=rtrim($dirPath,'\\/');
    $this->rootURL=rtrim($urlRoot,'\\/').'/';
    $this->runAllURL=$runAllURL;
  }
//-----------------------------------------------------
  function preProcessDir($dirName)
  {
    if (strpos($dirName,'.svn') !== false) return false;
    $path_parts = pathinfo($dirName);
    if ($path_parts['basename']=='support') return false;
    if (rtrim($dirName,'\\/')!=$this->rootDir) $shortName= $path_parts['basename'];
    else $shortName='';
    $data['shortDirName']=$shortName;
    if (file_exists(rtrim($dirName,'\\/').'/group_run.php')) {
        $data['groupRunURL']=$this->getPath($dirName).'/group_run.php';
    } else {
      $data['groupRunURL']='';
    }
    $this->htmlContent.=$this->templateEngine->parseItem('dirPre',$data);
    return true;
  }
//-----------------------------------------------------
  function processFile($fileName)
  {
    //
    if (strpos($fileName,'.svn')===false){
      $path_parts = pathinfo($fileName);
      if ($path_parts['basename']=='group_run.php') return;
      if (!preg_match('/test/',$path_parts['basename'])) return;
      $data['url']=$this->getPath($fileName);
      $data['filename']= $path_parts['basename'];
      $data['systemPath']= urlencode($fileName);
      $this->htmlContent.=$this->templateEngine->parseItem('testCase',$data);
    }
  }
//-----------------------------------------------------
  function postProcessDir($dirName)
  {
    if (strpos($dirName,'.svn')===false) {
      $path_parts = pathinfo($dirName);
      if ($path_parts['basename']=='support') return;
      $this->htmlContent.=$this->templateEngine->parseItem('dirPost');
    }
  }
//-----------------------------------------------------
  function getPath($fileName)
  {
      $path_parts = pathinfo($fileName);
      $relPath=str_replace($this->rootDir,'',$fileName);
      $relPath=trim($relPath,'/\\');
      $relPath=preg_replace('%\\\\%','/',$relPath);
      $path=$this->rootURL.$relPath;
      return $path;
  }
}
//=================================================================================

function renderTestTree($path2cases,$url2cases)
{
  if (isset($_GET['mode']) && $_GET['mode']=='source'){
    highlight_file ($_GET['path']);
  } else {
    $processor=new colesoTestTreeVisitor(dirname(__FILE__).'/test_tree.ptps');
    $processor->setPath($path2cases,$url2cases);
    $processor->init();
    $myWalker=new colesoFileOperation();
    $myWalker->traverseDirectoryTree($path2cases,$processor);
    print $processor->htmlContent;
  }
}
?>