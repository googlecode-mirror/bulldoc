<?php
require_once(colesoLibrarian::lib_lname('phptemplate'));
require_once(colesoLibrarian::lib_lname('toolkit'));
require_once (colesoLibrarian::getExternalLib('spyc/spyc.php5'));
require_once(dirname(__FILE__).'/build_on_toc.class.php');
require_once(dirname(__FILE__).'/default_highlight.php');
require_once(dirname(__FILE__).'/theme_manager.class.php');
require_once(dirname(__FILE__).'/toc_manipulation.php');


//===========================================================================

class renderDocPage  extends buildOnToc
{
  private $themeManager;
  private $navTemplate;
  private $layoutTemplateFile;
  private $sourcePath;
  private $mode='server';
  
  function __construct($contentFile,$bookKey,$themeManager=null)
  {
    parent::__construct($contentFile,$bookKey);
    if ($themeManager) $this->themeManager=$themeManager;
    else $this->themeManager=new decoThemes();
    
    $navTemplateFile=$this->themeManager->getFile('template/navigation.tset.phtml');
    $this->navTemplate=new colesoPHPTemplateSet($navTemplateFile);
    
    $this->layoutTemplateFile=$this->themeManager->getFile('template/layout.tpl.phtml');

    $this->sourcePath=dirname($contentFile).'/pages/';
  }
//---------------------------------------------------------------------------
  public function setMode($mode)
  {
    $this->mode=$mode;
  }
//---------------------------------------------------------------------------
  public function setBookTitle($title)
  {
    die ('deprecated!');
    $this->bookTitle=$title;
  }
//---------------------------------------------------------------------------
  public function getThemeManager()
  {
    return $this->themeManager;
  }
//---------------------------------------------------------------------------
  public function getToc()
  {
    return $this->structureHolder->getToc();
  }
//------------------------------------------------------------------------
  public function setRootIndexLevel($level)
  {  
    $this->structureHolder->setRootIndexLevel($level);
  }
//---------------------------------------------------------------------------
  public function getSourcePath()
  {
    return $this->sourcePath;
  }
//---------------------------------------------------------------------------
  public function renderPage($path)
  {
    try {
      $pathBuilder=new pathBuilder($path,$this->mode);
      $section=$this->structureHolder->getPageSection($pathBuilder);
      $menu=$this->buildMenu($section['curSection'],$pathBuilder);
      $data=array(
        'path'=>$path,
        'bookTitle'=>$this->bookTitle,
        'bookData'=>$this->bookData,
        'rootURL'=>$pathBuilder->getRootUrl(),
        'assetsURL'=>$this->getAssetsUrl($pathBuilder),
        'level'=>$pathBuilder->getLevel(),
        'next'=>$menu['next'],
        'prev'=>$menu['prev'],
        'menu'=>$menu['menu'],
        'upLevelLink'=>$pathBuilder->isIndex()? '../index.html':'index.html',
        'upTitle'=>$section['upTitle'],
        'curTitle'=>$section['curTitle'],
        'content' => $this->getContent($pathBuilder)
        );
      return colesoPHPTemplate::parseFile($this->layoutTemplateFile, $data);
    } catch (pageNotFoundException $e){
      colesoErrDie($e->getMessage());
    }
  }
//---------------------------------------------------------------------------
  private function getAssetsUrl($pathBuilder)
  {
    if ($this->mode=='server') {
      return $this->themeManager->getUrl('web/');
    } else {
      return $pathBuilder->getStaticAssetsUrl();
    }
  }
//---------------------------------------------------------------------------
  private function buildMenu($section,$pathBuilder)
  {
    if ($section===false) return $this->getRootMenuData();
    $html='';
    $prev=$next=$curTitle='';
    $sectionIterator=new contentTreeSectionIterator(new ArrayIterator($section));
    $sectionIterator->setIsIndex($pathBuilder->isIndex());
    foreach ($sectionIterator as $key=>$topic){
      if ($sectionIterator->key()==$pathBuilder->getPageName()) {
        $html.=$this->navTemplate->parseItem('active_menu_topic',$topic->getData());
        $curTitle=$topic->getTitle();
        $prev=$sectionIterator->getPrevTopicData();
        $next=$sectionIterator->getNextTopicData();
      } else $html.=$this->navTemplate->parseItem('menu_topic',$topic->getData());
    }
    return array('menu'=>$html,'next'=>$next,'prev'=>$prev,'curTitle'=>$curTitle);
  }
//---------------------------------------------------------------------------
  private function getRootMenuData()
  {
    $toc=$this->getToc();
    list($href,$val)=each ($toc);
    if (isset($val['type']) && $val['type']=='chapter') $href.='/index.html';
    return array(
      'menu'=>'',
      'next'=>array('href'=>$href,'title'=>is_array($val)?$val['title']:$val),
      'prev'=>'');
  }
//---------------------------------------------------------------------------
  private function getContent($pathBuilder)
  {
    $templateClass=colesoApplication::getConfigVal('/docgen/textProcessingClass');
    $t=new $templateClass;
    $fileName=$this->sourcePath.$pathBuilder;
    $params=array('root'=>$pathBuilder->getRootPath(),'path'=>$pathBuilder,'structure'=>$this->structureHolder);
    if ($pathBuilder->isIndex()){
      $content='';
      if (file_exists($fileName)) $content.=$t->parseFile($fileName,$params);
      $content.=$this->navTemplate->parseItem('toc',$this->buildIndex($pathBuilder));
      return $content;
    } elseif (file_exists($fileName)){
      return $t->parseFile($fileName,$params);
    } else {
      return colesoApplication::getSysMessage('docgen','underconstruction');
    }
  }
//---------------------------------------------------------------------------
  private function buildIndex($pathBuilder)
  {
    $sectionData=$this->structureHolder->getPageSection($pathBuilder,'current');
    $section=$sectionData['curSection'];
    $level=$sectionData['level'];
    $iterator =  new RecursiveIteratorIterator(new contentTreeRecursiveIterator($section),RecursiveIteratorIterator::SELF_FIRST);
    $html='';
    foreach($iterator as $topic){
      if ($level!=-1 && $iterator->getDepth() > $level-1) continue;
      $topic['href']=ltrim($iterator->getPath().'/'.$topic['href'],'\\/');
      $topic['level']=$iterator->getDepth();
      $html.=$this->navTemplate->parseItem('toc_topic',$topic);
    }
    return $html;
  }
}


