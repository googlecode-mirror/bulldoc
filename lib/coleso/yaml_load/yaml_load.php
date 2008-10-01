<?php
class colesoYMLLoader
{
  public static function load($file,$cachefile)
  {
    $file=self::getRealFilename($file);
    $path_parts=pathinfo($file);
    if ($path_parts['extension']=='php') $result=include($file);
    else $result=self::loadYML($file,$cachefile);
    return $result;
  }
//----------------------------------------------------------------------------------
  private static function loadYML($file,$cacheFile)
  {
    if (file_exists($cacheFile) && (filemtime ($cacheFile) > filemtime ($file))){
      $rawdata=file_get_contents ($cacheFile);
      $result=unserialize($rawdata);
    }else {
      $result = Spyc::YAMLLoad($file);
      $cacheDir=dirname($cacheFile);
      if (!file_exists($cacheDir)) mkdir ($cacheDir,0777,true);
      file_put_contents ($cacheFile, serialize($result));
    }
    return $result;
  }
//----------------------------------------------------------------------------------
  private static function getRealFilename($file)
  {
    $path_parts=pathinfo($file);
    if (
      isset($path_parts['extension']) && 
      ($path_parts['extension']=='php' || $path_parts['extension']=='yml')) return $file;
    if (file_exists($file.'.php')) return $file.'.php';
    if (file_exists($file.'.yml')) return $file.'.yml';
    colesoErrDie('No file detected');
  }
}  
