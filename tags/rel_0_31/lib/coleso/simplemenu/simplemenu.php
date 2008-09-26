<?php
class colesoSimpleMenu
{
  var $topicArray;
  var $actionScript;
//--------------------------------------------------------------
  function colesoSimpleMenu($actionURL,$commandsFile='')
  {
    $this->actionScript=$actionURL;
    if ($commandsFile) $this->readMenuTopicsFromFile($commandsFile);
  }
//--------------------------------------------------------------
  function readMenuTopicsFromFile($fileName)
  {
    require($fileName);
    $this->loadArray($MENU);
  }
//--------------------------------------------------------------
  function loadArray($MENU)
  {
    foreach ($MENU as $key => $params){
      if (isset($params['commandType']) && $params['commandType']=='section'){
        $this->addStdCommandRecordSection($key,$params);
      } else {
        $this->addCommand($key,$params);
      }
    }
  }
//--------------------------------------------------------------
  function addCommand($key,$params)
  {
    if (is_string($params)){
      $this->topicArray[$key]['Name']=$params;
      $this->topicArray[$key]['href']='';
    }elseif (isset ($params['link'])) {
      $this->topicArray[$key]['Name']=$params['Title'];
      $this->topicArray[$key]['href']=$params['link'];
    } elseif ($params['menuDisplay']) {
      $this->topicArray[$key]['Name']=$params['Title'];
      $this->topicArray[$key]['href']=$this->actionScript.$key;
    } else {
      $this->menuHiddenTopics[$key]['Name']=$params['Title'];
    }
  }
//--------------------------------------------------------------
  function addStdCommandRecordSection($keyword,$params)
  {
    $this->menuHiddenTopics[$keyword.'new']['Name']='Новый объект';
    $this->menuHiddenTopics[$keyword.'edit']['Name']='Свойства объекта';

    $this->topicArray[$keyword.'list']['Name']=$params['Title'];
    $this->topicArray[$keyword.'list']['href']=$this->actionScript.$keyword.'list';
  }
//--------------------------------------------------------------
  function getChapterTitle($curTopic)
  {
    if (isset($this->topicArray[$curTopic]['Name'])){
      return $this->topicArray[$curTopic]['Name'];
    } else {
      $hiddenTopic=isset($this->menuHiddenTopics[$curTopic]['Name'])? $this->menuHiddenTopics[$curTopic]['Name']:'';
      return $hiddenTopic;
    }
  }
//--------------------------------------------------------------
  function makeMenu($templateFile,$curTopic)
  {
    $html='';
    $templateParser = new colesoPHPTemplateSet($templateFile);
    $firstFlag=true;
    foreach($this->topicArray as $k => $v){
        $data=$v;
        $data['firstFlag']=$firstFlag;
        $firstFlag=false;
        if ($k==$curTopic){
          $html.=$templateParser->parseItem('active_topic',$data);
        } else {
          $html.=$templateParser->parseItem('regular_topic',$data);
        }
    }
    return $html;
  }
} //Class

//=======================================================================================
class colesoSQLMenu
{
  var $templateEngine;
  var $dbConn;
  var $noEscape=false;
  var $htmlResultsArray;
  var $idField;
  var $curID;

  
//------------------------------------
  function colesoSQLMenu($templateFile=NULL)
  {
    if ($templateFile) $this->templateEngine= new colesoPHPTemplateSet($templateFile);
    $this->dbConn=& colesoDB::getConnection();
    $this->htmlResultsArray=array();
  }
//------------------------------------
  function renderSQL($sql,$idField,$curID)
  {
    $this->idField=$idField;
    $this->curID=$curID;
    $this->assignResultsList();
    $this->dbConn->perform_query($sql);
    while($row=$this->dbConn->fetch_row_assoc()){
      $row=$this->preprocessFields($row);
      $row=$this->escapeFields($row);
      $this->renderRow($row);
    }
  }
//------------------------------------
  function assignResultsList()
  {
    $ia=func_get_args ();
    foreach ($ia as $f) $this->htmlResultsArray[$f]='';
  }
//------------------------------------
  function initResultArray()
  {
    foreach ($this->htmlResultsArray as $k=>$v){
      $this->htmlResultsArray[$k]='';
    }
  }
//------------------------------------
  function renderRow($row)
  {
    reset($this->htmlResultsArray);
    foreach ($this->htmlResultsArray as $k=>$v){
      $section=$k.'_row';
      if ($row[$this->idField]==$this->curID) $section.='_cur';
      $this->htmlResultsArray[$k].=$this->templateEngine->parseItem($section,$row);
    }
  }
//------------------------------------
  function escapeFields($row)
  {
    if ($this->noEscape) return $row;
    foreach ($row as $k => $v){
        $row[$k]=htmlspecialchars($v);
    }
    return $row;
  }
//------------------------------------
  function setTemplateEngine(& $enginePtr)
  {
    $this->templateEngine=& $enginePtr;
  }
//------------------------------------
  function setGlobalData($data)
  {
    $this->templateEngine->setGlobalData($data);
  }
//------------------------------------
  function preprocessFields($row)
  {
    //virtual
    return $row;
  }
}
?>