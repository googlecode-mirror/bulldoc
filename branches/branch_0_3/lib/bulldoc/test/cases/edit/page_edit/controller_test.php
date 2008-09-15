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
    colesoApplication::setConfigVal('/bulldoc/workshopDir',$this->fullCachePath.'workshop/');
    colesoApplication::setConfigVal('/bulldoc/workshopUrl','support/workshop/');
    colesoApplication::setConfigVal('/bulldoc/bookshelfConfig',dirname(__FILE__).'/support/');
    
    colesoApplication::setConfigVal('/bulldoc/textProcessingClass','docTemplateSet');
    colesoApplication::setConfigVal('/bulldoc/rootIndexLevel',2);
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
    $this->assertEqual($examinator->getMD5(),'6624e48a145d0c2cf4ae45f7b880b9fd','Edit form rendered');
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
    $this->assertEqual($examinator->getMD5(),'532cebde66c4d86dea9f43512fd745d9','TOC Edit form rendered');
  }
}

if (! defined('TESTGROUPRUNNER')) {
  define('TESTGROUPRUNNER', true);
  $test=new EditControllerTestCase;
  $test->run(new ShowPasses());
}
?>
