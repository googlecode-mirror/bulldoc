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
       'introduction.html' => '��������',
       'installation.html' => '���������',
       'quickstart.html' => '������� ������',
       'content' => '�����',
       'layout' => '����������',
       'export.html' => '�������',
       'appendix' => '����������'
      ), 'Correct collection obtained');
    
    $this->assertEqual($evaluationResult['curTitle'],'����������','Correct current topic obtained');
    $this->assertEqual($evaluationResult['prev'],array('href' => '../export.html','title' => '�������'),'Correct previous data obtained');
    $this->assertEqual($evaluationResult['next'],array('href' => 'topic_index.html','title' => '���������� ���������'),'Correct next data obtained');
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
       'topic_index.html' => '���������� ���������',
       'authors.html' => '������',
       'license.html' => '��������',
       'similar.html' => '�������',
       'roadmap.html' => '����� �� ��������'
      ), 'Correct collection obtained');
    
    $this->assertEqual($evaluationResult['curTitle'],'������','Correct current topic obtained');
    $this->assertEqual($evaluationResult['prev'],array('href'=>'topic_index.html', 'title'=>'���������� ���������'),'Correct previous data obtained');
    $this->assertEqual($evaluationResult['next'],array('href' => 'license.html', 'title' => '��������'),'Correct next data obtained');
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
        array('href'=>'introduction.html','title'=>'��������','level'=>0),
        array('href'=>'installation.html','title'=>'���������','level'=>0),
        array('href'=>'quickstart.html','title'=>'������� ������','level'=>0),
        array('href'=>'content/index.html','title'=>'�����','level'=>0),
        array('href'=>'content/bookshelf.html','title'=>'������� �����','level'=>1),
        array('href'=>'content/toc.html','title'=>'���������','level'=>1),
        array('href'=>'content/text.html','title'=>'�����','level'=>1),
        array('href'=>'layout/index.html','title'=>'����������','level'=>0),
        array('href'=>'layout/theme.html','title'=>'����','level'=>1),
        array('href'=>'layout/system_settings.html','title'=>'��������� ���������','level'=>1),
        array('href'=>'export.html','title'=>'�������','level'=>0),
        array('href'=>'appendix/index.html','title'=>'����������','level'=>0),
        array('href'=>'appendix/topic_index.html', 'title'=>'���������� ���������','level'=>1),
        array('href'=>'appendix/authors.html','title'=>'������','level'=>1),
        array('href'=>'appendix/license.html','title'=>'��������','level'=>1),
        array('href'=>'appendix/similar.html','title'=>'�������','level'=>1),
        array('href'=>'appendix/roadmap.html','title'=>'����� �� ��������','level'=>1)
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
