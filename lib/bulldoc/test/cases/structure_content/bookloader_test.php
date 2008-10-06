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
    $sourcePath=colesoApplication::getConfigVal('/bulldoc/source');
    $myBookLoader=new bookLoader();
    
    $this->assertEqual($myBookLoader->getBookSource('bulldoc_book'),
                        $sourcePath.'bulldoc_book/',
                        'Book source is correct');
    
    $this->assertEqual($myBookLoader->getBookTitle('bulldoc_book'),'Bull Doc','Correct book title obtained');
    
    $this->assertEqual($myBookLoader->getBooks(),
      array ('bulldoc_book' => array ('source'=>$sourcePath.'bulldoc_book/',
                                      'title'=>'Bull Doc',
                                      'author' => 'Dmitry Smirnov',
                                      'copyright' => 'H-type, 2008',
                                      'site' => 'www.bulldoc.ru',
                                      'bookShelfTitle' => 'Bull Doc',
                                      'outputMode'=> 'html'
                                      ),
             'bulldoc_chm' => array ('title'=> 'Bull Doc',
                                      'rootIndexLevel' => -1, 
                                      'source' => $sourcePath.'bulldoc_book/',
                                      'dest' => 'bulldoc_chm',
                                      'theme' => 'blueprint',
                                      'outputMode' => 'chm',
                                      'author' => 'Dmitry Smirnov',
                                      'copyright' => 'H-type, 2008',
                                      'site' => 'www.bulldoc.ru',
                                      'bookShelfTitle' => 'BullDoc CHM'                                      
                                      ),
             'bulldoc_site' => array ('title' => 'Bull Doc',
                                      'source' => $sourcePath.'bulldoc_book/',
                                      'dest' => '../../../doc',
                                      'theme' => 'blueprint_site',
                                      'author' => 'Dmitry Smirnov',
                                      'copyright' => 'H-type, 2008',
                                      'site' => 'www.bulldoc.ru',
                                      'bookShelfTitle' => 'BullDoc for Web-Site',                                      
                                      'outputMode'=> 'html'
                                      )
             ),
        'Correct Book list obtained'
      );
  }
//-------------------------------------------
  function testBook()
  {
    $sourcePath=colesoApplication::getConfigVal('/bulldoc/source');
    $outputPath=colesoApplication::getConfigVal('/bulldoc/output');
    $themesPath=colesoApplication::getConfigVal('/bulldoc/themeDir');
    
    $myBookLoader=new bookLoader();
    
    $myBook=$myBookLoader->getBook('bulldoc_book');
    
    $this->assertEqual($myBook->getBookDest(),$outputPath.'bulldoc_book/','Correct Output Dest obtained');
    $this->assertEqual($myBook->getBookKey(),'bulldoc_book','Book key Ok');
    
    $this->assertEqual($myBook->getBookData(),
     array ('source' => $sourcePath.'bulldoc_book/',
      'title' => 'Bull Doc',
      'author' => 'Dmitry Smirnov',
      'copyright' => 'H-type, 2008',
      'site' => 'www.bulldoc.ru',
      'bookShelfTitle' => 'Bull Doc',
      'outputMode' => 'html'
       ),
     'Correct book data array obtained');
    
    $this->assertEqual($myBook->getBookTitle(),'Bull Doc','Book title Ok');
    $this->assertEqual($myBook->getBookSource(),$sourcePath.'bulldoc_book/','Book source Ok');
    $this->assertEqual($myBook->getBookTheme(),
     array('themePath' => $themesPath.'blueprint','themeUrl' => 'support/workshop/themes/blueprint'),
     'Correct Theme data obtained'
     );
    
    $this->assertEqual($myBook->getTocFileName(),$sourcePath.'bulldoc_book/toc.yml','Correct TOC filename obtained');
    
    $this->assertIsA($myBook->getBookRenderer(),'renderDocPage','book rendrer obtained');
  }
}

if (! defined('TESTGROUPRUNNER')) {
  define('TESTGROUPRUNNER', true);
  $test=new BookLoaderTestCase;
  $test->run(new ShowPasses());
}
?>
