<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id: form.lib.php 235 2007-07-13 12:00:15Z hamster $
***********************************************************************************/

require_once ("coleso/phptemplate/phptemplate.php");
require_once ("coleso/token/token.php");


class colesoForm extends colesoPHPStringTemplate
{
	var $formname;
	var $fields;
	var $formListPool;
  
  protected $errors=array();

	//Data for Stream Forms output
	var $index=-1;


//-----------------------------------------------------
  function colesoForm($templateFile)
  {
  //Constructor

    $this->fields=array();
    $this->getDateListHashes();
    $this->read_template($templateFile);
  }
//-----------------------------------------------------
  function getDateListHashes()
  {
    $this->formListPool=array();
    $this->formListPool['_frm_sys_date_years']=colesoApplication::getConfigVal('/form/yearList');
    $this->formListPool['_frm_sys_date_months']=colesoApplication::getConfigVal('/form/monthList');
    $this->formListPool['_frm_sys_date_days']=colesoApplication::getConfigVal('/form/dayList');
  }
//-----------------------------------------------------
  function setYearsList($yFrom,$yTo)
  {
    $this->addListHash('_frm_sys_date_years',colesoFormControl::formGetYearHash($yFrom,$yTo));
  }
//-----------------------------------------------------
  function addListHash($hashName,$listHash)
  {
    if (is_array($listHash)) $listHash=new colesoDataset($listHash);
    $this->formListPool[$hashName]=$listHash;
  }
//-----------------------------------------------------
  function addListPool($listPool)
  {
    foreach ($listPool as $class=>$list){
      $this->addListHash($class,$list);
    }
  }
//-----------------------------------------------------
  function initdata_array()
  {
    //implyed arbitrary amount of string parameters
    $ia=func_get_args ();
    foreach ($ia as $f) $this->fields[$f]='';
  }
//-----------------------------------------------------
  function read_template($tfile)
  {
    $this->template=file_get_contents($tfile);
  }
//-----------------------------------------------------
  function render()
  {
		reset ($this->fields);
		$this->fields['_index']=$this->index;
		$this->fields['_formname']=$this->formname;
		if ($this->index!=-1){
		  $this->fields['_parity']=$this->index % 2;
		}
		$html_source=$this->parse($this->fields);
    return $html_source;
  }
//-----------------------------------------------------
  function setErrors($errors)
  {
    $this->errors=$errors;
    if (!$errors['status']){
      foreach ($errors['errMessages'] as $k=>$v) {
        if (is_array($v)){
          if (count($v)) $this->errors['errMessages'][$k]=implode('. ',$v);
        } elseif ($v) $this->errors['errMessages'][$k]=$v;
      }
    }
  }
//-----------------------------------------------------
  function addFieldValues($values)
  {
    $this->fields=array_merge($this->fields,$values);
  }
//-----------------------------------------------------
  function makeFormName($formName)
  {
  	if (isset($this->formname)) $formName=$this->formname;
  	else $this->formname=$formName;
	  return "name='$formName' id='$formName'";
  }
//-----------------------------------------------------
  function errText($name,$label='')
  {
    $res='';
    if (isset($this->errors['errMessages'][$name])){
      $res=$label? $label:$this->errors['errMessages'][$name];
    }
    return $res;
  }
//-----------------------------------------------------
  function errLabel()
  {
    $params=$this->buildWidgetParamArray(func_get_args ());
    $name=$params['name'];
    $fieldLabel=isset($params['fieldLabel'])? $params['fieldLabel'].': ':'';
    $decorationTag=isset($params['decorationTag'])? $params['decorationTag']:'span';
    $label=isset($params['label'])? $params['label']:'';
    $class=isset($params['class'])? $params['class']:'formerrLabel' ;
    $out=$params['out'];
    $res='';
    if ($errText=$this->errText($name,$label)){
      $res="<$decorationTag class='$class' $out>".$fieldLabel.$errText."</$decorationTag>";
    }
    return $res;
  }
//-----------------------------------------------------
  function isError($name)
  {
    return isset($this->errors['errMessages'][$name]);
  }  
//-----------------------------------------------------
  function getErrorStatus()
  {
    return isset($this->errors['status'])? $this->errors['status']:true;
  }  
//-----------------------------------------------------
  function token()
  {
    $tokenFieldName=colesoToken::getTokenKey();
    $tokenValue=colesoToken::getToken();
    $out="<input name='$tokenFieldName' type='hidden' value='$tokenValue'  />";
    return $out;
  }
//-----------------------------------------------------
  function html($s)
  {
    return htmlspecialchars($s, ENT_QUOTES);
  }
//-----------------------------------------------------
  function field()
  {
    $params=$this->buildWidgetParamArray(func_get_args ());
    return $this->processWidget('field',$params);
  }
//-----------------------------------------------------
  function select()
  {
    $params=$this->buildWidgetParamArray(func_get_args ());
    return $this->processWidget('select',$params);
  }
//-----------------------------------------------------
  function date()
  {
    $params=$this->buildWidgetParamArray(func_get_args ());
    return $this->processWidget('date',$params);
  }
//-----------------------------------------------------
  function memo()
  {
    $params=$this->buildWidgetParamArray(func_get_args ());
    return $this->processWidget('memo',$params);
  }
//-----------------------------------------------------
  function checkbox()
  {
    $params=$this->buildWidgetParamArray(func_get_args ());
    return $this->processWidget('checkbox',$params);
  }
//-----------------------------------------------------
  function image()
  {
    $params=$this->buildWidgetParamArray(func_get_args ());
    return $this->processWidget('image',$params);
  }
//-----------------------------------------------------
  function file()
  {
    $params=$this->buildWidgetParamArray(func_get_args ());
    $params['widget']='file';
    return $this->processWidget('image',$params);
  }
//-----------------------------------------------------
  function buildWidgetParamArray($rawParams)
  {
    $res=array();
    foreach ($rawParams as $param) {
      if (substr($param,0,4)=='out:'){
        $res['out']=substr($param,5);
      } else {
        $pair=split('=',$param,2);
        $res[$pair[0]]=$pair[1];
      }
    }
    if (!isset($res['out'])) $res['out']='';
    return $res;
  }
//-----------------------------------------------------
  function processWidget($widget,$params)
  {
    $params['originalName']=$params['name'];
    if ($this->index!=-1) {
      $params['name']=$this->formname.'_'.$params['name'].'['.$this->index.']';
    }
    $widgetClassName='colesoWidget_'.$widget;
    $widget= new $widgetClassName($params,$this);
    return $widget->render();
  }
//-----------------------------------------------------
  function fields2url()
  {
    reset($this->fields);
    $urlStr='';
    while (list($k,$v)=each($this->fields)) {
      $urlStr.='&'.$k.'='.urlencode($v);
    }
    $urlStr=substr($urlStr,1);
    return $urlStr;
  }
}       //class
//***************************************************************************


