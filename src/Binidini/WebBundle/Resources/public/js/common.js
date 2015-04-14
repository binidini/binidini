var loader = {
  show:function(insideObjectName){
      $(insideObjectName).append('<span class="loader glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>');
  },
    hide:function(insideObjectName){
        $(insideObjectName).find('.loader').remove();
    }
};