<?php
class ShowPasses extends HtmlReporter 
{
  function paintPass($message) 
  {
    $message=str_replace(TESTCASESFOLDER,'',$message);
    parent::paintPass($message);
    print "<span class=\"pass\">Pass</span>: ";
    $breadcrumb = $this->getTestList();
    array_shift($breadcrumb);
    print implode("->", $breadcrumb);
    print "->$message<br />\n";
  }
//--------------------------------------------------------------------------
  function paintFail($message) 
  {
    $message=str_replace(TESTCASESFOLDER,'',$message);
    parent::paintFail($message);
  }
//--------------------------------------------------------------------------
  protected function getCss() 
  {
      return parent::getCss() . ' .pass { color: green; }';
  }
}

