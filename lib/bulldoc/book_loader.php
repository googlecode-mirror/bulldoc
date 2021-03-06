<?php
//=================================================================================
function detectAbsolutePath($path)
{
  if (preg_match('/^[a-zA-Z]:(\\\\|\/)/',$path)) return true;
  if (preg_match('/^\//',$path)) return true;
  return false;
}

//=================================================================================
class bookLoader
{
  protected $books;
  
  public function __construct($bookshelfFile=null)
  {
    $this->loadLocalBookshelf($bookshelfFile);
    $this->loadBookShelf();
  }
//---------------------------------------------------------------------------
  private function loadLocalBookshelf($bookshelfFile)
  {
    if (is_null($bookshelfFile)) {
      if (file_exists('bookshelf.yml')) $bookshelfFile='bookshelf.yml';
      else return;
    }
    if (!detectAbsolutePath($bookshelfFile)) $bookshelfFile=getcwd()."/$bookshelfFile";
    colesoApplication::setConfigVal('/bulldoc/bookshelfConfig',$bookshelfFile);
    colesoApplication::setConfigVal('/bulldoc/source',dirname($bookshelfFile).'/');
    colesoApplication::setConfigVal('/bulldoc/output',dirname($bookshelfFile).'/compiled/');
    colesoApplication::setConfigVal('/bulldoc/rootUrl',rtrim(dirname($_SERVER['SCRIPT_NAME']),'\\/').'/');
  }
//---------------------------------------------------------------------------
  private function loadBookShelf()
  {
    $file=colesoApplication::getConfigVal('/bulldoc/bookshelfConfig');
    $cacheSalt=md5($file);
    $cacheFile=colesoApplication::getConfigVal('/system/cacheDir')."bulldoc/bookshelf/$cacheSalt";
    $this->books=colesoYMLLoader::load($file,$cacheFile);

    foreach ($this->books as $key=>$book){
      if (!is_array($this->books[$key])) $this->books[$key]=array('source'=>$book);
      $this->books[$key]['source']=$this->getBookSource($key);
      $this->obtainBookData($key);
    }
  }
//-----------------------------------------------------------
  public function getBookSource($key)
  {
    if (!isset($this->books[$key]['source']) || $this->books[$key]['source']=='') {
      return colesoApplication::getConfigVal('/bulldoc/source')."$key/";
    }
    $source=$this->books[$key]['source'];
    if (detectAbsolutePath($source)) return $source;
    $source=colesoApplication::getConfigVal('/bulldoc/source').$source;
    $source=rtrim($source,'\\/').'/';
    return $source;
  }
//-----------------------------------------------------------
  public function obtainBookData($key)
  {
    $dataFile= $this->books[$key]['source'].'book_data.yml';
    if (file_exists($dataFile)) $DATA = Spyc::YAMLLoad($dataFile);  //probably we need to use cache
    else $DATA=array();
    
    if (isset($this->books[$key]['title'])) $bookShelfTitle=$this->books[$key]['title'];
    elseif (isset($DATA['title'])) $bookShelfTitle=$DATA['title'];
    else $bookShelfTitle=$key;
    
    $this->books[$key]=array_merge($this->books[$key],$DATA);
    $this->books[$key]['bookShelfTitle']=$bookShelfTitle;
    
    if (!isset($this->books[$key]['outputMode'])) $this->books[$key]['outputMode']='html';
  }
//-----------------------------------------------------------
  public function getBooks()
  {
    return $this->books;
  }
//-----------------------------------------------------------
  public function getBookTitle($key)
  {
    return isset($this->books[$key]['title'])? $this->books[$key]['title']:$this->books[$key]['bookShelfTitle']; 
  }
//-----------------------------------------------------------
  public function getBook($key)
  {
    if (!isset($this->books[$key]) || isset($this->books[$key]['separatorTitle'])) throw new Exception("Book $key is not defined");
    return new book($this->books[$key],$key);
  }
}

//======================================================================
class book
{
  private $bookData;
  private $bookKey;

  protected $tocFileName;
  protected $pagesSourcePath;
  
