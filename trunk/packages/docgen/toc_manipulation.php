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
    if ($cdUp)  $this->params['href']='../'.$this->params['href'];
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
    $this->rootIndexLevel=colesoApplication::getConfigVal('/docgen/rootIndexLevel');
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
  public function getPageSection($pathBuilder,$mode='indexUp')
  {
    $curTitle='';
    $level=-1;
    $upTitle=array();
    $parts=$pathBuilder->getPathParts();
    $page=array_pop($parts);
    if (count($parts)==0) $level=$this->rootIndexLevel;
    if ($pathBuilder->isIndex() && $mode=='indexUp'){
      if (count($parts)==0) return $this->getTopSection();
      else $page=array_pop($parts);
    }
    $upSection=$curSection=$curPool=$this->toc;
    foreach ($parts as $chapter){
      if (!isset($curPool[$chapter])) throw new pageNotFoundException($pathBuilder);
      array_unshift($upTitle,$curPool[$chapter]['title']);
      $curSection=$curPool[$chapter]['topics'];
      if ($mode!='indexUp' && isset($curPool[$chapter]['level'])) $level=$curPool[$chapter]['level']; 
      $curPool=$curSection;
    }
    if (!isset($curSection[$page]) && $mode=='indexUp') throw new pageNotFoundException('wrong path: '.$pathBuilder);
    if ($mode=='indexUp') $curTitle=is_array($curSection[$page])?$curSection[$page]['title']:$curSection[$page];
    return array('curSection'=>$curSection,'upTitle'=>$upTitle,'curTitle'=>$curTitle,'level'=>$level);
  }
//------------------------------------------------------------------------
  private function getTopSection()
  {
    return array(
      'curSection'=>false,
      'upTitle'=>array(),
      'curTitle'=>colesoApplication::getSysMessage('docgen','toc'),
      'level'=>colesoApplication::getConfigVal('/docgen/rootIndexLevel')
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
