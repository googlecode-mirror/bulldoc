<?php
require_once('coleso/fileoperation/std_directory_iterators.php');
require_once('bulldoc/media_dir_iterators.php');

class outputGenerator
{
  private $toc;
  private $render;
  private $book;
  private $booKey;
  private $outputPath;
  public $mediaExt;
  private $themeManager;
  
  private $singlePageContent='';
  
  private $textConvPageDir;
    
  public function __construct($book)
  {
    $this->render=$book->getBookRenderer();
    $this->render->setMode('static');
    $this->book=$book;
    $this->booKey=$book->getBookKey();
    if ($this->book->getBookLanguage()) {
      colesoApplication::setLanguage($this->book->getBookLanguage(),$this->book->getBookLocale());
      colesoApplication::loadMessages('bulldoc/messages');
    }    
    $this->toc=$this->render->getToc();
    $this->outputPath=rtrim($book->getBookDest(),'\\/').'/';
    $this->mediaExt='gif,jpg,jpeg,png,pdf,zip,gz,tgz,css,js';
    $this->themeManager=$this->render->getThemeManager();
  }
//------------------------------------------------------------------
  private function copyMediaContent()
  {
    $outputPath=$this->outputPath;
    if ($this->book->getOutputMode()=='html_single') {
      $outputPath.='images';
      mkdir($outputPath);
    }
    
    $it = new mediaDirectoryFilterIterator(
      $this->render->getSourcePath(),
      $outputPath,
      $this->mediaExt);
    foreach ($it as $mediaItem) $mediaItem->copy();
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
    foreach ($it as $mediaItem) $mediaItem->copy();
  }
//------------------------------------------------------------------
  private function getDestPath($path)
  {
    return $this->outputPath.$path;
  }
//------------------------------------------------------------------
  function saveDestFile($path,$content)
  {
    if ($this->book->getOutputMode()=='html_single') {
      $path_parts = pathinfo($path);
      $this->textConvPageDir=($path_parts['dirname']=='.')? '':$path_parts['dirname'].'/';
      $content=preg_replace_callback('/<(a|img)(.*?)(src|href)=["\'](.*?)["\'](.*?)>/i',
                                     array($this,'singlePageLinkProcess'),$content);
      $this->singlePageContent.=$content;
    } else {
      $dirname=dirname($this->getDestPath($path));
      if (!file_exists($dirname)) mkdir ($dirname,0777,true);
      file_put_contents ($this->getDestPath($path), $content);
    }
  }
//------------------------------------------------------------------
  protected function singlePageLinkProcess($matches)
  {
    if (detectAbsolutePath($matches[4])) return $matches[0];
    if($matches[1]=='a' && substr($matches[4],0,1)=='#') return $matches[0];
    return '<'.$matches[1].$matches[2].$matches[3].'="images/'.$this->textConvPageDir.$matches[4].'"'.$matches[5].'>';
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
    $cacheFile=bulldocGetIndexCacheFileName($this->booKey);
    if (file_exists($cacheFile)) unlink($cacheFile);

    if (!file_exists($this->outputPath)) mkdir($this->outputPath,0777,true);
    else directoryClear($this->outputPath);
    $this->copyAssetsContent();
    $this->copyMediaContent();
    
    $rootContent=$this->render->renderPage('index.html');
    $this->saveDestFile('index.html',$rootContent);
    $this->buildSection($this->toc,'/');
    
    if ($this->book->getOutputMode()=='html_single') $this->buildSinglePage();
    if ($this->book->getOutputMode()=='chm') $this->buildCHM();
  }
//---------------------------------------------------
  private function buildSinglePage()
  {
    $mainLayout=$this->themeManager->getFile('template/singlepage_layout.tpl.phtml');
    $data=array(
      'content'=>$this->singlePageContent,
      'bookData'=>$this->book->getBookData()
      );
    
    $bodyContent=colesoPHPTemplate::parseFile($mainLayout, $data);
    
    $masterData=array(
      'assetsURL'=>'_assets/',
      'bookTitle'=>$this->book->getBookTitle(),
      'outputMode'=>$this->book->getOutputMode(),
      'content' => $bodyContent
      );
    if ($this->book->getBookStyle()) $data['customStyleUrl']='images/book_style.css';
    $masterLayout=$this->themeManager->getFile('template/master_layout.tpl.phtml');
    $result=colesoPHPTemplate::parseFile($masterLayout, $masterData);
    
    if (!file_exists($this->outputPath)) mkdir($this->outputPath,0777,true);
    file_put_contents ($this->outputPath.'single.html', $result);
  }
//---------------------------------------------------
  private function buildCHM()
  {
    $this->buildCHMToc();
    $this->buildCHMIndex();
  }
//---------------------------------------------------
  private function buildCHMToc()
  {
    $tocFile=$this->book->getTocFileName();
    $chmBuilder=new buildCHMToc($this->book);
    list($toc,$proj)=$chmBuilder->buildTOC();
    file_put_contents($this->outputPath.$this->booKey.'.hhc',$toc);
    file_put_contents($this->outputPath.$this->booKey.'.hhp',$proj);
    echo "\nchm structure built\n";
  }
//---------------------------------------------------
  private function buildCHMIndex()
  {
    $sourcePath=$this->book->getPagesSourcePath();
    $toc=$this->book->getStructureHolder()->getToc();
    $indexBuilder=new IndexBuilder($sourcePath,$toc);
    $myIndexRender=new IndexRender($indexBuilder,$this->book,null);
    $content=$myIndexRender->renderCHMIndex();

    file_put_contents($this->outputPath.$this->booKey.'.hhk',$content);
    echo "chm index built\n";
  }
}

?>
