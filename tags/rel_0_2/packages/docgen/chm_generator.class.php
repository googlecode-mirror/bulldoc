<?php
require_once(dirname(__FILE__).'/build_on_toc.class.php');

class buildCHMToc extends buildOnToc
{
//---------------------------------------------------------------------------
  public function setBookTitle($title)
  {
    $this->bookTitle=$title;
  }
//---------------------------------------------------------------------------
  public function buildTOC()
  {
    $pathBuilder=new pathBuilder('/');
    $sectionData=$this->structureHolder->getPageSection($pathBuilder,'current');
    $section=$sectionData['curSection'];
    $level=0;
    
    $iterator =  new RecursiveIteratorIterator(new contentTreeRecursiveIterator($section),RecursiveIteratorIterator::SELF_FIRST);
    $file_list='';
    $html='<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<HTML>
<HEAD>
<meta name="GENERATOR" content="Microsoft&reg; HTML Help Workshop 4.1">
<!-- Sitemap 1.0 -->
</HEAD><BODY><UL><LI><OBJECT type="text/sitemap">
		                <param name="Name" value="Обложка">
		                <param name="Local" value="index.html">
		               </OBJECT>';
    foreach($iterator as $topic){
      $href=ltrim($iterator->getPath().'/'.$topic['href'],'\\/');
      $file_list.="$href\n";
      $title=$topic['title'];
      
      if ($iterator->getDepth()>$level){
        $html.='<UL>';
        $level=$iterator->getDepth();
      }
      
      if ($iterator->getDepth()<$level){
        $html.='</UL>';
        $level=$iterator->getDepth();
      }
      
      $icon=basename($href)=='index.html'?'':'<param name="ImageNumber" value="11">';
      
      $html.='<LI> <OBJECT type="text/sitemap">
		                <param name="Name" value="'.$title.'">
		                <param name="Local" value="'.$href.'">
                    '.$icon.'
		               </OBJECT>';

    }
    $html=$html.'</UL></BODY></HTML>';
    
    $file_list="[OPTIONS]
Compatibility=1.1 or later
Compiled file={$this->bookKey}.chm
Contents file={$this->bookKey}.hhc
Default Font=Verdana,8,204
Default topic=index.html
Display compile progress=No
Language=0x419 Russian
Title={$this->bookTitle}


[FILES]
$file_list

[INFOTYPES]

";
    
    
    return array($html,$file_list);
  }
}
?>
