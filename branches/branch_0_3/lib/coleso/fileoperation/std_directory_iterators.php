<?php
//=================================================================================
class eraserIterator extends RecursiveDirectoryIterator
{
  public function erase()
  {
    if ($this->isDot()) return;
    if (!$this->isDir()){
      unlink ($this->getPathname());
    } else {
      rmdir ($this->getPathname());
    }
  }
//--------------------------------------------
  public function getChildren()
  {
    return new self($this->getPathname());
  }
}
//=================================================================================
function directoryClear($path)
{
  if(!file_exists($path)) return;
  $it=new RecursiveIteratorIterator(new eraserIterator($path), RecursiveIteratorIterator::CHILD_FIRST);
  while ($it->valid()){
    $it->erase();
    $it->next();
  }
}

//=================================================================================
function directoryCopy($source,$dest)
{
  $dest=rtrim($dest,'\\/').'/';
  if (!file_exists($dest)) mkdir($dest,0666,true);
  $it=new RecursiveDirectoryIterator($source);
  foreach(new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST) as $file) {
    $filePath=(string) $file;
    if (strstr($file,'.svn')===false) {
      $baseLength=strlen($source);
      $relativePath=ltrim(substr($filePath,$baseLength),'\\/');
      $toPath=$dest.$relativePath;
      if($file->isDir()) {
        if (!file_exists($toPath)) mkdir($toPath,0666,true);
      } else {
        copy($filePath,$toPath);
      }
    }
  }
}

