<?php
if (!defined('ALLTESTRUNNER') && !defined('TESTGROUPRUNNER')) {
  require_once('../../test_bootstrap.php');
}

require_once(dirname(__FILE__).'/common_init.php');
require_once('bulldoc/index_builder.php');

//======================================================================================
class IndexBuilderTestCase extends colesoBaseTest
{
  private $saveCachePath;
  
  function __construct()
  {
    $this->cachePath='bulldoc/structure/';
    //$this->sourceFilesDir=dirname(__FILE__).'/support/fixture/';

    parent::__construct('Index Builder');
    require_once(dirname(__FILE__).'/config.php');
  }
//--------------------------------------------------------
  public function setUp()
  {
    parent::setUp();
    $this->saveCachePath=colesoApplication::getConfigVal('/system/cacheDir');
    colesoApplication::setConfigVal('/system/cacheDir',$this->fullCachePath);
  }
//--------------------------------------------------------
  public function tearDown()
  {
    colesoApplication::setConfigVal('/system/cacheDir',$this->saveCachePath);
    
    parent::tearDown();
  }  
//-------------------------------------------
  function getIndexBuilder()
  {
    $myBookLoader=new bookLoader();
    $myBook=$myBookLoader->getBook('bulldoc_book');
    $sourcePath=$myBook->getBookRenderer()->getSourcePath();
    $toc=$myBook->getBookRenderer()->getToc();
    $myIndexBuilder=new IndexBuilder($sourcePath,$toc);

    return $myIndexBuilder;
  }
//-------------------------------------------
  function testSinglePageProcessing()
  {
    $myIndexBuilder=$this->getIndexBuilder();
    $myIndexBuilder->indexPage('quickstart.html','Быстрый старт');
    
    $this->assertEqual($myIndexBuilder->getIndexArray(),
      array(
        'новый проект' => array (0 => array('path' =>'quickstart.html', 'title' => 'Быстрый старт')),
        'пример проекта' => array (0 => array('path' =>'quickstart.html', 'title' => 'Быстрый старт')),
        'быстрый старт' => array (0 => array('path' =>'quickstart.html', 'title' => 'Быстрый старт'))
        ),
      'Single page index built successfully');
  }
//-------------------------------------------
  function testBookProcessing()
  {
    $myIndexBuilder=$this->getIndexBuilder();
    $myIndexBuilder->buildIndex();
    
    $this->assertEqual($myIndexBuilder->getIndexArray(),
      array(
        'новый проект' => array(0 => array('path' => 'quickstart.html', 'title' => 'Простой пример'),
                                1 => array('path' =>'content/bookshelf.html', 'title' => 'Книжная полка')),
        'пример проекта' => array (0 => array('path' => 'quickstart.html', 'title' => 'Простой пример')),
        'быстрый старт' => array (0 => array('path' => 'quickstart.html', 'title' => 'Простой пример')),
        'оглавление' => array (0 => array('path' => 'content/toc.html', 'title' => 'Структура')),
        'работа с книгой' => array (0 => array('path' => 'content/toc.html', 'title' => 'Структура'),
                                    1 => array('path' => 'content/text.html', 'title' => 'Текст')),
        'содержание' => array (0 => array('path' => 'content/toc.html', 'title' => 'Структура'),
                                    1 => array('path' => 'content/text.html', 'title' => 'Текст')),
        'работа с текстом' => array (0 => array('path' => 'content/text.html', 'title' => 'Текст')) 
        ),
      'Book index built successfully');
  }
//-------------------------------------------
  function checkArrayEq($arr1,$arr2)
  {
    while (list($key1,$val1)=each($arr1)){
      list($key2,$val2)=each($arr2);
      if ($key2!=$key1) die("$key2!=$key1");
      if (is_array($val1)) {
        if (!is_array($val2)) {
          print_r ($val1);
          print_r ($val2);
          die('array type mismatch');
        }
        $this->checkArrayEq($val1,$val2);
      } else {
        if ($val2!=$val1) die("$val2!=$val1");
      }
    }
  }
//-------------------------------------------
  function testRenderIndexPage()
  {
    $this->assertFalse(file_exists($this->getCacheFileName()),'No Cache file exists');

    $myIndexRender=$this->getIndexRender();
    $html=$myIndexRender->render('appendix/topic_index.html');

    $examinator=$this->getContentExaminator($html);
    echo $examinator->display();
    $this->assertEqual($examinator->getMD5(),'49532f66be514aeb4436b8418a1fda49','Index rendered');

    $this->assertTrue(file_exists($this->getCacheFileName()),'Cache file exists');

    $this->touchCacheFile();
    $myIndexRender=$this->getIndexRender();
    $html=$myIndexRender->render('appendix/topic_index.html');

    $examinator=$this->getContentExaminator($html);
    echo $examinator->display();
    $this->assertEqual($examinator->getMD5(),'f1a5b0bf8b5cbab2df8ec4ca0a2063a0','Index loaded from CACHE');
  }
//-------------------------------------------
  function testRenderCHMIndexPage()
  {
    $myIndexRender=$this->getIndexRender();
    $html=$myIndexRender->renderCHMIndex();

    $examinator=$this->getContentExaminator($html);
    echo $examinator->display();
    $this->assertEqual($examinator->getMD5(),'84305f1c0d4341706bfb42fccd0e33da','CHM Index built');
  }
//-------------------------------------------
  function getIndexRender()
  {
    $myBookLoader=new bookLoader();
    $theme=new bulldocDecoThemes($myBookLoader->getBook('bulldoc_book')->getBookTheme());
    $myIndexRender=new IndexRender($this->getIndexBuilder(),$myBookLoader->getBook('bulldoc_book'),$theme);
    return $myIndexRender;
  }
//-------------------------------------------
  function getCacheFileName()
  {
    return colesoApplication::getConfigVal('/system/cacheDir')."bulldoc/bulldoc_book/book_index.cache";
  }
//-------------------------------------------
  function touchCacheFile()
  {
    $cacheFile=$this->getCacheFileName();
    $rawdata=file_get_contents ($cacheFile);
    $topic_index=unserialize($rawdata);
    $topic_index['Loaded from CACHE']=array(0=>array('path'=>'Path To Cache','title'=>'Cached Content'));
    file_put_contents ($cacheFile, serialize($topic_index));
  }
}

if (! defined('TESTGROUPRUNNER')) {
  define('TESTGROUPRUNNER', true);
  $test=new IndexBuilderTestCase;
  $test->run(new ShowPasses());
}
?>
