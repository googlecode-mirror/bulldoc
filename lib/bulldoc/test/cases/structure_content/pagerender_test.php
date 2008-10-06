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
    $this->assertEqual($examinator->getMD5(),'7537a3b865459a23672f726a79d5badf','Page rendered');
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
    $this->assertEqual($examinator->getMD5(),'309e6496c9e3dbe6e838233464325e7e','Index rendered');
  }
}

if (! defined('TESTGROUPRUNNER')) {
  define('TESTGROUPRUNNER', true);
  $test=new PageRenderTestCase;
  $test->run(new ShowPasses());
}
?>