//=================================================================================
//           Form Widget Classes
//=================================================================================
class colesoFormWidgetBase
{
  var $params;
  var $formPtr;

  function colesoFormWidgetBase($params,&$formPointer)
  {
    $this->params=$params;
    $this->formPtr = & $formPointer;
  }
//-------------------------------
  function render()
  {
    die ('Abstract class do not use directly');
  }
//-------------------------------
  function getEscapedValue()
  {
    $name=$this->params['originalName'];
    $value=isset($this->formPtr->fields[$name])? $this->formPtr->fields[$name]:'';
    return htmlspecialchars($value,ENT_QUOTES,colesoApplication::getConfigVal('/system/lngEncoding'));
  }
//-------------------------------
  function getField($name){
    $value=isset($this->formPtr->fields[$name])? $this->formPtr->fields[$name]:'';
    return $value;
  }
}

//=================================================================================
class colesoWidget_field extends colesoFormWidgetBase
{
  function render()
  {
    $curVal=$this->getEscapedValue();
    $res="<input name='{$this->params['name']}' {$this->params['out']} value='$curVal' />";
    return $res;
  }
}
//-------------------------------------------------------------------------
class colesoWidget_memo extends colesoFormWidgetBase
{
  function render()
  {
    $curVal=$this->getEscapedValue();
    $res="<textarea name='{$this->params['name']}' {$this->params['out']}>$curVal</textarea>";
    $res.=$this->richEditWidget();
    return $res;
  }
//-------------------------------------------------------------------------
  function richEditWidget()
  {
    $name=$this->params['name'];
    $widgetType=isset($this->params['widget'])? $this->params['widget']:'win';
    if  ($widgetType=='none') return '';
    $res="<script type='text/javascript' language='JavaScript'>placeEditWidget('$name','{$this->formPtr->formname}');</script>\n";
    return $res;
  }
}

//=================================================================================
class colesoWidget_checkbox extends colesoFormWidgetBase
{
  function render()
  {
    $widgetType=isset($this->params['type'])? $this->params['type']:'checkbox';
    $curVal=$this->getEscapedValue();
    if ($widgetType=='checkbox'){
      $checkedStr=($curVal!='' && $curVal!='0')? "checked='checked'" : '';
      $value='';
    } elseif ($widgetType=='radio') {
      $checkedStr=($curVal==$this->params['value'])? "checked='checked'" : '';
      $value='value="'.$this->params['value'].'"';
    }
    return "<input name='{$this->params['name']}' type='$widgetType' $value $checkedStr />";
  }
}

