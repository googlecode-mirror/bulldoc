document.write(getCalendarStyles());

function placeEditWidget(fieldName,formName)
{
  //alert(getFormNoByName(formName));
  var formNo=getFormNoByName(formName);
  var linkStr="<a href='#' onClick='window.open(\""+colesoSystemRicheditPath+"tinymce&hideLayout=yes&";
  linkStr+="form="+formNo+"&field="+fieldName+"\",\"Editor\",";
  linkStr+="\"width=750,height=505,resizable=no,scrollbars=no,toolbar=no,location=no,status=no,menubar=no\");return false'";
  linkStr+="><img src='"+colesoSystemImagesPath+"icons/page_edit.png' style='border: 0'></a><br/>";
  linkStr+="<a href='#' onClick='window.open(\""+colesoSystemRicheditPath+"plainedit&hideLayout=yes&";
  linkStr+="form="+formNo+"&field="+fieldName+"\",\"Editor\",";
  linkStr+="\"width=750,height=505,resizable=no,scrollbars=no,toolbar=no,location=no,status=no,menubar=no\");return false'";
  linkStr+="><img src='"+colesoSystemImagesPath+"icons/application_edit.png' style='border: 0'></a>";
  document.write(linkStr);
}
//-------------------------------------------------------------------------
function getFormNoByName(formName)
{
  var myForm=document.getElementById(formName);
  var frms=document.forms;
  for (var i=0; i<frms.length;i++){
    if (frms[i] == myForm){
      return i;
    }
  }
  return null;
}
//-------------------------------------------------------------------------
function placeCalendarWidget(formName,fieldName)
{
  fieldNameEsc=fieldName.replace(/[\]\[]/g,"b");

  if (arguments.length>2 && arguments[2]=='div'){
     var myCal=window['cal'+formName+'_'+fieldNameEsc] = new CalendarPopup('calDiv'+formName+'_'+fieldName);
  } else {
     var myCal=window['cal'+formName+'_'+fieldNameEsc] = new CalendarPopup();
  }
  myCal.offsetX = 50;
  myCal.offsetY = -80;

  
  var linkStr="<a href='#' name='calCtrl"+formName+'_'+fieldName+"' id='calCtrl"+formName+'_'+fieldName+"' ";
  linkStr+="onClick=\"cal"+formName+'_'+fieldNameEsc+".showCalendar";
  linkStr+="('calCtrl"+formName+'_'+fieldName+"',getDateString('"+formName+"','"+fieldName+"'));";
  linkStr+=" return false;\">";
  linkStr+='<img border="0" src="'+colesoSystemImagesPath+'icons/calendar.png" style="vertical-align: middle">';
  linkStr+="</a>";
  document.write(linkStr);

  window['setDateWidgetValues'+formName+'_'+fieldNameEsc] = function (y,m,d) { setDateWidgetValues(formName,fieldName,y,m,d); }
  window['cal'+formName+'_'+fieldNameEsc].setReturnFunction('setDateWidgetValues'+formName+'_'+fieldNameEsc);
  document.write("<div class='calendar' id='"+'calDiv'+formName+'_'+fieldName+"'></div>");
}

//-------------------------------------------------------------------------

function setDateWidgetValues(formName,fieldName,y,m,d)
{
  var myForm=document.getElementById(formName);
  if (fieldName.match(formName+'_')){
    var dayControl=myForm.elements[fieldName.replace(/_/,'__').replace('[',"_day[")];
    var monthControl=myForm.elements[fieldName.replace(/_/,'__').replace('[',"_month[")];
    var yearControl=myForm.elements[fieldName.replace(/_/,'__').replace('[',"_year[")];
  } else {
    var dayControl=myForm.elements["_"+fieldName+"_day"];
    var monthControl=myForm.elements["_"+fieldName+"_month"];
    var yearControl=myForm.elements["_"+fieldName+"_year"];
  }
  dayControl.selectedIndex=d;
  monthControl.selectedIndex=m;
  yearControl.value=y;
}
//-------------------------------------------------------------------------
function getDateString(formName,fieldName) {
  var myForm=document.getElementById(formName);
  if (fieldName.match(formName+'_')){
    var d=myForm.elements[fieldName.replace(/_/,'__').replace('[',"_day[")].value;
    var m=myForm.elements[fieldName.replace(/_/,'__').replace('[',"_month[")].value;
    var y=myForm.elements[fieldName.replace(/_/,'__').replace('[',"_year[")].value;
  } else {
    var d=myForm.elements["_"+fieldName+"_day"].value;
    var m=myForm.elements["_"+fieldName+"_month"].value;
    var y=myForm.elements["_"+fieldName+"_year"].value;
  }

	if (y=="" || m=="" || y=="0" || m=="0") { return null; }
	if (d=="") { d=1; }
  return str= y+'-'+m+'-'+d;
}
//-------------------------------------------------------------------------
function selectAll(formName)
{
  var myForm = document.getElementById(formName);
  var i;
  var l=myForm.length;
  for (i=0;i < l; i++){
    if (myForm.elements[i].name.match(/^operid/)){
      myForm.elements[i].checked=true;
    }
  }
}
//-------------------------------------------------------------------------
function deselectAll(formName)
{
  var myForm = document.getElementById(formName);
  var i;
  var l=myForm.length;
  for (i=0;i < l; i++){
    if (myForm.elements[i].name.match(/^operid/)){
      myForm.elements[i].checked=false;
    }
  }
}

//=============================================================================
//table highlight

  // this function is need to work around
  // a bug in IE related to element attributes
  function hasClass(obj) {
     var result = false;
     if (obj.getAttributeNode("class") != null) {
         result = obj.getAttributeNode("class").value;
     }
     return result;
  }

