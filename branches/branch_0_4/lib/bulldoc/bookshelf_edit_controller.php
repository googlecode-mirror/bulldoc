<?php
require_once('coleso/controllers/pagecontroller.lib.php');
require_once('coleso/form/form.php');

class bookshelfPositionEditController extends colesoGeneralController
{
  protected $data;
  protected $bookshelfFields=array('title', 'source', 'dest', 'theme', 'hideOnBookShelf', 'separator', 'key');
  protected $bookFields=array('title', 'author', 'copyright', 'site');
  
  public function run()
  {
    if ($this->Environment->method=='GET') {
      return new colesoControllerExecResult($this->renderForm());
    }
    
    $errStatus=false;
    if ($this->Environment->method=='POST') {
      if (!colesoToken::checkValid()) throw new Exception('Invalid token');
      try {
        $bookshelfData=$this->loadData($this->bookshelfFields);
        $bookData=$this->loadData($this->bookFields,array('title'=>'book_title'));
        if (!$bookshelfData['key']) throw new bookCreationException('You should assign keyword value');
        $this->saveBookshelfPosition($bookshelfData);
        if(!isset($bookshelfData['separator'])) $this->saveBook($bookData,$bookshelfData['key']);
      } catch (bookCreationException $e) {
        $data=array('errMessage'=>$e->getMessage());
        $data=array_merge($data,$bookshelfData);
        $bookData['book_title']=$bookData['title'];
        unset($bookData['title']);
        $data=array_merge($data,$bookData);
        
        return new colesoControllerExecResult($this->renderForm($data));
      }
      return new colesoControllerRedirect ('./');
    }
    throw new Exception('Invalid HTTP method');
  }
//-----------------------------------------------------------------------------------
  protected function renderForm($data=null)
  {
    $form=new colesoForm(colesoApplication::getConfigVal('/bulldoc/systemTemplates').'bookshelf_postion_editform.tpl.phtml');
    if ($data) {
      $form->addFieldValues($data);
    } else {
      $form->fields['book_title'] = 'My Book';
      $form->fields['author'] = 'Author';
      $form->fields['copyright'] = 'Me';
      $form->fields['site'] = 'www.mysite.com';
    }

    $form->addListHash('themesList',$this->buildThemeList());

    $shelfTemplateFile=colesoApplication::getConfigVal('/bulldoc/systemTemplates').'bookshelf.tset.phtml';
    $template=new colesoPHPTemplateSet($shelfTemplateFile);
    $html=$template->parseItem('layout',array('content'=>$form->render(),'skipTitle'=>true));
    return $html;
  }
//-----------------------------------------------------------------------------------
  protected function buildThemeList()
  {
    $themePath=colesoApplication::getConfigVal('/bulldoc/themeDir');
    $list=array();
    foreach (new DirectoryIterator($themePath) as $fileInfo) {
      if($fileInfo->isDir() && 
        $fileInfo->getFilename()!='system' &&
        $fileInfo->getFilename()!='.svn' &&
        !$fileInfo->isDot()
      ) $list[$fileInfo->getFilename()]=$fileInfo->getFilename();
    }    
    return $list;
  }
//-----------------------------------------------------------------------------------
  protected function loadData($fieldList,$map=array())
  {
    $data=array();
    foreach($fieldList as $field){
      $key = isset($map[$field])? $map[$field]:$field;
      $data[$field] = $this->Environment->getPostVar($key);
    }
    return $data;
  }
//-----------------------------------------------------------------------------------
  protected function saveBookshelfPosition($data)
  {
    $key=$data['key'];
    unset ($data['key']);
    $books=$this->parameters->bookLoader->getBooks();
    if (isset($books[$key])) throw new bookCreationException('Book allready exists');

    if (isset($data['separator'])) {
      $data['separatorTitle']=$data['title'];
      unset($data['title']);
      unset($data['separator']);
    }
    
    $text="$key:\n";
    foreach ($data as $key=>$val) if ($val) $text.="  $key: $val\n";
    $text.="\n";
    $bookshelfFile=colesoApplication::getConfigVal('/bulldoc/bookshelfConfig');
    file_put_contents($bookshelfFile,"\n$text",FILE_APPEND);
  }
//-----------------------------------------------------------------------------------
  protected function saveBook($data,$bookKey)
  {
    $text='';
    foreach ($data as $key=>$val) $text.="$key: $val\n";
    $bookDir=colesoApplication::getConfigVal('/bulldoc/source')."$bookKey/";
    if (file_exists($bookDir)) throw new bookCreationException('This folder allready exists: '.$bookDir);

    mkdir ($bookDir,0777);
    mkdir ($bookDir.'pages',0777);
    file_put_contents($bookDir.'book_data.yml',$text);
  }
}
?>
