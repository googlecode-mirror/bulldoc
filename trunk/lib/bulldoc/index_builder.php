<?php

class IndexBuilder
{
  private $index;
  private $sourcePath;
  private $toc;
  
  public function __construct ($sourcePath,$toc)
  {
    $this->sourcePath=$sourcePath;
    $this->toc=$toc;
    
    $this->index=array();
  }
//----------------------------------------------
  public function indexPage($path,$title)
  {
    $pathBuilder=new pathBuilder($path);
    $fileName=$this->sourcePath.$pathBuilder;
    if (file_exists($fileName)) {
      $content=file_get_contents($fileName);
      if (preg_match('/<(?:cls|bdc):keywords\s+data=[\'"](.*?)[\'"]\s*\/>/',$content,$matches)){
        $words=array_unique(split(',', $matches[1]));
        foreach ($words as $word) $this->index[trim($word)][]=array('path' => $path,'title' => $title);
      }
    }
  }
//----------------------------------------------
  public function buildIndex()
  {
    $iterator = new RecursiveIteratorIterator(
                  new contentTreeRecursiveIterator($this->toc),
                  RecursiveIteratorIterator::SELF_FIRST);
    foreach($iterator as $topic){
      $path=ltrim($iterator->getPath().'/'.$topic['href'],'\\/');
      $this->indexPage($path,$topic['title']);
    }
  }
//----------------------------------------------
  public function getIndexArray()
  {
    return $this->index;
  }
//----------------------------------------------
}


//===================================================================================
class IndexRender
{
  private $indexBuilder;
  private $bookKey;
  protected $book;
  private $themeManager;
  
  function __construct($indexBuilder,$book,$themeManager)
  {
    $this->indexBuilder=$indexBuilder;
    $this->book=$book;
    $this->bookKey=$book->getBookKey();
    $this->themeManager=$themeManager;
  }
//-----------------------------------------------------
  function render($indexPagePath)
  {
    $indexTemplateFile=$this->themeManager->getFile('template/index.tset.phtml');
    $indexTemplate= new colesoPHPTemplateSet($indexTemplateFile);
    $indexTemplate->setGlobalVar('outputMode',$this->book->getOutputMode());
    
    $myPathBuilder=new pathBuilder($indexPagePath);
    $rootPath=$myPathBuilder->getRootPath();
    
    $indexArray=$this->getIndexArray();
    
    $curLetter=$html=$buffer='';
    foreach ($indexArray as $word=>$pagesSet){
      $letter=colesoSubstr($word,0,1);
      if ($letter!=$curLetter) {
        if ($buffer) $html.=$indexTemplate->parseItem('indexSection',array('curLetter' => $curLetter, 'buffer' => $buffer));
        $buffer='';
        $curLetter=$letter;
      }
      $buffer.=$indexTemplate->parseItem('indexTopic',array('word'=>$word,'rootPath'=>$rootPath,'pages'=>$pagesSet));
    }
    if ($buffer) $html.=$indexTemplate->parseItem('indexSection',array('curLetter' => $curLetter, 'buffer' => $buffer));
    return $html;
  }
//-----------------------------------------------------
  private function getIndexArray()
  {
    $cacheFile=$this->getIndexCacheFileName();
    if (file_exists($cacheFile)) {
      $rawdata=file_get_contents ($cacheFile);
      $topic_index=unserialize($rawdata);
    } else {
      $this->indexBuilder->buildIndex();
      $topic_index=$this->indexBuilder->getIndexArray();
      ksort($topic_index);
      file_put_contents ($cacheFile, serialize($topic_index));
    }
    return $topic_index;
  }
//-----------------------------------------------------
  public function renderCHMIndex()
  {
    $indexTemplateFile=colesoApplication::getConfigVal('/bulldoc/systemTemplates').'index_chm.tset.phtml';

    $indexTemplate= new colesoPHPTemplateSet($indexTemplateFile);
    $indexArray=$this->getIndexArray();
    
    $html='';
    foreach ($indexArray as $word=>$pagesSet) {
      $html.=$indexTemplate->parseItem('indexTopic',array('word'=>$word, 'pages'=>$pagesSet));
    }
    $html=$indexTemplate->parseItem('indexHeader').$html.$indexTemplate->parseItem('indexFooter');
    return $html;
  }
//-----------------------------------------------------
  private function getIndexCacheFileName()
  {
    return colesoApplication::getConfigVal('/system/cacheDir')."bulldoc/{$this->bookKey}/book_index.cache";
  }
//-----------------------------------------------------
  public function clearCache()
  {
    $cacheFile=$this->getIndexCacheFileName();
    if (file_exists($cacheFile)) unlink($cacheFile);
  }
}

//==========================================================================
function bulldocGetIndexCacheFileName($key)
{
  return colesoApplication::getConfigVal('/system/cacheDir')."bulldoc/{$key}/book_index.cache";
}
?>