function stripe(id) {
    var even = false;
    var evenColor = arguments[1] ? arguments[1] : "#fff";
    var oddColor = arguments[2] ? arguments[2] : "#eee";
    var table = document.getElementById(id);
    if (! table) { return; }
    var tbodies = table.getElementsByTagName("tbody");

    for (var h = 0; h < tbodies.length; h++) {
      var trs = tbodies[h].getElementsByTagName("tr");
      for (var i = 0; i < trs.length; i++) {
	      if (!hasClass(trs[i]) && ! trs[i].style.backgroundColor) {
          var tds = trs[i].getElementsByTagName("td");
          for (var j = 0; j < tds.length; j++) {
            var mytd = tds[j];
	          if (! hasClass(mytd) && ! mytd.style.backgroundColor) {
		          mytd.style.backgroundColor = even ? evenColor : oddColor;
            }
          }
        }
        even =  ! even;
      }
    }
}

//============================================================
function getObj(name)
{
  if (document.getElementById)
  {
      this.obj = document.getElementById(name);
      this.style = document.getElementById(name).style;
  }
  else if (document.all)
  {
      this.obj = document.all[name];
      this.style = document.all[name].style;
  }
  else if (document.layers)
  {
    this.obj = document.layers[name];
    this.style = document.layers[name];
  }
}
//==============================================================
    function toggle(id,prefix){
      ul = prefix+"ul_" + id;
      img = prefix+"img_" + id;
      ulElement  = document.getElementById(ul);
      imgElement = document.getElementById(img);
      if (ulElement){
        if (ulElement.className == 'closed'){
           nodestats[id]=1;
           ulElement.className = "open";
           imgElement.src = colesoSystemImagesPath+"icons/open_folder.png";
        } else {
           nodestats[id]=0;
           ulElement.className = "closed";
           imgElement.src = colesoSystemImagesPath+"icons/closed_folder.png";
        }
        saveNodeStats();
      }
      return false;
} // toggle()

var colesoSkipCookie;

//==============================================================
function setCookie(cookieName, cookieValue, expires, path, domain, secure)
{
  document.cookie = escape(cookieName) + '=' + escape(cookieValue)
  + (expires ? '; EXPIRES=' + expires.toGMTString() : '')
  + (path ? '; PATH=' + path : '')
  + (domain ? '; DOMAIN=' + domain : '')
  + (secure ? '; SECURE' : '');
}

// A complementary function to unwrap a cookie.
function getCookie(cookieName)
{
  var cookieValue = null;
  var posName = document.cookie.indexOf(escape(cookieName) + '=');
  if (posName != -1) {
    var posValue = posName + (escape(cookieName) + '=').length;
    var endPos = document.cookie.indexOf(';', posValue);
    if (endPos != -1) {
      cookieValue = unescape(document.cookie.substring(posValue, endPos));
    } else {
      cookieValue = unescape(document.cookie.substring(posValue));
    }
  }
  return cookieValue;
}

//===================================================================
function askConfirm()
{
  var res=confirm('Are you sure?');
  return res;
}
//------------------------------------------------------------------
function confirmFormDelete(form)
{
  var mySelect=form.selAction;
  var mySelectIndex=mySelect.selectedIndex;
  var mySelectValue=mySelect.options[mySelectIndex].value;
  if (mySelectValue=='del') return confirm('Are you shure?');
  return true;
}

//===================================================================
function doLoad(reqParam,interactionConfig) {
    var req = new JsHttpRequest();
    var myFunc=reqParam.func;
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
            if (req.responseJS) {
                myFunc(req.responseJS,interactionConfig);
            }
            document.getElementById('debug').innerHTML =
                req.responseText;
        }
    }
    req.caching = true;
    req.open('POST', reqParam.url, true);
    req.send(reqParam.query);
}
//-----------------------------------------------------
function recieveTopics(requestData,interacionConfig)
{
  document.getElementById(interacionConfig.slaveElementContainer).innerHTML =
                    requestData[interacionConfig.responceContent] || interacionConfig.emptyText;
                    selectCurrentTopic(interacionConfig);
}
//-----------------------------------------------------
function doLoadTopics(interacionConfig) {
  var selControl=document.getElementById(interacionConfig.masterElement);
  var masterID = '' +selControl.options[selControl.selectedIndex].value;
  var query=interacionConfig.query;
  query['masterID']=masterID;
  var reqParam={func: recieveTopics, 
                url: interacionConfig.queryURL, 
                query: query};
  doLoad(reqParam,interacionConfig);
}
//-----------------------------------------------------
function selectCurrentTopic(interacionConfig)
{
  var topicID=interacionConfig.currentValue;
  var mySelect=document.getElementById(interacionConfig.slaveElement);
  var l=mySelect.options.length;
  for (i=0;i<l;i++){
    if (mySelect.options[i].value==topicID){
      mySelect.selectedIndex=i;
    }
  }
}
//--------------------------------------------------------------------
function debug(id) {
    var container = document.getElementById(id);
    var source = container.innerHTML;
    source = source.replace(/&/g, '&amp;');
    source = source.replace(/</g, '&lt;');
    source = source.replace(/>/g, '&gt;');
    source = source.replace(/\n/g, '<br>');
    var win = window.open('', 'debug'+id, 'width=500,height=450,resizable=yes,scrollbars=yes,titlebar=yes');
    win.document.write("<html><head><title>Debug</title></head><body><div style='font-size: x-small;padding: 5px;background-color: #D0CEA8'>"+source+"</div></body></html>");
}
