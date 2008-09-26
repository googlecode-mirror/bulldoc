<?php
if (!defined('ALLTESTRUNNER') && !defined('TESTGROUPRUNNER')) {
  require_once('../../test_bootstrap.php');
}

require_once(dirname(__FILE__).'/common_init.php');

//======================================================================================
class StructureIteratorsTestCase extends colesoBaseTest
{
  function __construct()
  {
    $this->cachePath='bulldoc/structure/';
    //$this->sourceFilesDir=dirname(__FILE__).'/support/fixture/';

    parent::__construct('Structure Iterators');

    require_once(dirname(__FILE__).'/config.php');
  }
//-------------------------------------------
  function testIndexSectionIterator() 
  {
    $structHolder=$this->getTestStructure();
    $pathBuilder=new pathBuilder('/appendix/index.html');
    $appendixSection=$structHolder->getPageSection($pathBuilder);
    
    $sectionIterator=new contentTreeSectionIterator(new ArrayIterator($appendixSection['curSection']));
    $sectionIterator->setIsIndex($pathBuilder->isIndex());
    
    $evaluationResult=$this->evaluateSectionIterator($sectionIterator,$pathBuilder);
    $this->assertEqual($evaluationResult['menu_topics'],array(
       'introduction.html' => 'Введение',
       'installation.html' => 'Установка',
       'quickstart.html' => 'Простой пример',
       'content' => 'Книга',
       'layout' => 'Оформление',
       'export.html' => 'Экспорт',
       'appendix' => 'Приложения'
      ), 'Correct collection obtained');
    
    $this->assertEqual($evaluationResult['curTitle'],'Приложения','Correct current topic obtained');
    $this->assertEqual($evaluationResult['prev'],array('href' => '../export.html','title' => 'Экспорт'),'Correct previous data obtained');
    $this->assertEqual($evaluationResult['next'],array('href' => 'topic_index.html','title' => 'Предметный указатель'),'Correct next data obtained');
  }
//-------------------------------------------
  function testSectionIterator() 
  {
    $structHolder=$this->getTestStructure();
    $pathBuilder=new pathBuilder('/appendix/authors.html');
    $appendixSection=$structHolder->getPageSection($pathBuilder);
    
    $sectionIterator=new contentTreeSectionIterator(new ArrayIterator($appendixSection['curSection']));
    $sectionIterator->setIsIndex($pathBuilder->isIndex());
    
    $evaluationResult=$this->evaluateSectionIterator($sectionIterator,$pathBuilder);
    
    $this->assertEqual($evaluationResult['menu_topics'],array(
       'topic_index.html' => 'Предметный указатель',
       'authors.html' => 'Авторы',
       'license.html' => 'Лицензия',
       'similar.html' => 'Аналоги',
       'roadmap.html' => 'Планы по развитию'
      ), 'Correct collection obtained');
    
    $this->assertEqual($evaluationResult['curTitle'],'Авторы','Correct current topic obtained');
    $this->assertEqual($evaluationResult['prev'],array('href'=>'topic_index.html', 'title'=>'Предметный указатель'),'Correct previous data obtained');
    $this->assertEqual($evaluationResult['next'],array('href' => 'license.html', 'title' => 'Лицензия'),'Correct next data obtained');
  }
//-------------------------------------------
  function evaluateSectionIterator($sectionIterator,$pathBuilder) 
  {
    $menutopics=array();
    foreach ($sectionIterator as $key=>$topic){
      $menutopics[$key]=$topic->getTitle();
      if ($sectionIterator->key()==$pathBuilder->getPageName()) {
        $curTitle=$topic->getTitle();
        $prev=$sectionIterator->getPrevTopicData();
        $next=$sectionIterator->getNextTopicData();
      }
    }
    return array(
      'menu_topics'=>$menutopics,
      'curTitle'=>$curTitle,
      'prev'=>$prev,
      'next'=>$next
      );
  }
//-------------------------------------------
  function testRecursiveTreeItarator()
  {
    $structHolder=$this->getTestStructure();
    $pathBuilder=new pathBuilder('/index.html');
    $sectionData=$structHolder->getPageSection($pathBuilder,'current');
    $section=$sectionData['curSection'];
    $level=$sectionData['level'];
    $iterator =  new RecursiveIteratorIterator(new contentTreeRecursiveIterator($section),RecursiveIteratorIterator::SELF_FIRST);
    $tocItems=array();
    foreach($iterator as $topic){
      if ($level!=-1 && $iterator->getDepth() > $level-1) continue;
      $topic['href']=ltrim($iterator->getPath().'/'.$topic['href'],'\\/');
      $topic['level']=$iterator->getDepth();
      $tocItems[]=$topic;
    }
    
    $this->assertEqual($tocItems,
      array(
        array('href'=>'introduction.html','title'=>'Введение','level'=>0),
        array('href'=>'installation.html','title'=>'Установка','level'=>0),
        array('href'=>'quickstart.html','title'=>'Простой пример','level'=>0),
        array('href'=>'content/index.html','title'=>'Книга','level'=>0),
        array('href'=>'content/bookshelf.html','title'=>'Книжная полка','level'=>1),
        array('href'=>'content/toc.html','title'=>'Структура','level'=>1),
        array('href'=>'content/text.html','title'=>'Текст','level'=>1),
        array('href'=>'layout/index.html','title'=>'Оформление','level'=>0),
        array('href'=>'layout/theme.html','title'=>'Темы','level'=>1),
        array('href'=>'layout/system_settings.html','title'=>'Системные настройки','level'=>1),
        array('href'=>'export.html','title'=>'Экспорт','level'=>0),
        array('href'=>'appendix/index.html','title'=>'Приложения','level'=>0),
        array('href'=>'appendix/topic_index.html', 'title'=>'Предметный указатель','level'=>1),
        array('href'=>'appendix/authors.html','title'=>'Авторы','level'=>1),
        array('href'=>'appendix/license.html','title'=>'Лицензия','level'=>1),
        array('href'=>'appendix/similar.html','title'=>'Аналоги','level'=>1),
        array('href'=>'appendix/roadmap.html','title'=>'Планы по развитию','level'=>1)
        ),
      'Correct data obtained'
      );
  }
//-------------------------------------------
  function getTestStructure()
  {
    $myBookLoader=new bookLoader();
    $myBook=$myBookLoader->getBook('bulldoc_book');
    $struct=$myBook->getStructureHolder();
    return $struct;
  }
}

if (! defined('TESTGROUPRUNNER')) {
  define('TESTGROUPRUNNER', true);
  $test=new StructureIteratorsTestCase;
  $test->run(new ShowPasses());
}
?>