  public function __construct($bookData,$bookKey)
  {
    $this->bookData=$bookData;
    $this->bookKey=$bookKey;
    $this->tocFileName=$this->obtainTocFileName();
    $this->pagesSourcePath=dirname($this->tocFileName).'/pages/';
  }
//-----------------------------------------------------------
  public function getBookDest()
  {
    if (!isset($this->bookData['dest'])) 
      return colesoApplication::getConfigVal('/bulldoc/output')."{$this->bookKey}/";

    $dest=$this->bookData['dest'];
    if (detectAbsolutePath($dest)) return $dest;
    $dest=colesoApplication::getConfigVal('/bulldoc/output')."$dest";
    $dest=rtrim($dest.'\\/').'/';
    return $dest;
  }
//-----------------------------------------------------------
  public function getBookKey()
  {
    return $this->bookKey;
  }
//-----------------------------------------------------------
  public function getBookData()
  {
    return $this->bookData;
  }
//-----------------------------------------------------------
  public function getBookTitle()
  {
    return  $this->bookData['title'];
  }
//-----------------------------------------------------------
  public function getBookLanguage()
  {
    return isset($this->bookData['language'])? $this->bookData['language']:false; 
  }
//-----------------------------------------------------------
  public function getBookLocale()
  {
    return isset($this->bookData['locale'])? $this->bookData['locale']:null; 
  }

//-----------------------------------------------------------
  public function getOutputMode()
  {
    return $this->bookData['outputMode'];
  }
//-----------------------------------------------------------
  public function getBookSource()
  {
    return $this->bookData['source'];
  }
//-----------------------------------------------------------
  public function getBookStyle()
  {
    $styleFilePath=$this->bookData['source'].'pages/book_style.css';
    if (file_exists($styleFilePath)) return $styleFilePath;
    return '';
  }
//-----------------------------------------------------------
  public function getBookTheme()
  {
    $workshopThemeDir=colesoApplication::getConfigVal('/bulldoc/themeDir');
    $workshopThemeUrl=colesoApplication::getConfigVal('/bulldoc/themeUrl');
    if(isset($this->bookData['theme'])){
      $theme=$this->bookData['theme'];
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
      $defaultTheme=colesoApplication::getConfigVal('/bulldoc/defaultTheme');
      return array(
        'themePath'=> $workshopThemeDir.$defaultTheme,
        'themeUrl'=>  $workshopThemeUrl.$defaultTheme
        );
    }
  }
//-----------------------------------------------------------
  protected function getBookRootIndexLevel()
  {
    return isset($this->bookData['rootIndexLevel'])? $this->bookData['rootIndexLevel']:-1;
  }
//-----------------------------------------------------------
  public function getTocFileName()
  {
    return $this->tocFileName;
  }
//-----------------------------------------------------------
  public function getStructureHolder()
  {  
    $cacheFile=colesoApplication::getConfigVal('/system/cacheDir')."bulldoc/{$this->bookKey}/toc.cache";
    $TOC=colesoYMLLoader::load($this->tocFileName,$cacheFile);
    $structureHolder=new structureHolder($TOC);
    return $structureHolder;
  }
//-----------------------------------------------------------
  public function getPagesSourcePath()
  {
    return $this->pagesSourcePath;
  }
//-----------------------------------------------------------
  protected function obtainTocFileName()
  {
    $sourcePath=rtrim($this->getBookSource(),'\\/').'/';
    if (file_exists($sourcePath.'toc.php')) return $sourcePath.'toc.php';
    elseif (file_exists($sourcePath.'toc.yml')) return $sourcePath.'toc.yml';
    throw new configFileNotFoundException();
  }
//-----------------------------------------------------------
  public function getBookRenderer()
  {
    $manager=new bulldocDecoThemes($this->getBookTheme());
    $render=new renderDocPage($this,$manager);
    $rootIndexLevel=$this->getBookRootIndexLevel();
    if ($rootIndexLevel) $render->setRootIndexLevel($rootIndexLevel);
    return $render;
  }
}
?>
