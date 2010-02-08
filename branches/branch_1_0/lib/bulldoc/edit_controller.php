<?php
/*
TODO: если содержимое пусто -- удаляем файл
*/

//===========================================================================================================================
require_once (dirname(__FILE__).'/controller.php');
require_once('coleso/token/token.php');
require_once('coleso/common_file_edit/common_file_edit.php');

//===========================================================================================================================
abstract class bulldocFileEditController extends colesoTextFileEditController
{
//------------------------------------------------
  public function run()
  {
    $result=parent::run();
    if (!($result instanceof colesoControllerRedirect)) {
      $result->content=$this->applyLayout($result->content);
    }
    return $result;
  }
//------------------------------------------------
  protected function getSuccessSaveMessage()
  {
    return colesoApplication::getMessage('textFormEdit','save_message');
  }
//------------------------------------------------
  protected function getTemplate()
  {
    return colesoApplication::getConfigVal('/bulldoc/systemTemplates').'editform.tpl.phtml';
  }
//------------------------------------------------
  abstract protected function applyLayout($content);
//------------------------------------------------
  public function buildRedirectAfterSaveURL()
  {
    if ($this->Environment->getPostVar('save_view')){
      return $this->backToViewLink;
    }
    return parent::buildRedirectAfterSaveURL();
  }
}

//===========================================================================================================================
class pageEditController extends bulldocFileEditController
{
  protected $pathBuilder;
//------------------------------------------------
  public function run()
  {
    $this->pathBuilder=new pathBuilder($this->parameters->url);
    $this->title=colesoApplication::getMessage('bulldoc','page_edit_title');
    $this->backToViewLink=$this->pathBuilder->isIndex()? 'index.html' : $this->pathBuilder->getPageName();
    return parent::run();
  }
//------------------------------------------------
  protected function applyLayout($content)
  {
    $render=$this->parameters->book->getBookRenderer();
    $html=$render->renderPage($this->parameters->url,$content);
    return $html;
  }
//------------------------------------------------
  protected function getPageFileName()
  {
    return $this->parameters->book->getBookSource().'pages/'.$this->pathBuilder->__toString();
  }
}

//===========================================================================================================================
class tocEditController extends bulldocFileEditController
{
//------------------------------------------------
  public function run()
  {
    $this->title=colesoApplication::getMessage('bulldoc','toc_edit_title');
    $this->backToViewLink='index.html';
    return parent::run();
  }
//------------------------------------------------
  protected function applyLayout($content)
  {
    $render=$this->parameters->book->getBookRenderer();
    $html=$render->renderPage('/index.html',$content);
    return $html;
  }
//------------------------------------------------
  function getPageFileName()
  {
    return $this->parameters->book->getTocFileName();
  }
}

//===========================================================================================================================
class bookshelfEditController extends bulldocFileEditController
{
//------------------------------------------------
  public function run()
  {
    $this->title=colesoApplication::getMessage('bulldoc','bookshelf_edit_title');
    $this->backToViewLink='./';
    return parent::run();
  }
//------------------------------------------------
  protected function applyLayout($content)
  {
    $shelfTemplateFile=colesoApplication::getConfigVal('/bulldoc/systemTemplates').'bookshelf.tset.phtml';
    $template=new colesoPHPTemplateSet($shelfTemplateFile);
    $html=$template->parseItem('layout',array('content'=>$content,'skipTitle'=>true));
    return $html;    
  }
//------------------------------------------------
  function getPageFileName()
  {
    return colesoApplication::getConfigVal('/bulldoc/bookshelfConfig');
  }
}

?>
