<?php
require_once('coleso/phptemplate/phptemplate.php');
require_once ('spyc/spyc.php5');
require_once(dirname(__FILE__).'/build_on_toc.php');
require_once(dirname(__FILE__).'/default_highlight.php');
require_once(dirname(__FILE__).'/theme_manager.php');
require_once(dirname(__FILE__).'/toc_manipulation.php');
require_once(dirname(__FILE__).'/index_builder.php');

//===========================================================================

class renderDocPage  extends buildOnToc
{
  private $themeManager;
  private $navTemplate;
  private $layoutTemplateFile;
  private $masterLayoutTemplateFile;
  private $sourcePath;
  private $mode='server'; //static | server
                          //generate static version or serverside rendering on the fly (server)
                          
  function __construct($book,$themeManager=null)
  {
    parent::__construct($book);
    if ($themeManager) $this->themeManager=$themeManager;
    else $this->themeManager=new bulldocDecoThemes();
    
    $navTemplateFile=$this->themeManager->getFile('template/navigation.tset.phtml');
    $this->navTemplate=new colesoPHPTemplateSet($navTemplateFile);
    $this->navTemplate->setGlobalVar('outputMode',$this->book->getOutputMode());
    
    $this->obtainLayoutTemplateFile();
    $this->masterLayoutTemplateFile=$this->themeManager->getFile('template/master_layout.tpl.phtml');
    $this->sourcePath=$this->book->getPagesSourcePath();
  }
//---------------------------------------------------------------------------
  protected function obtainLayoutTemplateFile()
  {
    $outputMode=$this->book->getOutputMode();
    if ($outputMode=='html_single') {
      $this->layoutTemplateFile=$this->themeManager->getFile('template/singlepage_content.tpl.phtml');
    } elseif ($outputMode=='html') { 
      $this->layoutTemplateFile=$this->themeManager->getFile('template/html_layout.tpl.phtml');
    } elseif ($outputMode=='chm') {
      $this->layoutTemplateFile=$this->themeManager->getFile('template/chm_layout.tpl.phtml');
    } else colesoErrDie('Invalid Output Mode:'.$outputMode);
  }
//---------------------------------------------------------------------------
  public function setMode($mode)
  {
    $this->mode=$mode;
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
  public function getSection($pathBuilder)
  {
    return $this->structureHolder->getPageSection($pathBuilder);
  }
//---------------------------------------------------------------------------
  public function renderPage($path, $content=null)
  {
    try {
      $pathBuilder=new pathBuilder($path);
      if (is_null($content)) {
        $content=$this->getContent($pathBuilder);
        $editMode=false;
      } else $editMode=true;
      
      $data=array(
        'content' => $content,
        'editMode' => $editMode
        );
      $this->buildGeneralData($pathBuilder,$data);
      $this->buildMenuData($pathBuilder,$data);
      $this->buildUrlData($pathBuilder,$data);
      return $this->renderPageData($data);
    } catch (pageNotFoundException $e){
      colesoErrDie($e->getMessage());
    }
  }
//---------------------------------------------------------------------------
  protected function renderPageData($data)
  {
    $bodyContent=colesoPHPTemplate::parseFile($this->layoutTemplateFile, $data);
    if ($this->book->getOutputMode()!='html_single'){
      $data['content']=$bodyContent;
      return colesoPHPTemplate::parseFile($this->masterLayoutTemplateFile, $data);
    } else {
      return $bodyContent;
    }
  }
//---------------------------------------------------------------------------
  private function buildGeneralData($pathBuilder,&$data)
  {
    $data['path']=(string) $pathBuilder;
    $data['bookTitle']=$this->bookTitle;
    $data['bookData']=$this->book->getBookData();
    $data['level']=$pathBuilder->getLevel();
    $data['pageData']=$this->structureHolder->getPage($pathBuilder);
    $data['mode'] = $this->mode;
    $data['outputMode'] = $this->book->getOutputMode();
  }
//---------------------------------------------------------------------------
  private function buildMenuData($pathBuilder,&$data)
  {
    $section=$this->getSection($pathBuilder);
    $menu=$this->buildMenu($section['curSection'],$pathBuilder);
    $prev_next=$this->correctNavigationIfEmpty($section['parentSection'],$pathBuilder,$menu);
    
    $data['next']=$prev_next['next'];
    $data['prev']=$prev_next['prev'];
    $data['menu']=$menu['menu'];
    $data['upTitle']=$section['upTitle'];
    $data['curTitle']=$section['curTitle'];
  }
//---------------------------------------------------------------------------
  private function buildUrlData($pathBuilder,&$data)
  {
    $data['rootURL']=$pathBuilder->getRootUrl();
    $data['rootPath']=$pathBuilder->getRootPath();
    $data['bookShelfURL']='../'.$pathBuilder->getRootPath();
    $data['assetsURL']=$this->getAssetsUrl($pathBuilder);
    $data['upLevelLink']=$pathBuilder->isIndex()? '../index.html':'index.html';
    $data['editURL']=$pathBuilder->isIndex()? 'index.html.edit' : $pathBuilder->getPageName().'.edit';
    $data['editTocURL']=str_replace('index.html','',$pathBuilder->getRootUrl()).'.edit';
    $this->buildCustomStyleUrl($data);
  }
//---------------------------------------------------------------------------
  private function buildCustomStyleUrl(&$data)
  {
    if ($this->book->getBookStyle()) $data['customStyleUrl']=$data['rootPath'].'book_style.css';
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
  private function correctNavigationIfEmpty($parentSection,$pathBuilder,$menu)
  {
    $prev=$next='';
    if (!is_array($parentSection)) return array('next'=>$menu['next'],'prev'=>$menu['prev']);
    $sectionIterator=new contentTreeSectionIterator(new ArrayIterator($parentSection));
    foreach ($sectionIterator as $key=>$topic){
      if ($sectionIterator->key()==$pathBuilder->getParentName()) {
        $prev=$sectionIterator->getPrevTopicData();
        $next=$sectionIterator->getNextTopicData();
        if (is_array($next)) $next['href']='../'.$next['href'];
        if (is_array($prev)) $prev['href']='../'.$prev['href'];
        break;
      }
    }
    $next=($menu['next'])? $menu['next']:$next;
    $prev=($menu['prev'])? $menu['prev']:$prev;
    return array('next'=>$next,'prev'=>$prev);
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
    $templateClass=colesoApplication::getConfigVal('/bulldoc/textProcessingClass');
    $t=new $templateClass;
    $t->setOutputMode($this->book->getOutputMode());
    $fileName=$this->sourcePath.$pathBuilder;
    $params=array('root'=>$pathBuilder->getRootPath(),'path'=>$pathBuilder,'structure'=>$this->structureHolder);
    
    $pageData=$this->structureHolder->getPage($pathBuilder);
    
    if ($pathBuilder->isIndex()){
      $content='';
      if (file_exists($fileName)) $content.=$t->parseFile($fileName,$params);
      $content.=$this->navTemplate->parseItem('toc',$this->buildIndex($pathBuilder));
      return $content;
    } elseif ($pageData['type']=='index'){
      $myIndexBuilder=new IndexBuilder($this->sourcePath,$this->structureHolder->getToc());
      $myIndexRender=new IndexRender($myIndexBuilder, $this->book, $this->themeManager);
      return $myIndexRender->render($pathBuilder);
    } elseif (file_exists($fileName)){
      return $t->parseFile($fileName,$params);
    } else {
      return colesoApplication::getMessage('bulldoc','underconstruction');
    }
  }
//---------------------------------------------------------------------------
  private function buildIndex($pathBuilder)
  {
    $basePath=dirname((string) $pathBuilder);
    if ($basePath=='.') $basePath='';
    else $basePath.='/';
    
    $sectionData=$this->structureHolder->getPageSection($pathBuilder,'current');
    $section=$sectionData['curSection'];
    $level=$sectionData['level'];
    $iterator =  new RecursiveIteratorIterator(new contentTreeRecursiveIterator($section),RecursiveIteratorIterator::SELF_FIRST);
    $html='';
    foreach($iterator as $topic){
      if ($level!=-1 && $iterator->getDepth() > $level-1) continue;
      $topic['href']=ltrim($iterator->getPath().'/'.$topic['href'],'\\/');
      $topic['level']=$iterator->getDepth();
      $topic['path']= $basePath.$topic['href'];
      $html.=$this->navTemplate->parseItem('toc_topic',$topic);
    }
    return $html;
  }
}
?>
