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
        'introduction.html' => '��������',
        'installation.html' => '���������',
        'quickstart.html' => '������� ������',
        'content' => array ('type' => 'chapter',
                            'title' => '�����',
                            'topics' => array ('bookshelf.html' => '������� �����',
                                                'toc.html' => '���������',
                                                'text.html' => '�����')
                            ),
        'layout' => array ('type' => 'chapter',
                           'title' => '����������',
                           'topics' => array ('theme.html' => '����',
                                              'system_settings.html' => '��������� ���������')
                           ), 
        'export.html' => '�������',
        'appendix' => array ('type' => 'chapter',
                             'title' => '����������',
                             'topics' => array (
                                                'topic_index.html' => array('type'=>'index', 'title'=>'���������� ���������'),
                                                'authors.html' => '������',
                                                'license.html' => '��������',
                                                'similar.html' => '�������',
                                                'roadmap.html' => '����� �� ��������')
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
                       'upTitle' => array (),'curTitle' => '�������', 'level' => 2 ),
                       'Correct top section obtained');
  
    $this->assertEqual($topSection,$structHolder->getPageSection(new pathBuilder('/index.html')),
                       'TopSection obtained for /quickstart.html');
    
    $themeSampleSection=array('curSection' => array ('theme.html' => '����',
                                                     'system_settings.html' => '��������� ���������'),
                              'parentSection' => $this->getTOCarraySample(),
                              'upTitle' => array (0 => '����������'),
                              'curTitle' => '����',
                              'level' => -1 );
    
    $this->assertEqual($themeSampleSection,$structHolder->getPageSection(new pathBuilder('/layout/theme.html')),
                       'Correct Section Info obtained for /layout/theme.html');

    $layoutSampleSection=array('curSection' => $this->getTOCarraySample(),
                              'parentSection' => null,
                              'upTitle' => array (),
                              'curTitle' => '����������',
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
        'introduction.html' => '��������',
        'installation.html' => '���������',
        'quickstart.html' => '������� ������',
        'content' => array ('type' => 'chapter',
                            'title' => '�����',
                            'topics' => array ('bookshelf.html' => '������� �����',
                                                'toc.html' => '���������',
                                                'text.html' => '�����')
                            ),
        'layout' => array ('type' => 'chapter',
                           'title' => '����������',
                           'topics' => array ('theme.html' => '����',
                                              'system_settings.html' => '��������� ���������')
                           ), 
        'export.html' => '�������',
        'appendix' => array ('type' => 'chapter',
                             'title' => '����������',
                             'topics' => array (
                                                'topic_index.html' => array('type'=>'index', 'title'=>'���������� ���������'),
                                                'authors.html' => '������',
                                                'license.html' => '��������',
                                                'similar.html' => '�������',
                                                'roadmap.html' => '����� �� ��������')
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
