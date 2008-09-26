<?php
require_once ('coleso/controllers/frontcontroller.lib.php');
require_once ('coleso/phptemplate/phptemplate.php');
require_once (dirname(__FILE__).'/edit_controller.php');

class bulldocFrontController extends colesoFrontController
{
  private $url;
  private $bookLoader;
  private $media_ext=array('jpg','jpeg','gif','png','txt');

//-----------------------------------------------------
  public function __construct()
  {
    parent::__construct();
    $this->bookLoader=new bookLoader();
  }
//-----------------------------------------------------
  protected function setup()
  {
    $this->addCommand('bookshelf',__FILE__,'bookShelfController');
    $this->addCommand('bookrender',__FILE__,'bookRenderController');
    $this->addCommand('media',__FILE__,'bookMediaController');
    $this->addCommand('edit_page',__FILE__,'pageEditController');
    $this->addCommand('edit_toc',__FILE__,'tocEditController');
    $this->addCommand('index_rebuild',__FILE__,'inexRebuild');
  }
//-----------------------------------------------------
  protected function getAction()
  {
    $this->action='bookrender';
    if($this->isMedia()) $this->action='media';
    if ($this->url=='') $this->action='bookshelf';
    if (preg_match('/\.edit$/',$this->url)) $this->action='edit_page';
    if (preg_match('/\/\.edit$/',$this->url)) $this->action='edit_toc';
    if (preg_match('/[^\w]_index_rebuild$/',$this->url)) $this->action='index_rebuild';
  }
//-----------------------------------------------------
  protected function checkRedirectToIndex()
  {
    $path_parts = pathinfo($this->url);
    $ext=isset($path_parts['extension'])? $path_parts['extension']:'';
    return ($ext=='' && $this->url!='' && $this->url!='/' && $path_parts['basename']!='_index_rebuild');
  }
//-----------------------------------------------------------
  private function isMedia()
  {
    $path_parts = pathinfo($this->url);
    $ext=isset($path_parts['extension'])? $path_parts['extension']:'';
    return in_array($ext,$this->media_ext);
  }
//-----------------------------------------------------------
  protected function getBookKey()
  {
    $urlParts=split('/',ltrim($this->url,'\\/'));
    return $urlParts[0];
  }
//-----------------------------------------------------------
  protected function getPageUrl()
  {
    $urlParts=split('/',ltrim($this->url,'\\/'));
    array_shift($urlParts);
    $url=implode('/',$urlParts);
    
    //if editing is enabled
    $url=preg_replace('/\.edit$/','',$url);
    
    return $url;
  }
//-----------------------------------------------------
  protected function buildEnvironment()
  {
    $this->url=$this->Environment->getReqVar('colesoRequestPath');
    if ($this->checkRedirectToIndex()) {
      $this->Environment->redirect(
        colesoApplication::getConfigVal('/bulldoc/rootUrl').
        rtrim($this->url,'\\/').'/index.html');
    }
    $this->getAction();
    $this->parameters=array(
     'bookLoader' => $this->bookLoader,
     'url' => $this->getPageUrl()
     );
       
    if ($this->action!='bookshelf') {
      $bookKey=$this->getBookKey();
      $this->parameters['bookKey']=$bookKey;
      $book=$this->bookLoader->getBook($bookKey);
      $this->parameters['book']=$book;
      if ($book->getBookLanguage()) {
        colesoApplication::setLanguage($book->getBookLanguage(),$book->getBookLocale());
        colesoApplication::loadMessages('bulldoc/messages');
      }
    }
  }
}


//==============================================================================================
class inexRebuild extends colesoGeneralController
{
  public function run()
  {
    if ($this->Environment->getReqVar('result')=='ok'){
      $themeParams=$this->parameters['book']->getBookTheme();
      $html=colesoPHPTemplate::parseFile(
        $themeParams['themePath'].'/template/message.tpl.phtml',
        array(
          'bookTitle'=> $this->parameters['book']->getBookTitle(),
          'assetsURL'=>$themeParams['themeUrl'].'/web/',
          'message'=>colesoApplication::getMessage('bulldoc','index_cleared'),
          'errstatus'=>'success',
          'backLink'=> colesoApplication::getConfigVal('/bulldoc/rootUrl').
                       $this->parameters['bookKey'].'/'.
                       $this->Environment->getReqVar('path')
          )
        );
      return new colesoControllerExecResult($html);
    } else {
      $cacheFile=colesoApplication::getConfigVal('/system/cacheDir')."bulldoc/{$this->parameters['bookKey']}/book_index.cache";
      if (file_exists($cacheFile)) unlink ($cacheFile);
      return new colesoControllerRedirect($this->parameters->url.'?result=ok&path='.$this->Environment->getReqVar('path'));
    }
  }
}


//==============================================================================================
class bookController extends colesoGeneralController
{
  //assumed the following parameters:
  //'bookKey' -- book name
  //'book' -- book object
  //'bookLoader' -- book factory object
  //'url' -- relative path from book's root

  protected function getUrlExt()
  {
    $path_parts = pathinfo($this->parameters->url);
    return isset($path_parts['extension'])? $path_parts['extension']:'';
  }
}

//==============================================================================================
class bookRenderController extends bookController
{
  public function run()
  {
    $render=$this->parameters->book->getBookRenderer();
    $html=$render->renderPage($this->parameters->url);
    return new colesoControllerExecResult($html);
  }
}

//==============================================================================================
class bookShelfController extends bookController
{
  public function run()
  {
    $shelfTemplateFile=colesoApplication::getConfigVal('/bulldoc/systemTemplates').'bookshelf.tset.phtml';
    $template=new colesoPHPTemplateSet($shelfTemplateFile);
    $res='';
    $books=$this->parameters->bookLoader->getBooks();
    foreach($books as $book=>$data){
      if (isset($data['separatorTitle'])) {
        $res.=$template->parseItem('separator',array('title'=>$data['separatorTitle']));
      } else {
        $data['key']=$book;
        $res.=$template->parseItem('item',$data);
      }
    }
    $html=$template->parseItem('layout',$res);
    return new colesoControllerExecResult($html);
  }
}

//==============================================================================================
class bookMediaController extends bookController
{
  private $media_mime=array('jpg'=>'image/jpeg',
                            'jpeg'=>'image/jpeg',
                            'gif'=>'image/gif',
                            'png'=>'image/png',
                            'txt'=>'text/plain');
  
  public function run()
  {
    $headers=array('Content-Type: '.$this->media_mime[$this->getUrlExt()]);
    $content=file_get_contents($this->parameters->book->getBookSource().'pages/'.$this->parameters->url);
    return new colesoControllerExecResult($content,$headers);
  }
}

?>