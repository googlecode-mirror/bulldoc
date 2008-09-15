<?php
if (!defined('ALLTESTRUNNER') && !defined('TESTGROUPRUNNER')) {
  require_once('../../test_bootstrap.php');
}

require_once(dirname(__FILE__).'/common_init.php');

//======================================================================================
class StructureHolderTestCase extends colesoBaseTest
{
  function __construct()
  {
    $this->cachePath='bulldoc/structure/';
    //$this->sourceFilesDir=dirname(__FILE__).'/support/fixture/';

    parent::__construct('Structure Holder');
    require_once(dirname(__FILE__).'/config.php');
  }
//-------------------------------------------
  function testArrayLoad() 
  {
    $structHolder=$this->getTestStructure();
    $this->assertEqual(
      $structHolder->getToc(),
      array ( 
        'introduction.html' => 'Введение',
        'installation.html' => 'Установка',
        'quickstart.html' => 'Простой пример',
        'content' => array ('type' => 'chapter',
                            'title' => 'Книга',
                            'topics' => array ('bookshelf.html' => 'Книжная полка',
                                                'toc.html' => 'Структура',
                                                'text.html' => 'Текст')
                            ),
        'layout' => array ('type' => 'chapter',
                           'title' => 'Оформление',
                           'topics' => array ('theme.html' => 'Темы',
                                              'system_settings.html' => 'Системные настройки')
                           ), 
        'export.html' => 'Экспорт',
        'appendix' => array ('type' => 'chapter',
                             'title' => 'Приложения',
                             'topics' => array (
                                                'topic_index.html' => array('type'=>'index', 'title'=>'Предметный указатель'),
                                                'authors.html' => 'Авторы',
                                                'license.html' => 'Лицензия',
                                                'similar.html' => 'Аналоги',
                                                'roadmap.html' => 'Планы по развитию')
                             )
        ),
      'Correct TOC array obtained');
  }
//-------------------------------------------
  function testGetSection()
  {
    $structHolder=$this->getTestStructure();
    $topSection=$structHolder->getPageSection(new pathBuilder('/'));
    $this->assertEqual($topSection,array ('curSection' => false, 'parentSection' => null,
                       'upTitle' => array (),'curTitle' => 'Обложка', 'level' => 2 ),
                       'Correct top section obtained');
  
    $this->assertEqual($topSection,$structHolder->getPageSection(new pathBuilder('/index.html')),
                       'TopSection obtained for /quickstart.html');
    
    $themeSampleSection=array('curSection' => array ('theme.html' => 'Темы',
                                                     'system_settings.html' => 'Системные настройки'),
                              'parentSection' => $this->getTOCarraySample(),
                              'upTitle' => array (0 => 'Оформление'),
                              'curTitle' => 'Темы',
                              'level' => -1 );
    
    $this->assertEqual($themeSampleSection,$structHolder->getPageSection(new pathBuilder('/layout/theme.html')),
                       'Correct Section Info obtained for /layout/theme.html');

    $layoutSampleSection=array('curSection' => $this->getTOCarraySample(),
                              'parentSection' => null,
                              'upTitle' => array (),
                              'curTitle' => 'Оформление',
                              'level' => -1 );

    $this->assertEqual($layoutSampleSection,$structHolder->getPageSection(new pathBuilder('/layout/index.html')),
                       'Correct Section Info obtained for /layout/index.html');
    
    $this->expectException('pageNotFoundException','Not found exception occurs for /strange/notfound.html');
    $error=$structHolder->getPageSection(new pathBuilder('/strange/notfound.html'));
  }
//-------------------------------------------
  function getTOCarraySample()
  {
    return array ( 
        'introduction.html' => 'Введение',
        'installation.html' => 'Установка',
        'quickstart.html' => 'Простой пример',
        'content' => array ('type' => 'chapter',
                            'title' => 'Книга',
                            'topics' => array ('bookshelf.html' => 'Книжная полка',
                                                'toc.html' => 'Структура',
                                                'text.html' => 'Текст')
                            ),
        'layout' => array ('type' => 'chapter',
                           'title' => 'Оформление',
                           'topics' => array ('theme.html' => 'Темы',
                                              'system_settings.html' => 'Системные настройки')
                           ), 
        'export.html' => 'Экспорт',
        'appendix' => array ('type' => 'chapter',
                             'title' => 'Приложения',
                             'topics' => array (
                                                'topic_index.html' => array('type'=>'index', 'title'=>'Предметный указатель'),
                                                'authors.html' => 'Авторы',
                                                'license.html' => 'Лицензия',
                                                'similar.html' => 'Аналоги',
                                                'roadmap.html' => 'Планы по развитию')
                             )
        );
  }
//-------------------------------------------
  function getTestStructure()
  {
    $bookToc=colesoApplication::getConfigVal('/bulldoc/workshopDir').'source/bulldoc_book/toc.yml';
    $loader=new structureHolderLoader($bookToc,'myBook');
    $struct=$loader->getHolder();
    return $struct;
  }
}

if (! defined('TESTGROUPRUNNER')) {
  define('TESTGROUPRUNNER', true);
  $test=new StructureHolderTestCase;
  $test->run(new ShowPasses());
}
?>
