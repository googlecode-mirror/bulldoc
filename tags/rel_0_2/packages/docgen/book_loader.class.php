<?php
class onlineBookLoader
{
  protected $books;
  
  public function __construct()
  {
    $this->loadBookShelf();
  }
//---------------------------------------------------------------------------
  private function loadBookShelf()
  {
    $file=colesoApplication::getConfigVal('/system/docRoot').'config/bookshelf.yml';
    $cacheFile=colesoApplication::getConfigVal('/system/cacheDir')."bulldoc/bookshelf.cache";
    if (file_exists($cacheFile) && (filemtime ($cacheFile) > filemtime ($file))){
      $rawdata=file_get_contents ($cacheFile);
      $this->books=unserialize($rawdata);
    }else {
      $this->books = Spyc::YAMLLoad($file);
      $cacheDir=dirname($cacheFile);
      if (!file_exists($cacheDir)) mkdir ($cacheDir,0666,true);
      file_put_contents ($cacheFile, serialize($this->books));
    }
    
    foreach ($this->books as $key=>$book){
      if (!is_array($this->books[$key])) $this->books[$key]=array('source'=>$book);
      $this->books[$key]['title']=$this->getBookTitle($key);
    }
  }
//-----------------------------------------------------------
  protected function getBookDest($bookName)
  {
    if (!isset($this->books[$bookName]['dest'])) 
      return colesoApplication::getConfigVal('/docgen/workshopDir')."output/$bookName/";

    $dest=$this->books[$bookName]['dest'];
    if (detectAbsolutePath($dest)) return $dest;
    $dest=colesoApplication::getConfigVal('/docgen/workshopDir')."output/$dest";
    $dest=rtrim($dest.'\\/').'/';
    return $dest;
  }
//-----------------------------------------------------------
  protected function getBookTitle($bookName)
  {
    if (isset($this->books[$bookName]['title'])) return  $this->books[$bookName]['title'];
    $dataFile=$this->getBookSource($bookName).'book_data.yml';
    if (file_exists($dataFile)){
      $DATA = Spyc::YAMLLoad($dataFile);
      return $DATA['title'];
    } else {
      return $bookName;
    }
  }
//-----------------------------------------------------------
  protected function needChm($bookName)
  {
    return isset($this->books[$bookName]['buildChm'])? $this->books[$bookName]['buildChm']:false; 
  }
//-----------------------------------------------------------
  protected function getBookSource($bookName)
  {
    if (!isset($this->books[$bookName]['source']) || $this->books[$bookName]['source']=='') {
      return colesoApplication::getConfigVal('/docgen/workshopDir')."source/$bookName/";
    }
    $source=$this->books[$bookName]['source'];
    if (detectAbsolutePath($source)) return $source;
    $source=colesoApplication::getConfigVal('/docgen/workshopDir')."source/$source";
    $source=rtrim($source,'\\/').'/';
    return $source;
  }
//-----------------------------------------------------------
  protected function getBookTheme($bookName)
  {
    $workshopThemeDir=colesoApplication::getConfigVal('/docgen/workshopDir').'themes/';
    $workshopThemeUrl=colesoApplication::getConfigVal('/docgen/workshopUrl').'themes/';
    if(isset($this->books[$bookName]['theme'])){
      $theme=$this->books[$bookName]['theme'];
      if (is_array($theme)) {
        if (!detectAbsolutePath($theme['themePath'])) $theme['themePath']=$workshopThemeDir.$theme['themePath'];
        if (!detectAbsolutePath($theme['themeUrl']))  $theme['themeUrl']=$workshopThemeUrl.$theme['themeUrl'];
      } else {
        $theme=array(
          'themePath'=> $workshopThemeDir.$theme,
          'themeUrl'=>  $workshopThemeUrl.$theme
          );
      }
      return $theme;
    } else {
      $defaultTheme=colesoApplication::getConfigVal('/docgen/defaultTheme');
      return array(
        'themePath'=> $workshopThemeDir.$defaultTheme,
        'themeUrl'=>  $workshopThemeUrl.$defaultTheme
        );
    }
  }
//-----------------------------------------------------------
  protected function getBookRootIndexLevel($bookName)
  {
    return isset($this->books[$bookName]['rootIndexLevel'])? $this->books[$bookName]['rootIndexLevel']:-1;
  }
//-----------------------------------------------------------
  protected function getTocFileName($bookName)
  {
    $sourcePath=rtrim($this->getBookSource($bookName),'\\/').'/';
    if (file_exists($sourcePath.'toc.php')) return $sourcePath.'toc.php';
    elseif (file_exists($sourcePath.'toc.yml')) return $sourcePath.'toc.yml';
    throw new configFileNotFoundException();
  }
//-----------------------------------------------------------
  protected function getBookRenderer($bookName)
  {
    $tocFile=$this->getTocFileName($bookName);
    $manager=new decoThemes($this->getBookTheme($bookName));
    $render=new renderDocPage($tocFile,$bookName,$manager);
    $rootIndexLevel=$this->getBookRootIndexLevel($bookName);
    if ($rootIndexLevel) $render->setRootIndexLevel($rootIndexLevel);
    return $render;
  }
}
?>
