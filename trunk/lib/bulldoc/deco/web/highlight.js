  function addLoadEvent(func) {
    var oldonload = window.onload;
    if (typeof window.onload != 'function') window.onload = func;
    else {
      window.onload = function() {
      if (oldonload) oldonload();
        func();
      }
    }
  }
  function initCodeHashHighlight() {
    if (!document.getElementById) return;
    if (!location.hash) return;
    var target = document.getElementById(location.hash.substr(1));
    if (!target) return;
    target.style.backgroundColor = '#FFFF99';
  }
