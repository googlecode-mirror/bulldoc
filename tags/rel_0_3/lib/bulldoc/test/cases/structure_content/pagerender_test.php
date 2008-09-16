<?php
if (!defined('ALLTESTRUNNER') && !defined('TESTGROUPRUNNER')) {
  require_once('../../test_bootstrap.php');
}

require_once(dirname(__FILE__).'/common_init.php');

//======================================================================================
class PageRenderTestCase extends colesoBaseTest
{
  private $bookLoader;
  
  function __construct()
  {
    $this->cachePath='bulldoc/structure/';
    parent::__construct('Page Render');

    require_once(dirname(__FILE__).'/config.php');
  }
//-------------------------------------------
  function testPageRender() 
  {
    $bookKey='bulldoc_book';
    $myBookLoader=new bookLoader();
    $myBook=$myBookLoader->getBook('bulldoc_book');
    $render=$myBook->getBookRenderer();
    $html=$render->renderPage('/layout/theme.html');
    
    $examinator=$this->getContentExaminator($html);
    echo $examinator->display();
    $this->assertEqual($examinator->getMD5(),'84191db3a4a1579a047489848a2b5921','Page rendered');
  }
//-------------------------------------------
  function testIndexRender() 
  {
    $bookKey='bulldoc_book';
    $myBookLoader=new bookLoader();
    $myBook=$myBookLoader->getBook('bulldoc_book');
    $render=$myBook->getBookRenderer();
    $html=$render->renderPage('/layout/index.html');

    $examinator=$this->getContentExaminator($html);
    echo $examinator->display();
    $this->assertEqual($examinator->getMD5(),'952064e35e906bbd1b0e49e2d6e34d57','Index rendered');
  }
}

if (! defined('TESTGROUPRUNNER')) {
  define('TESTGROUPRUNNER', true);
  $test=new PageRenderTestCase;
  $test->run(new ShowPasses());
}
?>
