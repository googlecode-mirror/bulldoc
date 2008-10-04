<?php
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
  private $parentName='';
  
  public function __construct($path,$mode='server')
  {
    $this->mode=$mode;
    $path=(string) $path;
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
    
    if (count($this->pathParts) > 1) $this->parentName=$this->pathParts[count($this->pathParts)-2];
  }
//---------------------------------------------------------------------------
  public function getStaticAssetsUrl($fileName='')
  {
    return $this->rootPath.'_assets/'.$fileName;
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
  public function getParentName()
  {  
    return $this->parentName;
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
