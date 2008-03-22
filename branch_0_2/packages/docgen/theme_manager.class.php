<?php
class decoThemes
{
  private $themePath;
  private $themeUrl;
  private $packagesAssetsUrl;
  
  public function __construct($themeData=array())
  {
    if (isset($themeData['themePath'])) $this->themePath=rtrim($themeData['themePath'],'\\/').'/';
    else $this->themePath=colesoApplication::getConfigVal('/system/themeDir');
    
    if (isset($themeData['themeUrl'])) $this->themeUrl=rtrim($themeData['themeUrl'],'\\/').'/';
    else $this->themeUrl=colesoApplication::getConfigVal('/system/themeUrl');
    
    $this->packagesAssetsUrl=colesoApplication::getConfigVal('/system/moduleUrl');
  }
//----------------------------------------------------------------------------------
  public function getFile($path)
  {
    $realPath=$this->getRealPath($path);
    if (file_exists($this->themePath.$path)) return $this->themePath.$realPath;
    else return colesoLibrarian::getModule('docgen/'.$path);
  }
//----------------------------------------------------------------------------------
  public function getUrl($url='')
  {
    $realUrl=$this->getRealPath($url);
    if (file_exists($this->themePath.$url)) return $this->themeUrl.$realUrl;
    else return $this->packagesAssetsUrl.'docgen/'.$url;
  }
//----------------------------------------------------------------------------------
  public function getAssetsPath($url='')
  {
    $realUrl=$this->getRealPath($url);
    if (file_exists($this->themePath.$url)) return $this->themePath.$realUrl;
    else return colesoLibrarian::getModule('docgen/'.$url);
  }
//----------------------------------------------------------------------------------
  protected function getRealPath($path)
  {
    $realPath=$path;
    if (!colesoApplication::getConfigVal('/docgen/useStandaloneTheme')) $realPath='docgen/'.$path;
    return $realPath;
  }
}
?>
