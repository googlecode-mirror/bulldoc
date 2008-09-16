<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id$
***********************************************************************************/

//==============================================================
//Output md5 with content
//==============================================================
class colesoContentDebug
{
  private $md5str;
  private $content;
  private $stripURLS=null;
  protected $mode;

//----------------------------------------------
  public function __construct($content,$mode='debug',$stripSystemPaths=null)
  {
    $this->stripURLS=$stripSystemPaths;
    $contentStrippedURLs=$this->stripSystemURLS($content);
    //echo '<pre>'.htmlentities($contentStrippedURLs).'</pre>';
    $this->md5str=md5($contentStrippedURLs);
    $this->content=$content;
    $this->mode=$mode;
  }
//----------------------------------------------
  public function stripSystemURLS($content)
  {
    $contentStrippedURLs=$content;
    if (!is_null($this->stripURLS)){
      foreach ($this->stripURLS as $sysName => $stripStr){
        $contentStrippedURLs=str_replace($stripStr,$sysName,$contentStrippedURLs);
      }
    }
    return $contentStrippedURLs;
  }
//----------------------------------------------
  public function getMD5()
  {
    return $this->md5str;
  }
//----------------------------------------------
  function getContent()
  {
    return $this->content;
  }
//----------------------------------------------
  function display()
  {
    if ($this->mode!='debug') return '';
    return '<br>'.$this->getMD5().'<br>'.$this->getContent();
  }

}

//===================================================================================
function colesoEchoDebugHeader()
{
    $sysImgPath=colesoApplication::getConfigVal('/coleso_cms/imgURL');
    $jsPath=colesoApplication::getConfigVal('/coleso_cms/jsURL');
    $richeditPath=colesoApplication::getConfigVal('/system/richedit/url');
    ?>
<script type='text/javascript' language='JavaScript'>
colesoSystemImagesPath='<?php echo $sysImgPath; ?>';
colesoSystemRicheditPath='<?php echo $richeditPath; ?>';
colesoSkipCookie='skip';
</script>
<script language='JavaScript' src="<?php echo $jsPath; ?>calendar_popup.js">
</script>
<script language='JavaScript' src="<?php echo $jsPath; ?>coleso_core.js">
</script>
<style type='text/css'>
div.calendar
{
  position: absolute;
  visibility: hidden;
  width: 150px;
  background-color: #F5EFD6;
}

div.formerr,span.formerrLabel {
font-weight: bold;
color: red;
}
</style>
    <?php
}


//------------------------------------------------------
function colesoTestShowReport($sql,$dbDriver)
{
    $out="<table style='background-color: #dddddd;margin: 5px 0 20px 0'>";
    $stmt=$dbDriver->query($sql);
    $firstFlag=true;
    while ($row=$stmt->fetch()){
      if ($firstFlag){
        $out.="<tr>";
        foreach($row as $k => $v) {
          $out.= "<td style='background-color: #aaaaaa;padding: 3px;'><b>$k</b></td>";
        }
        $out.= "</tr>";
        $firstFlag=false;
      }
      $out.="<tr>";
      reset($row);
      foreach($row as $field) $out.="<td style='background-color: white;padding: 3px;'>".$field."</td>";
      $out.= "</tr>";
    }
    $out.="</table>";
    if ($firstFlag) return '<p>No records found</p>';
    return $out;
} 
//------------------------------------------------------
class colesoRecordsetExaminator
{
  private $resultMattrix;
  private $renderedHtml;
  private $mode;
  private $dbDriver;

  public function __construct($sql,$mode,$dbDriver=null)
  {
    $this->mode=$mode;
    $this->dbDriver=is_null($dbDriver)? colesoDB::getConnection():$dbDriver;
    $this->renderedHtml=colesoTestShowReport($sql,$this->dbDriver);
    $this->readMattrix($sql);
  }
//------------------------------------------------------
  protected function readMattrix($sql)
  {
    $this->resultMattrix=array();
    $stmt=$this->dbDriver->query($sql);
    $counter=0;
    while ($row=$stmt->fetch()){
      foreach($row as $k => $v) $this->resultMattrix[$counter][$k]=$v;
      $counter++;
    }
  }
//------------------------------------------------------
  public function getRecordsetValue($row,$key)
  {
    return isset($this->resultMattrix[$row][$key])? $this->resultMattrix[$row][$key]: '';
  }
//------------------------------------------------------
  public function getRecordsetRow($row)
  {
    return isset($this->resultMattrix[$row])? $this->resultMattrix[$row] : null;
  }
//------------------------------------------------------
  public function getRecordset()
  {
    return $this->resultMattrix;
  }
//------------------------------------------------------
  public function getMD5()
  {
    return md5($this->renderedHtml);
  }
//------------------------------------------------------
  public function showRecords($comment='')
  {
    if ($this->mode!='debug') return '';
    $out=$comment ? $comment.'<br/>' : '';
    $out.=$this->renderedHtml;
    return $out;
  }
//------------------------------------------------------
  public function showMD5Records($comment='')
  {
    if ($this->mode!='debug') return '';
    return '<br>'.$this->getMD5().'<br>'.$this->showRecords($comment);
  }
//------------------------------------------------------
  public function countRecords()
  {
    return count($this->resultMattrix);
  }
//------------------------------------------------------
  public function findColumn($colName,$value)
  {
    $l=$this->countRecords();
    if ($l==0) return -1;
    for ($i=0; $i < $l;$i++){
      if ($this->resultMattrix[$i][$colName]==$value) return $i;
    }
    return -1;
  }
//------------------------------------------------------
  public function getRecordsetRowByColumn($colName,$value)
  {
    $row=$this->findColumn($colName,$value);
    if ($row==-1) return null;
    return $this->resultMattrix[$row];
  }
}

//------------------------------------------------------
function colesoFormCounterTune($formName,$html,$counterReset=false)
{
  static $counter=0;
  if ($counterReset) $counter=0;
  $counter+=10;
  $newName=$formName.$counter;
  $out=str_replace($formName,$newName,$html);
  $out=str_replace("type=\"hidden\"","type='text' style='background-color: #cccccc'",$out);
  $out=str_replace("type='hidden'","type='text' style='background-color: #cccccc'",$out);
  $out='<div style="background-color: #eeeeee;padding: 5px">'.$out.'</div>';
  return $out;
}
//------------------------------------------------------
function colesoIdCounterTune($idName,$html,$counterReset=false)
{
  static $counter=0;
  if ($counterReset) $counter=0;
  $counter+=10;
  $newName=$idName.$counter;
  $out=str_replace($idName,$newName,$html);
  return $out;
}

?>
