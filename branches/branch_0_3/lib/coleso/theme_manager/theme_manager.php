<?php
class colesoDecoThemes
{
  private $themePath;
  private $themeUrl;
  private $libAssetsUrl;
  private $libPath;
  
  public function __construct($libPath,$themeData=array())
  {
    $this->libPath=rtrim($libPath,'\\/').'/';
    $this->libAssetsUrl=colesoApplication::getConfigVal('/system/libUrlRoot').$this->libPath;
    
    if (isset($themeData['themePath'])) $this->themePath=rtrim($themeData['themePath'],'\\/').'/';
    else $this->themePath=colesoApplication::getConfigVal('/system/themeDir');
    
    if (isset($themeData['themeUrl'])) $this->themeUrl=rtrim($themeData['themeUrl'],'\\/').'/';
    else $this->themeUrl=colesoApplication::getConfigVal('/system/themeUrl');
  }
//----------------------------------------------------------------------------------
  public function getFile($path)
  {
    $realPath=$this->getRealPath($path);
    if (file_exists($this->themePath.$path)) return $this->themePath.$realPath;
    else return colesoApplication::getConfigVal('/system/docRoot').'lib/'.$this->libPath.'deco/'.$path;
  }
//----------------------------------------------------------------------------------
  public function getUrl($url='')
  {
    $realUrl=$this->getRealPath($url);
    if (file_exists($this->themePath.$url)) return $this->themeUrl.$realUrl;
    else return $this->libAssetsUrl.'deco/'.$url;
  }
//----------------------------------------------------------------------------------
  protected function getRealPath($path)
  {
    $realPath=$path;
    if (!colesoApplication::getConfigVal('/system/useStandaloneTheme')) $realPath=$this->libPath.$path;
    return $realPath;
  }
}
?>
