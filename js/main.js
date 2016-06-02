$(document).ready(function() {
  var $dropzone = $("div#file-upload-container").dropzone({ 
    url: "include/requestHandler.php",
    paramName: "inventory-file",
    uploadMultiple: false,
    previewsContainer: false,
    drop: function() {
      $("#parse-result").empty();
    },
    success: function(event, response) {
      var self = this;
      if(event.status == 'success') {
        if(response.length > 0 && response != '') {
          var items = $.parseJSON(response);
          populateList(items);
        }
      }
    }
  });

  function populateList(items) {
    if(items && items != undefined) {
      for(var k in items) {
        switch(k) {
          case 'characterItems':
            var $header = $('<h4>Equipped Items:</h4>');
          break;
          case 'inventoryItems':
            var $header = $('<h4>Bag Items:</h4>');
          break;
          case 'bankItems':
            var $header = $('<h4>bank Items:</h4>');
          break;
        }
  
        var $list = $('<ul id="'+k+'"></ul>');
        var categoryItems = items[k];
  
        for(var j in categoryItems) {
          var item = categoryItems[j];
          if(item.name.toLowerCase() == 'empty') {
             $item = $('<li><span class="slot">'+item.location+'</span><span class="link">'+item.name+'</span></li>');
          } else {
             $item = $('<li><span class="slot">'+item.location+'</span><span class="link"><a href="http://lucy.allakhazam.com/itemraw.html?id='+item.id+'">'+item.count+'x '+item.name+'</a></span></li>');
          }
  
          $list.append($item);
        }
        $("#parse-result").append($header, $list);
      }
//       $("div#file-upload-container").reset();
    }
  }

  $("#parse-result h4").on("click", function() {
    $(this).next("ul").toggle();
  });

});
