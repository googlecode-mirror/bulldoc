<?php

abstract class colesoTextFileEditController extends colesoGeneralController
{
  protected $action;
  protected $title;
  protected $encoding;
  protected $bakToViewLink='#';
  
  protected $useToken=true;
  
  abstract protected function getPageFileName();
  abstract protected function getTemplate();
//------------------------------------------------
  protected function getSuccessSaveMessage()
  {
    return 'Page Saved';
  }
//------------------------------------------------
  public function __construct()
  {
    parent::__construct();
    $this->title='';
    $this->encoding=colesoApplication::getConfigVal('/system/lngEncoding');
  }
//------------------------------------------------
  protected function getAction()
  {
    if ($this->Environment->method=='GET') $this->action='edit_form';
    if ($this->Environment->method=='POST') $this->action='save';
    return;
  }
//------------------------------------------------
  function getPageContent()
  {
    $fileName=$this->getPageFileName();
    return file_exists($fileName)? file_get_contents($fileName):'';
  }
//------------------------------------------------
  function savePageContent($content)
  {
    $fileName=$this->getPageFileName();
    if ($content!='') {
      $dir=dirname($fileName);
      if (!file_exists($dir)) mkdir($dir,0777,true);
      file_put_contents($fileName,$content);
    } else {
      if (file_exists($fileName)) unlink ($fileName);
    }
  }
//------------------------------------------------
  protected function getFormData()
  {
    $data=array();
    $messageStatus=$this->Environment->getGetVar('message');
    if ($messageStatus=='ok') $data['message']=$this->getSuccessSaveMessage();
    $data['content']=htmlspecialchars($this->getPageContent(),ENT_COMPAT,$this->encoding);
    $data['title']=$this->title;
    $data['bakToViewLink']=$this->bakToViewLink;
    
    $data['tokenFieldName']=colesoToken::getTokenKey();
    $data['tokenValue']=colesoToken::getToken();
    
    return $data;
  }
//------------------------------------------------
  function executeEditForm()
  {
    $editformTemplateFile=$this->getTemplate();
    $html=colesoPHPTemplate::parseFile($editformTemplateFile,$this->getFormData());
    return new colesoControllerExecResult($html);
  }
//------------------------------------------------
  function executeSave()
  {
    if ($this->useToken) {
      if (!colesoToken::checkValid()) throw new Exception('Invalid token');
    }
    $content=$this->Environment->getPostVar('content');
    $this->savePageContent($content);
    
    $curURL=$this->Environment->getRequestURL();
    $urlManipulator=new colesoUrlManipulator($curURL);
    $urlManipulator->setVariable('message','ok');
    $redirect=$urlManipulator->buildUrl();
    return new colesoControllerRedirect($redirect);
  }
//------------------------------------------------
  public function run()
  {
    $this->getAction();
    if ($this->action=='edit_form') return $this->executeEditForm();
    if ($this->action=='save') return $this->executeSave();
    return new colesoControllerExecResult();
  }
}
