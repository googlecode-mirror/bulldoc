<?php
if (!defined('ALLTESTRUNNER') && !defined('TESTGROUPRUNNER')) {
  require_once('../../../test_bootstrap.php');
}
require_once('bulldoc/edit_controller.php');

//======================================================================================
class EditControllerTestCase extends colesoBaseTest
{
    
  function __construct()
  {
    $this->cachePath='bulldoc/edit_controller/';
    $this->sourceFilesDir=dirname(__FILE__).'/support/fixture/';

    parent::__construct('Edit controller');

    colesoApplication::setConfigVal('/bulldoc/systemTemplates',dirname(__FILE__).'/support/templates/system/');
    colesoApplication::setConfigVal('/bulldoc/bookshelfConfig',dirname(__FILE__).'/support/bookshelf.yml');
    colesoApplication::setConfigVal('/bulldoc/textProcessingClass','docTemplateSet');
    colesoApplication::setConfigVal('/bulldoc/rootIndexLevel',2);

    colesoApplication::setConfigVal('/bulldoc/themeDir',dirname(__FILE__).'/support/workshop/themes/');
    colesoApplication::setConfigVal('/bulldoc/themeUrl','support/workshop/themes/');
    colesoApplication::setConfigVal('/bulldoc/output',dirname(__FILE__).'/support/workshop/output/');
    
    colesoApplication::setConfigVal('/bulldoc/source',$this->fullCachePath.'workshop/source/');
  }
//-------------------------------------------
  function testShowForm() 
  {
    $bookLoader=new bookLoader();
    $parameters=array(
      'bookKey'=> 'bulldoc_book',
      'bookLoader' => $bookLoader,
      'book' => $bookLoader->getBook('bulldoc_book'),
      'url' => 'introduction.html'
    );
    $controller=new pageEditController();
    $controller->addParameters($parameters);
    
    $result=$controller->run();
    
    $examinator=$this->getContentExaminator($result->content);
    echo $examinator->display();
    $this->assertEqual($examinator->getMD5(),'775d7a5093e502c39fb794f12240a059','Edit form rendered');
  }
//-------------------------------------------
  function testSaveForm() 
  {
    $env=colesoApplication::getEnvironment();
    $env->method='POST';
    $env->requestURL='http://bulldoc/mybook/introduction.html';
    $env->setPostVar('content','my new text');
    $env->setTestTokenReq(); 
    
    $bookLoader=new bookLoader();
    $parameters=array(
      'bookKey'=> 'bulldoc_book',
      'bookLoader' => $bookLoader,
      'book' => $bookLoader->getBook('bulldoc_book'),
      'url' => 'introduction.html'
    );
    $controller=new pageEditController();
    $controller->addParameters($parameters);
    
    $result=$controller->run();
    
    $this->assertIsA($result,'colesoControllerRedirect');
    $this->assertEqual($result->headers,array('Location: http://bulldoc/mybook/introduction.html?message=ok'));
  }
//-------------------------------------------
  function testShowTocForm() 
  {
    $env=colesoApplication::getEnvironment();
    $env->method='GET';
    $bookLoader=new bookLoader();
    $parameters=array(
      'bookKey'=> 'bulldoc_book',
      'bookLoader' => $bookLoader,
      'book' => $bookLoader->getBook('bulldoc_book'),
      'url' => 'introduction.html'
    );
    $controller=new tocEditController();
    $controller->addParameters($parameters);
    $result=$controller->run();

    $examinator=$this->getContentExaminator($result->content);
    echo $examinator->display();
    $this->assertEqual($examinator->getMD5(),'299e680e2c64e630be8a0ffe97f7b8cb','TOC Edit form rendered');
  }
}

if (! defined('TESTGROUPRUNNER')) {
  define('TESTGROUPRUNNER', true);
  $test=new EditControllerTestCase;
  $test->run(new ShowPasses());
}
?>
