$(document).ready(function() {
  var items;
  var selectedItems = new Array;
  var $dropzone = $("div#file-upload-container").dropzone({ 
    url: "eqp/requestHandler.php",
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
          items = $.parseJSON(response);
          populateList(items);
          $("#upload-modal").modal("hide");
        }
      }
    }
  });

/*
  $("html").dropable(function() {
  });
*/

  $("html").on("dragenter", function() {
    if($("#upload-modal").length > 0) {
      $("#upload-modal").modal("show");
    }
  });

  function populateList(items, isImport = false) {
    if(items && items != undefined) {

      if(isImport) {
        var $form = $('<form id="import-form" action="" method="POST"></form>');
        var $menuBar = $('<div id="menu-bar"></div>');
        var $buttonImportAll = $('<div id="import-all" class="button">Import All Items</div>');
        $buttonImportAll.click(items, importAllItems);
        var $buttonImportSelected = $('<div id="import-selected" class="button">Import Selected Items</div>');
        $buttonImportSelected.click(items, importSelectedItems);
  
        $menuBar.append($buttonImportAll, $buttonImportSelected);
  
        $form.append($menuBar);
      } else {
        $form = $('<div id="item-list"></div>');
      }

      for(var k in items) {
        var withItemList = true;

        $container = $('<div class="item-container"></div>');

        var $selector = $('<div class="toggle-all" id="toggle-'+k+'" data-item-type="'+k+'">toggle all</div>');
        $selector.on("click", function() {
          $self = $(this);
          $("ul#"+$self.data('item-type')+" li").each(function() {
            if($(this).hasClass("selected")) {
              $(this).removeClass("selected");
            } else {
              $(this).addClass("selected");
            }
          });
        });

        switch(k) {
          case 'characterName':
            var $header = $('<h4>Character Name: '+items[k]+'</h4>');
            withItemList = false;
          break;
          case 'characterItems':
            var $header = $('<h4>Equipped Items:</h4>');
            $header.on("click", function() {
              $("ul#characterItems").toggle();
            });
            $header.append($selector);
          break;
          case 'inventoryItems':
            var $header = $('<h4>Bag Items:</h4>');
            $header.on("click", function() {
              $("ul#inventoryItems").toggle();
            });
            $header.append($selector);
          break;
          case 'bankItems':
            var $header = $('<h4>bank Items:</h4>');
            $header.on("click", function() {
              $("ul#bankItems").toggle();
            });
            $header.append($selector);
          break;
        }

        if(withItemList) {
          var $list = $('<ul id="'+k+'"></ul>');
          var categoryItems = items[k];
    
          for(var j in categoryItems) {
            var item = categoryItems[j];
            if(item.name.toLowerCase() == 'empty') {
               $item = $('<li><span class="slot">'+item.location+'</span><span class="link">'+item.name+'</span></li>');
            } else {
               $item = $('<li data-item-id="'+item.id+'"><span class="slot">'+item.location+'</span><span class="link"><a href="http://lucy.allakhazam.com/itemraw.html?id='+item.id+'">'+item.count+'x '+item.name+'</a></span></li>');
            }
    
            $list.append($item);
          }
          $container.append($header, $selector, $list);
        } else {
          $container.append($header);
        }
        $form.append($container);
      }
      $("#parse-result").append($form);
//       $("div#file-upload-container").reset();
    }
  }

  $("#parse-result h4").on("click", function() {
    $(this).next("ul").toggle();
  });

  function importAllItems(event) {
    console.log(event);
    var importData = event.data;

    // Check if Profile Exists
    var profileData = {
      'action': 'checkProfile',
      'profileName': importData.characterName
    }
    $.post('eqp/requestHandler.php', profileData, function(response) {
      if(response) {
        var result = $.parseJSON(response);
        // Character exists, proceed with import
        if(result.success) {

        } else {
          // To Do: Dropdown with Server?
          var $responseHTML = $(result.html),
          $responseButtonCancel = $responseHTML.find('#cancel-button').html(),
          $responseButtonProceed = $responseHTML.find('import-all');
console.log($responseButtonCancel);
          $responseHTML.find('cancel-button').on("click", function() {
            $("#query-modal").modal("hide");
          });
          $responseButtonProceed.on("click", function() {
            var profileData = {
              'action': 'addCharacter',
              'proileName': importData.characterName
            }
            $.post('eqp/requestHandler.php', profileData, function(response) {
            });
          });

          //$responseHTML; //.append($responseText, $responseButtonCancel, $responseButtonProceed);

          $("#query-modal .modal-body").html($responseHTML);
          $("#query-modal").modal('show');

          console.log('Character does not exist');
        }
      }
    });
  }

  function importSelectedItems(event) {
    alert('under construction');
  }


  $("#parse-result #import-all-items").on('click', function() {
    console.log('hier');
/*
    var $self = $(this);
    var items = new Array;
    $("#parse-result #import-form .item").each(function() {
      if(parseInt($(this).data("item-id")) > 0) {
        items.push(parseInt($(this).data("item-id")));
      }
    });
    console.log(items);
*/
  });

  function createCharacterAndImport() {
    if(items.selectedItems == undefined || items.selectedItems === null) {
      for(var k in items) {
        if(k != 'characterName' && items[k].length > 0) {
          for(var i in items[k]) {
            selectedItems.push(items[k][i]);
          }
        }
      }
    }

    if(items.characterName != '') {
      var profileData = {
        'action': 'addCharacterAndImport',
        'profileName': items.characterName,
        'items': selectedItems
      }
      $.post('eqp/requestHandler.php', profileData, function(response) {
        if(response) {
          var result = $.parseJSON(response);
          if(result.success) {
            $("#query-modal .modal-body").html(result.response);
          }
        }
      });
    }
  }

  $("#filter-by-character").on('change', function() {
    var $self = $(this);
    if($self.val() > 0) {
      var requestData = {
        'action': 'getItems',
        'characterId': $self.val()
      }
      $.post('eqp/requestHandler.php', requestData, function(response) {
        if(response) {
          var result = $.parseJSON(response);

          populateList(result);
        }
      });
    }
  });

  $(document).on("click", ".button", function() {
    var $self = $(this);
    switch($self.attr('id')) {
      case 'cancel-import':
        $("#query-modal").modal("hide");
      break;

      case 'create-and-import':
        createCharacterAndImport();
      break;

      case 'finish-import':
        $("#query-modal").modal("hide");
        $("#parse-result").empty();
      break;
    }
  });

});
