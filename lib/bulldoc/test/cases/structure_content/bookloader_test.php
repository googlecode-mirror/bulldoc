<?php
if (!defined('ALLTESTRUNNER') && !defined('TESTGROUPRUNNER')) {
  require_once('../../test_bootstrap.php');
}

require_once(dirname(__FILE__).'/common_init.php');

//======================================================================================
class BookLoaderTestCase extends colesoBaseTest
{
  private $bookLoader;
  
  function __construct()
  {
    $this->cachePath='bulldoc/structure/';
    parent::__construct('Book Loader');

    require_once(dirname(__FILE__).'/config.php');
  }
//-------------------------------------------
  function testBookLoad() 
  {
    $workshopPath=colesoApplication::getConfigVal('/bulldoc/workshopDir');
    $myBookLoader=new bookLoader();
    
    $this->assertEqual($myBookLoader->getBookSource('bulldoc_book'),
                        colesoApplication::getConfigVal('/bulldoc/workshopDir').'source/bulldoc_book/',
                        'Book source is correct');
    
    $this->assertEqual($myBookLoader->getBookTitle('bulldoc_book'),'Bull Doc','Correct book title obtained');
    
    $this->assertEqual($myBookLoader->getBooks(),
      array ('bulldoc_book' => array ('source'=>$workshopPath.'source/bulldoc_book/',
                                      'title'=>'Bull Doc',
                                      'author' => 'Dmitry Smirnov',
                                      'copyright' => 'H-type, 2008',
                                      'site' => 'www.bulldoc.ru',
                                      'bookShelfTitle' => 'Bull Doc'                                      
                                      ),
             'bulldoc_chm' => array ('title'=> 'Bull Doc',
                                      'rootIndexLevel' => -1, 
                                      'source' => $workshopPath.'source/bulldoc_book/',
                                      'dest' => 'bulldoc_chm',
                                      'theme' => 'blueprint_chm',
                                      'buildChm' => 1,
                                      'author' => 'Dmitry Smirnov',
                                      'copyright' => 'H-type, 2008',
                                      'site' => 'www.bulldoc.ru',
                                      'bookShelfTitle' => 'BullDoc CHM'                                      
                                      ),
             'bulldoc_site' => array ('title' => 'Bull Doc',
                                      'source' => $workshopPath.'source/bulldoc_book/',
                                      'dest' => '../../../doc',
                                      'theme' => 'blueprint_site',
                                      'author' => 'Dmitry Smirnov',
                                      'copyright' => 'H-type, 2008',
                                      'site' => 'www.bulldoc.ru',
                                      'bookShelfTitle' => 'BullDoc for Web-Site'                                      
                                      )
             ),
        'Correct Book list obtained'
      );
  }
//-------------------------------------------
  function testBook()
  {
    $workshopPath=colesoApplication::getConfigVal('/bulldoc/workshopDir');
    $myBookLoader=new bookLoader();
    
    $myBook=$myBookLoader->getBook('bulldoc_book');
    
    $this->assertEqual($myBook->getBookDest(),$workshopPath.'output/bulldoc_book/','Correct Output Dest obtained');
    $this->assertEqual($myBook->getBookKey(),'bulldoc_book','Book key Ok');
    
    $this->assertEqual($myBook->getBookData(),
     array ('source' => $workshopPath.'source/bulldoc_book/',
      'title' => 'Bull Doc',
      'author' => 'Dmitry Smirnov',
      'copyright' => 'H-type, 2008',
      'site' => 'www.bulldoc.ru',
      'bookShelfTitle' => 'Bull Doc'                                      
       ),
     'Correct book data array obtained');
    
    $this->assertEqual($myBook->getBookTitle(),'Bull Doc','Book title Ok');
    $this->assertEqual($myBook->getBookSource(),$workshopPath.'source/bulldoc_book/','Book source Ok');
    $this->assertEqual($myBook->getBookTheme(),
     array('themePath' =>$workshopPath.'themes/blueprint','themeUrl' => 'support/workshop/themes/blueprint'),
     'Correct Theme data obtained'
     );
    
    $this->assertEqual($myBook->getTocFileName(),$workshopPath.'source/bulldoc_book/toc.yml','Correct TOC filename obtained');
    
    $this->assertIsA($myBook->getBookRenderer(),'renderDocPage','book rendrer obtained');
  }
}

if (! defined('TESTGROUPRUNNER')) {
  define('TESTGROUPRUNNER', true);
  $test=new BookLoaderTestCase;
  $test->run(new ShowPasses());
}
?>
