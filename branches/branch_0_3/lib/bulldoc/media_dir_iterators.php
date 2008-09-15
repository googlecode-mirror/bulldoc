<?php
class mediaItem
{
  private $fromPath;
  private $toPath;
  
  public function __construct($fileName,$sourceBasePath,$destBasePath)
  {
    $this->fromPath=$fileName;
    $this->toPath=$destBasePath.$this->getRelativePath($fileName,$sourceBasePath);
  }
//--------------------------------------------
  private function getRelativePath($fileName,$sourceBasePath)
  {
    $baseLength=strlen($sourceBasePath);
    $relativePath=substr($fileName,$baseLength);
    return $relativePath;
  }
//--------------------------------------------
  public function copy()
  {
    $dirname=dirname($this->toPath);
    if (!file_exists($dirname)) mkdir ($dirname,0666,true);
    copy($this->fromPath,$this->toPath);
  }
//--------------------------------------------
  public function getFilename()
  {
    return $this->fromPath;
  }
}

//=================================================================================
class mediaDirectoryRecursiveIterator extends RecursiveDirectoryIterator
{
  private $sourceBasePath;
  private $destBasePath;
  
  public function __construct($path,$sourceBasePath,$destBasePath)
  {
    $this->sourceBasePath=$sourceBasePath;
    $this->destBasePath=$destBasePath;
    parent::__construct($path);
  }
//--------------------------------------------
  public function current()
  {
    $fileName=realpath($this->getPathName());
    return new mediaItem($fileName,$this->sourceBasePath,$this->destBasePath);
  }
//--------------------------------------------
  public function getChildren()
  {
    return new self($this->getPathname(),$this->sourceBasePath,$this->destBasePath);
  }
}

//=================================================================================
class mediaDirectoryRecursiveIteratorIterator extends RecursiveIteratorIterator
{
  public function __construct($sourcePath,$destBasePath)
  {
    $sourcePath=realpath($sourcePath);
    $sourcePath=rtrim($sourcePath,'\\/').'/';
    $destBasePath=realpath($destBasePath);
    $destBasePath=rtrim($destBasePath,'\\/').'/';
    parent::__construct(new mediaDirectoryRecursiveIterator($sourcePath,$sourcePath,$destBasePath));
  }
}

//=================================================================================
class mediaDirectoryFilterIterator extends FilterIterator
{
  protected $ext = array(); //extensions acceptable

  public function __construct($sourcePath,$destBasePath, $ext = 'php')
  {
    $this->ext = explode(',', $ext);
    parent::__construct(new  mediaDirectoryRecursiveIteratorIterator(realpath($sourcePath),realpath($destBasePath)));
  }
//--------------------------------------------------------
  public function accept()
  {
    $item = $this->getInnerIterator();
    if (!$item->isFile()) return true;
    $fileExt=pathinfo($item->getFilename(), PATHINFO_EXTENSION);
    return in_array($fileExt, $this->ext);
  }
}
?>
