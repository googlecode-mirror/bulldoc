<?php
abstract class buildOnToc
{
  protected $structureHolder;
  protected $bookKey=null;
  protected $bookTitle='';
  protected $bookData=null;
  
  function __construct($contentFile,$bookKey)
  {
    $this->bookKey=$bookKey;
    $this->loadBookData($contentFile);
    if (!file_exists($contentFile)) throw new configFileNotFoundException();
    if (colesoFileExt($contentFile)=='php') require($contentFile);
    else $TOC=$this->loadYML($contentFile);
    $this->structureHolder=new structureHolder($TOC);
  }
//---------------------------------------------------------------------------
  private function loadYML($file)
  {
    if (is_null($this->bookKey)) colesoErrDie('Book key is not defined');
    $cacheFile=colesoApplication::getConfigVal('/system/cacheDir')."bulldoc/{$this->bookKey}/toc.cache";
    if (file_exists($cacheFile) && (filemtime ($cacheFile) > filemtime ($file))){
      $rawdata=file_get_contents ($cacheFile);
      $TOC=unserialize($rawdata);
    }else {
      $TOC = Spyc::YAMLLoad($file);
      $cacheDir=dirname($cacheFile);
      if (!file_exists($cacheDir)) mkdir ($cacheDir,0666,true);
      file_put_contents ($cacheFile, serialize($TOC));
    }
    return $TOC;
  }
//---------------------------------------------------------------------------
  private function convertTitles()
  {
    
  }
//---------------------------------------------------------------------------
  private function loadBookData($contentFile)
  {
    $dataFile=dirname($contentFile).'/book_data.yml';
    if (file_exists($dataFile)){
      $DATA = Spyc::YAMLLoad($dataFile);
      $this->bookTitle=$DATA['title'];
      $this->bookData=$DATA;
    } else {
      $this->bookTitle=$this->bookKey;
    }
  }
}
