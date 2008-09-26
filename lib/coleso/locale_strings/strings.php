<?php
function colesoStrToLower($str)
{
  $localeData=colesoApplication::getConfigVal('/system/localeData');
  if (isset($localeData['isMultiByte'])){
    return mb_strtolower ($str, $localeData['encoding']);
  }
  return strtolower($str);
}
//-----------------------------------------------------------------
function colesoStrToUpper($str)
{
  $localeData=colesoApplication::getConfigVal('/system/localeData');
  if (isset($localeData['isMultiByte'])){
    return mb_strtoupper ($str, $localeData['encoding']);
  }
  return strToUpper($str);
}
//-----------------------------------------------------------------
function colesoSubstr($str, $start,$length=null)
{
  $localeData=colesoApplication::getConfigVal('/system/localeData');
  if (isset($localeData['isMultiByte'])){
    if (is_null($length)) $length=mb_strlen($str);
    return mb_substr ($str, $start, $length, $localeData['encoding']);
  }

  if (is_null($length)) $length=strlen($str);
  return substr ($str, $start, $length);
}
?>
