<?php
class onlineDocController extends onlineBookLoader
{
  private $url;
  
  protected function getAction()
  {
    if ($this->url=='') return 'index';
    return 'book';
  }
//-----------------------------------------------------------
  protected function getBookKey()
  {
    $urlParts=split('/',ltrim($this->url,'\\/'));
    if (!isset($this->books[$urlParts[0]])) throw new bookNotSpecifiedException();
    return $urlParts[0];
  }
//-----------------------------------------------------------
  protected function getUrl()
  {
    $urlParts=split('/',ltrim($this->url,'\\/'));
    array_shift($urlParts);
    return implode('/',$urlParts);
  }
//-----------------------------------------------------------
  protected function showIndex()
  {
    $shelfTemplateFile=colesoApplication::getConfigVal('/docgen/systemTemplates').'index.tset.phtml';
    $template=new colesoPHPTemplateSet($shelfTemplateFile);
    $res='';
    foreach($this->books as $book=>$data){
      $data['key']=$book;
      $res.=$template->parseItem('item',$data);
    }
    return $template->parseItem('layout',$res);
  }
//-----------------------------------------------------------
  protected function showBook()
  {
    $bookKey=$this->getBookKey();
    $render=$this->getBookRenderer($bookKey);
    return $render->renderPage($this->getUrl(),$this->getBookTitle($bookKey));
  }
//-----------------------------------------------------------
  private function checkUrlNoExt($url)
  {
    $path_parts = pathinfo($url);
    return (!isset($path_parts['extension']) || $path_parts['extension']=='');
  }
//-----------------------------------------------------------
  public function run()
  {
    $env=colesoApplication::getEnvironment();
    $this->url=$env->getReqVar('colesoRequestPath');
    if ($this->checkUrlNoExt($this->url) && $this->url!='' && $this->url!='/') {
      $env->redirect(rtrim($this->url,'\\/').'/index.html');
    }
      
    if ($this->getAction()=='index') return $this->showIndex();
    $res=$this->showBook();
    return $res;
  }
}
?>