//====================================================================================
class pathBuilder
{
  private $path;
  private $pageName;
  private $isIndex;
  private $level;
  private $rootPath;
  private $rootUrl;
  private $pathParts;
  private $mode;
  
  public function __construct($path,$mode='server')
  {
    $this->mode=$mode;
    $path=ltrim($path,'\\/');
    if ($path=='') $path='index.html';
    $this->path=$path;
    $this->pageName=basename($path);
    $this->isIndex=($this->pageName=='index.html');
    if ($this->isIndex) $this->pageName=basename(dirname($path));
    $this->pathParts=explode('/',$path);
    $this->level=count($this->pathParts)-1;
    $this->rootPath=str_repeat('../',$this->level);
    $this->rootUrl=$this->rootPath.'index.html';
  }
//---------------------------------------------------------------------------
  public function getStaticAssetsUrl()
  {
    return $this->rootPath.'_assets/';
  }
//---------------------------------------------------------------------------
  public function getRootPath()
  {
    return $this->rootPath;
  }
//---------------------------------------------------------------------------
  public function getRootUrl()
  {
    return $this->rootUrl;
  }
//---------------------------------------------------------------------------
  public function getLevel()
  {
    return $this->level;
  }
//---------------------------------------------------------------------------
  public function getPageName()
  {  
    return $this->pageName;
  }
//---------------------------------------------------------------------------
  public function isIndex()
  {  
    return $this->isIndex;
  }
//---------------------------------------------------------------------------
  public function getPathParts()
  {
    return $this->pathParts;
  }
//---------------------------------------------------------------------------
  public function __toString()
  {
    return $this->path;
  }
//---------------------------------------------------------------------------
  public function getPathFromCurrent($link)
  {
    $linkParts=split('/',ltrim($link,'\\/'));
    $actualLinkParts=$linkParts;
    $uplevel=$this->level;
    for ($i=0;$i < $this->level; $i++){
      if ($this->pathParts[$i]==$linkParts[$i]) {
        array_shift($actualLinkParts);
        $uplevel--;
      }else break;
    }
    $res=str_repeat('../',$uplevel).implode('/',$actualLinkParts);
    return $res;
  }
}
?>
