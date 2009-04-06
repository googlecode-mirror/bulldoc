<?php
require_once ('coleso/yaml_load/yaml_load.php');

abstract class buildOnToc
{
  protected $book;
  
  protected $structureHolder;
  protected $bookKey=null;
  protected $bookTitle='';
  
  function __construct($book)
  {
    $this->book=$book;
    
    $this->bookKey=$book->getBookKey();
    $this->bookTitle=$book->getBookTitle();
    $this->structureHolder=$book->getStructureHolder();
  }
//---------------------------------------------------------------------------
  public function getBookData()
  {
    return $this->bookData;
  }
}
