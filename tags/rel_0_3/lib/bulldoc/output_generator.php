<?php
require_once('coleso/fileoperation/std_directory_iterators.php');
require_once('bulldoc/media_dir_iterators.php');

class outputGenerator
{
  private $toc;
  private $render;
  private $outputPath;
  public $mediaExt;
  private $themeManager;
    
  public function __construct($pageRender,$outputPath)
  {
    $this->toc=$pageRender->getToc();
    $this->render=$pageRender;
    $this->outputPath=rtrim($outputPath,'\\/').'/';
    $this->mediaExt='gif,jpg,jpeg,png,pdf,zip,gz,tgz,css,js';
    $this->themeManager=$this->render->getThemeManager();
  }
//------------------------------------------------------------------
  private function copyMediaContent()
  {
    $it = new mediaDirectoryFilterIterator(
      $this->render->getSourcePath(),
      $this->outputPath,
      $this->mediaExt);
    foreach ($it as $mediaItem){
      $mediaItem->copy();
    }
  }
//------------------------------------------------------------------
  private function copyAssetsContent()
  {
    mkdir($this->outputPath.'_assets');
    $source=$this->themeManager->getFile('web');
    
    $it = new mediaDirectoryFilterIterator(
      $source,
      $this->outputPath.'_assets',
      $this->mediaExt);
    foreach ($it as $mediaItem){
      $mediaItem->copy();
    }
  }
//------------------------------------------------------------------
  private function getDestPath($path)
  {
    return $this->outputPath.$path;
  }
//------------------------------------------------------------------
  function saveDestFile($path,$content)
  {
    $dirname=dirname($this->getDestPath($path));
    if (!file_exists($dirname)) mkdir ($dirname,0666,true);
    file_put_contents ($this->getDestPath($path), $content);
  }
//------------------------------------------------------------------
  public function buildSection($section,$path)
  {
    foreach ($section as $topic=>$params){
      if (isset($params['type']) && $params['type']=='chapter') $topicPath=$path.$topic.'/index.html';
      else $topicPath=$path.$topic;
      $topicPath=ltrim($topicPath,'\\/');
      
      echo $topicPath."\n";
      $content=$this->render->renderPage($topicPath);
      $this->saveDestFile($topicPath,$content);
      
      if (isset($params['type']) && $params['type']=='chapter') {
        if (!is_array($params['topics'])) colesoErrDie('Chapter should have topics!');
        $this->buildSection($params['topics'],$path.$topic.'/');
      }
    }
  }
//------------------------------------------------------------------
  public function build()
  {
    if (!file_exists($this->outputPath)) mkdir($this->outputPath,0777,true);
    else directoryClear($this->outputPath);
    $this->copyAssetsContent();
    $this->copyMediaContent();
    $rootContent=$this->render->renderPage('index.html');
    $this->saveDestFile('index.html',$rootContent);

    $this->buildSection($this->toc,'/');
  }
}

?>