//=================================================================================
class colesoWidget_date extends colesoFormWidgetBase
{
  function render()
  {
    //$curVal should be in form yyyy-mm-dd(or any other non-numeric delimeter
    //or empty or NULL
    //assumed that the date is completely valid here
    $name=$this->params['originalName'];
    $curVal=$this->formPtr->fields[$name];

    $tmp_date=preg_split('/\D/',$curVal,-1,PREG_SPLIT_NO_EMPTY);
    if (count($tmp_date)!=3) {
      $tmp_date=array(0,0,0);
    }
    $dateOrder=colesoFormControl::formGetDateOrder();
    $l=strlen($dateOrder);
    $res='';
    for ($i=0;$i < $l;$i++){
      $code=$dateOrder{$i};
      if ($i>0) $res.='&nbsp;';
      $res.=$this->makeDateElementDropDown($code,$name,$tmp_date);
    }
    $res.=$this->calendarWidget();
    return $res;
  }
//------------------------------------------------------------------------
  function makeDateElementDropDown($code,$name,$dateparts)
  {
    if ($code=='y')
        $res=$this->makeDropDown($this->dropDownElemntName($name,'year'),'_frm_sys_date_years',$dateparts[0]);
    elseif ($code=='m')
        $res=$this->makeDropDown($this->dropDownElemntName($name,'month'),'_frm_sys_date_months',$dateparts[1]);
    elseif($code=='d')
        $res=$this->makeDropDown($this->dropDownElemntName($name,'day'),'_frm_sys_date_days',$dateparts[2]);
    else
        die ('illegal component in date order format settings: '.$code);
    return $res;
  }
//------------------------------------------------------------------------
  function makeDropDown($name,$topics,$curVal)
  {
    $params=$this->params;
    $params['name']=$name;
    $params['topics']=$topics;
    $params['emptyLine']='noEmptyLine';  //use prepared values
    $widget= new colesoWidget_select($params,$this->formPtr);
    return $widget->dropDown($params['emptyLine'],$curVal);
  }
//------------------------------------------------------------------------
  function dropDownElemntName($name,$option)
  {
    $res='_'.$name."_$option";
    if ($this->formPtr->index!=-1) {
      $res= $this->formPtr->formname.'_'.$res."[{$this->formPtr->index}]";
    }
    return $res;
  }
//--------------------------------------------------------------------------
  function calendarWidget()
  {
    $widgetType=isset($this->params['widget'])? $this->params['widget']:'win';
    $name=$this->params['name'];
    if  ($widgetType=='none') return '';
    $widgetStr=($widgetType=='div')? ",'div'":"";
    $res="&nbsp;<script type='text/javascript' language='JavaScript'>\n";
    $res.="placeCalendarWidget('".$this->formPtr->formname."','$name'$widgetStr);\n";
    $res.="</script>";
    return $res;
  }
}

//=================================================================================
class colesoWidget_select extends colesoFormWidgetBase
{
  function render()
  {
    if (!isset ($this->params['topics'])) die ('List elements array does not exists: '.$attrib['names_array']);
    $emptyLine=isset($this->params['emptyLine'])? $this->params['emptyLine']:'emptyLine';
    return $this->dropDown($emptyLine);
  }
//-----------------------------------------------------------------
  function dropDown($emptyLine,$curVal='')
  {
    $res="<select name='{$this->params['name']}' {$this->params['out']} >\n";
    $listPool=$this->formPtr->formListPool[$this->params['topics']];
    if ($curVal=='') $curVal=$this->getEscapedValue();
    //if (is_array($listPool)) $listPool=new colesoDataset($listPool);
    if ($listPool instanceof colesoDataset) {
      if ($emptyLine=='emptyLine') {
        $emptyValue=isset($this->params['emptyLineString'])? $this->params['emptyLineString']:'';
        $listPool->insertBefore('',$emptyValue);
      }
      $res.=$this->buildTopicsSection($listPool,$curVal);
    }
    $res.="</select>";
    return $res;
  }
//-----------------------------------------------------------------
  function buildTopicsSection($pool,$curVal)
  {
    $res='';
    foreach($pool as $k => $v) {
      if (is_array($v)){
        $text=htmlspecialchars($k);
        $res.="<optgroup label='$text'>\n";
        $res.=$this->buildTopicsSection($v,$curVal);
        $res.="</optgroup>\n";
      } else {
        $selectedMark=($curVal==$k)? "selected='selected'":'';
        $value=$k;
        $text=htmlspecialchars($v);
        $res.="<option value='$value' $selectedMark>$text</option>\n";
      }
    }
    return $res;
  }
}
//==================================================================================
class colesoWidget_image extends colesoFormWidgetBase
{
  function render()
  {
    $curVal=$this->getEscapedValue();
    $name=$this->params['name'];
    $widgetType=isset($this->params['widget'])? $this->params['widget']:'picture';
    $salt=isset($this->params['salted'])? '?salt='.md5(time()):'';
    $deltext=colesoApplication::getMessage('form','deltext');
    $image_url=$this->getField($name.'_url');
    $res='';
    if ($curVal){
      if ($widgetType=='picture')  $res.="<img src='$image_url{$salt}'><br/>";
      $res.="<a href='$image_url'>$curVal</a>,\n".
      "$deltext <input type='checkbox' name='del{$name}'><br/><br/>";
    }
    $res.="<input type='file' name='upld{$name}'/>";
    $res.="<br><input name='$name' type='hidden' value='$curVal' />\n";
    return $res;
  }
}
?>
