<?php
require_once ('lib/geshi/geshi.php');

class docTemplateSet
{
  private $previous;
  private $structureHolder;
  private $pathBuilder;
  
//---------------------------------------------
  public function parseFile($filename,$params)
  {
    $this->pathBuilder=$params['path'];
    $this->structureHolder=$params['structure'];
    $content=file_get_contents($filename);
    $content=preg_replace_callback('/<cls:link\s+page=[\'"](.*?)[\'"]\s*\/>/i',array($this,'getPageLink'),$content);
    $content=preg_replace_callback('/<cls:(\w+)>(.*?)<\/cls:\w+>/sm',array($this,'highlightMatches'),$content);
    return $content;
  }  
//-------------------------------------------------
  private function getPageLink($matches)
  {
    $link=$matches[1];
    return '<a class="inner" href="'.
           $this->pathBuilder->getPathFromCurrent($link).'">'.
           $this->structureHolder->getSectionTitleByPath($link).'</a>';
  }
//-------------------------------------------------
  private function highlightMatches($matches)
  {
    return $this->highlight(rtrim($matches[2]),$matches[1]);
  }
//-------------------------------------------------
  public function highlight($code,$language='php')
  {
    $geshi =new GeSHi($code, $language);
    return $geshi->parse_code();
  }
}
?>
