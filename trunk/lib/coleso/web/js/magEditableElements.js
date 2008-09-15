var magEditableElements = Class.create();
magEditableElements.prototype = {
  initialize: function(id, template){
            
    this.container = $(id);
    this.template = $(template);
                
    this.options = Object.extend({
        type: 'Name'
    },arguments[2]||{});
                
  }, //end initialize

  loadData: function(){
  var obj = this;

  JsHttpRequest.query(
    reqUrl,
    { ajaxaction: 'getElements'}, // parameters
    function(result, errors) {
      var xotree = new XML.ObjTree();
      var xtree = xotree.parseXML( result);
      obj.XML = xtree;
      obj.applyData();
    },
    true
  );
  }, //end loadData

  applyData: function(){
    
    while (this.container.childNodes.length > 0) {
      this.container.removeChild(this.container.firstChild);
    }
    if (this.XML.elements.element['-id']!=undefined) {
      this.addRow(this.XML.elements.element,0);
    } else {
      for ( var i = 0; i < this.XML.elements.element.length; i++ ){
        var element = this.XML.elements.element[i];
        this.addRow(element,i);
      }
    }
    // Adding drag&drop
    //
    if ( this.options.type != 'Name' ){
      var obj = this;
      Sortable.create(this.container, {
        tag: 'li',

        starteffect: function(element){
          //alert(element.style.border);
          new Effect.Opacity(element, {duration:0.2, from:1, to:0.7}); 
          element.style.border = '1px solid orange';
        },

        endeffect: function(element){
          new Effect.Opacity(element, {duration:0.2, from:0.7, to:1}); 
          element.style.border = '1px solid #ccc';
          curColor='white';
        },

        onUpdate: function(){ 
          //alert(Sortable.serialize(this.element));
          JsHttpRequest.query(
              reqUrl,              // backend address
              { 
                  listname: listname,
                  elements: Sortable.serialize(this.element),
                  ajaxaction: 'saveSorting'
              }, // parameters
      
            function(result, errors) {
              //alert(result);
              }
          );
        }//end onUpdate

      });
    }

  }, //end applyData function

  
  addRow: function(element,i){
    var tpl = this.template.cloneNode(true);
    var chk = tpl.getElementsByClassName("elements_id")[0];

    chk.value = element['-id'];
    tpl.id = "elem_" + element['-id'];
    
    var name = tpl.getElementsByClassName("elements_name")[0];
    name.innerHTML = element['-name'];
    name.setAttribute('elementID', element['-id']);

    name.onclick = function(){
      startElementEdit(this, this.getAttribute('elementID'), this.innerHTML);
      return false;
    }
    name.id = 'element_' + element['-id'];
    name.checked = false;

    tpl.style.backgroundColor = i % 2 ? evenRowColor : oddRowColor;

    this.container.appendChild(tpl);
  }
}

//=======================================================================================
  function startElementEdit(obj, id, text){
    var pos = Position.cumulativeOffset(obj);
    var editWindow = $('editWindow');
    editWindow.style.left = pos[0] + obj.offsetWidth + 10 + 'px';
    editWindow.style.top = pos[1] + 5 + 'px';
    editWindow.style.display = '';

    Event.observe(editWindow,"mousedown", function() { IN_EDIT_WINDOW = true} );
    Event.observe(editWindow,"mouseout", function() { IN_EDIT_WINDOW = false} );
     
    Event.observe(document,"mouseup", function() { if (!IN_EDIT_WINDOW) $('editWindow').style.display = 'none'; } );    

    $('editText').value = text;
    $('editText').focus();
    $('activeID').value = id;

    Event.observe($('editText'),"keypress", function(key) {if (key.keyCode == 13) saveElement(); if (key.keyCode == 27) $('editWindow').style.display = 'none'; } );
  };

//------------------------------------------------------------------------------
  function saveElement(){
    JsHttpRequest.query(
      reqUrl,              // backend address
      { 
        listname: listname,
        id: $('activeID').value, 
        name: $('editText').value,
        ajaxaction: 'saveElement'
      }, // parameters
      function(result, errors) {
        if ( result == 'ok' ){
          var element = $('element_' + $('activeID').value);
          element.innerHTML = $('editText').value;
          $('editWindow').style.display = 'none';
        }
      }
    );
  }
  
//------------------------------------------------------------------------------
  function addElement(){
    JsHttpRequest.query(
      reqUrl,              // backend address
      { 
        listname: listname,
        name: $('newElementName').value,
        ajaxaction: 'addElement'
      }, // parameters
      function(result, errors) {
        loadData();
        $('newElementName').value = '';
      }
    );
  }

//------------------------------------------------------------------------------
  function deleteElements(){
    JsHttpRequest.query(
      reqUrl,              // backend address
      { 
        listname: listname,
        elements: Form.serialize('elementsForm'),
        ajaxaction: 'deleteElements'
      }, // parameters
      function(result, errors) {
        loadData();
      }
    );
  }

//------------------------------------------------------------------------------
  function loadData(){
    mee = new magEditableElements('data', 'template', {type: sortMode});
    mee.loadData();
  }
  
//--------------------------------------------------------------------------------
function selectAllAjaxItems()
{
  container = $('data');
  for (i=0;i < container.childNodes.length;i++) {
      var chk = container.childNodes[i].getElementsByClassName("elements_id")[0];
      chk.checked=true;
  }
}
//--------------------------------------------------------------------------------
function deselectAllAjaxItems()
{
  container = $('data');
  for (i=0;i < container.childNodes.length;i++) {
      var chk = container.childNodes[i].getElementsByClassName("elements_id")[0];
      chk.checked=false;
  }
}

