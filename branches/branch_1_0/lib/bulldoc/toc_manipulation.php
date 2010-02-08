<?php
class contentTreeSectionIterator extends CachingIterator
{
  private $prev=null;
  private $prevKey=null;
  private $isIndex=false;
  
//-----------------------------------
  public function setIsIndex($isIndex)
  {
    $this->isIndex=$isIndex;
  }
//-----------------------------------
  public function next()
  {
    $this->prev=$this->current();
    $this->prevKey=$this->key();
    parent::next();
  }
//-----------------------------------
  public function current()
  {
    return new treeTopic($this->key(),parent::current(),$this->isIndex);
  }
//-----------------------------------
  public function getNextTopicData()
  {
    if ($this->isIndex) return $this->getDefaultTopicData();
    if ($this->hasNext()) $nextTopic=new treeTopic($this->getInnerIterator()->key(),$this->getInnerIterator()->current());
    else $nextTopic=new treeTopic();
    return $nextTopic->getData();
  }
//-----------------------------------
  public function getPrevTopicData()
  {
    if ($this->prev) return $this->prev->getData();
    return null;
  }
//---------------------------------------------------------------------------
  private function getDefaultTopicData()
  {
    $topic=parent::current();
    $topics=$topic['topics'];
    list($key,$val)=each ($topics);
    $defaultTopic=new treeTopic($key,$val);
    return $defaultTopic->getData();
  }
}

//====================================================================================
class treeTopic
{
  private $params;
  private $isEmpty=false;
  
  //cdUp will add '../' to the links
  public function __construct($key=null,$topic=null,$cdUp=false)
  {
    $this->isEmpty=is_null($key);
    $this->params=array();
    $this->params['href']=$key;
    if (is_null($topic)) return;
    
    if (!isset($topic['type'])) $topic['type']='page';
    if ($topic['type']=='chapter') $this->params['href'].='/index.html';
    if ($cdUp)  $this->cdUp();
    if(is_array($topic)) $this->params['title']=$topic['title'];
    else $this->params['title']=$topic;
  }
//---------------------------------------------------------------------------
  public function getData()
  {
    if ($this->isEmpty) return null;
    return $this->params;
  }
//---------------------------------------------------------------------------
  public function getTitle()
  {
    if ($this->isEmpty) return null;
    return $this->params['title'];
  }
//---------------------------------------------------------------------------
  public function cdUp()
  {
    $this->params['href']='../'.$this->params['href'];
  }
}


//====================================================================================
class contentTreeRecursiveIterator extends RecursiveArrayIterator
{
  private $path;
  
  public function __construct($array,$flags = 0,$path='')
  {
    parent::__construct($array,$flags = 0);
    $this->path=$path;
  }
//-------------------------------------------------------------------
  public function current()
  {
    $topic= new treeTopic($this->key(),parent::current());
    return $topic->getData();
  }
//-------------------------------------------------------------------
  public function hasChildren ()
  {
    $section=parent::current();
    return (is_array($section) && isset($section['topics']));
  }
//-------------------------------------------------------------------
  public function getPath()
  {
    return $this->path;
  }
//-------------------------------------------------------------------
  public function getChildren ()
  {
    $section=parent::current();
    return new self($section['topics'],0,$this->path.'/'.$this->key());
  }
}


//====================================================================================
class structureHolder
{
  private $toc;
  private $rootIndexLevel;
  
  public function __construct($toc)
  {
    $this->toc=$toc;
    $this->rootIndexLevel=colesoApplication::getConfigVal('/bulldoc/rootIndexLevel');
  }
//------------------------------------------------------------------------
  public function setRootIndexLevel($level)
  {  
    $this->rootIndexLevel=$level;
  }
//------------------------------------------------------------------------
  public function getToc()
  {
    return $this->toc;
  }
//------------------------------------------------------------------------
  public function getPage($pathBuilder)
  {
    $curPool=$this->toc;
    $parts=$pathBuilder->getPathParts();
    $curNode=array('type'=>'top_index');
    foreach ($parts as $filename){
      if ($filename=='index.html') break;
      if (!isset($curPool[$filename])) throw new pageNotFoundException($pathBuilder->__toString());
      $curNode=$curPool[$filename];
      if (!isset($curNode['type'])) $curNode['type']='page';
      if ($curNode['type']=='chapter') $curPool=$curNode['topics'];
    }
    return $curNode;
  }
//------------------------------------------------------------------------
//indexUp for menu (we need to up one level)
//current for toc (index) -- just use currnet section. level parameter will be set
//------------------------------------------------------------------------
  public function getPageSection($pathBuilder,$mode='indexUp')
  {
    $upTitle=array();
    $parts=$pathBuilder->getPathParts();
    $page=array_pop($parts); //get current page from path parts
    if (count($parts)==0) $level=$this->rootIndexLevel; //get level value for root node
    
    //if $pathBuilder->isIndex() && $mode=='indexUp' 
    //return topSection, for index.html located in the root node, 
    //othrewise pop path part again (we just got index.html from previous pop)
    if ($pathBuilder->isIndex() && $mode=='indexUp'){
      if (count($parts)==0) return $this->getTopSection();
      else $page=array_pop($parts);
    }
    
    $section=$this->processPathParts($parts,$mode,$pathBuilder);
    $section['curTitle']=$this->getCurTitle($section,$page,$mode); //Current page's title
    return $section;
  }
//------------------------------------------------------------------------
  private function getCurTitle($section,$page,$mode)
  {
    $curTitle='';
    if ($mode=='indexUp') {
      $curPage=$section['curSection'][$page];
      $curTitle=is_array($curPage)?$curPage['title']:$curPage;
    }
    return $curTitle;
  }
//------------------------------------------------------------------------
  private function processPathParts($parts,$mode,$pathBuilder)
  {
    $upSection=$curSection=$curPool=$this->toc;
    $parentSection=null;
    $upTitle=array();
    $level=-1;
    foreach ($parts as $chapter){
      if (!isset($curPool[$chapter])) throw new pageNotFoundException($pathBuilder->__toString());
      array_unshift($upTitle,$curPool[$chapter]['title']);
      $parentSection=$curSection;
      $curSection=$curPool[$chapter]['topics'];
      if ($mode!='indexUp' && isset($curPool[$chapter]['level'])) $level=$curPool[$chapter]['level']; 
      $curPool=$curSection;
    }
    return array('curSection'=>$curSection, //current section, containing current page
                 'parentSection'=>$parentSection, //parent section
                 'upTitle'=>$upTitle, //Array of parent section's titles
                 'level'=>$level); //Level of toc in case of index page
  }
//------------------------------------------------------------------------
  private function getTopSection()
  {
    return array(
      'curSection'=>false,
      'parentSection'=>null,
      'upTitle'=>array(),
      'curTitle'=>colesoApplication::getMessage('bulldoc','toc'),
      'level'=>colesoApplication::getConfigVal('/bulldoc/rootIndexLevel')
      );
  }
//------------------------------------------------------------------------
  public function getSectionTitleByPath($path)
  {
    $pathBuilder=new pathBuilder($path);
    $section=$this->getPageSection($pathBuilder);
    return $section['curTitle'];
  }
}
?>
