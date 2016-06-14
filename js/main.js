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
          $("#file-upload-container").hide();
          items = $.parseJSON(response);
          populateList('parse-result', true);
//           $("#upload-modal").modal("hide");
        }
      }
    }
  });

/*
  $("html").dropable(function() {
  });
*/

  function populateList(listItem = 'parse-result', isImport = false) {
    if((items && items != undefined) && (listItem && $("#"+listItem).length > 0)) {
      if(isImport) {
        var $form = $('<form id="import-form" action="" method="POST"></form>');
        var $menuBar = $('<div id="menu-bar"></div>');
        var $buttonImportAll = $('<div id="import-all" class="button">Import All Items</div>');
        $buttonImportAll.click(items, importAllItems);
/*
        var $buttonImportSelected = $('<div id="import-selected" class="button">Import Selected Items</div>');
        $buttonImportSelected.click(items, importSelectedItems);
*/
  
        $menuBar.append($buttonImportAll/* , $buttonImportSelected */);
  
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
               $item = $('<li><span class="link">'+item.name+'</span></li>');
            } else {
               $item = $('<li data-item-id="'+item.id+'"><span class="link"><a href="http://lucy.allakhazam.com/itemraw.html?id='+item.id+'">'+item.name+'</a></span><span class="count">'+item.count+'x</span></li>');
            }
    
            $list.append($item);
          }
          $container.append($header, $selector, $list);
        } else {
          $container.append($header);
        }
        $form.append($container);
      }
      $("#"+listItem).append($form);
//       $("div#file-upload-container").reset();
    }
  }
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
         $("#upload-modal").modal('hide');
        if(result.success && parseInt(result.characterId) > 0) {
          var $responseHTML = $(result.html);
          items.characterId = parseInt(result.characterId);
          $("#query-modal .modal-body").html($responseHTML);
          $("#query-modal").modal('show');

          console.log('Character does exist');
        } else {
          // To Do: Dropdown with Server?
          var $responseHTML = $(result.html);
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
  function eraseAndImport(characterId = items.characterId) {
    if(parseInt(characterId) > 0) {
      if(items.selectedItems == undefined || items.selectedItems === null) {
        for(var k in items) {
          if(k != 'characterName' && items[k].length > 0) {
            for(var i in items[k]) {
              selectedItems.push(items[k][i]);
            }
          }
        }
      }
  
      var profileData = {
        'action': 'eraseAndImport',
        'characterId': characterId,
        'items': selectedItems
      }
  
      $.post('eqp/requestHandler.php', profileData, function(response) {
        if(response) {
          var result = $.parseJSON(response);
          if(result.success) {
            $("#query-modal .modal-body").html(result.response);
            reloadCharacterDropdown();
          }
        }
      });
    } else {
      return false;
    }
  }
  function reloadCharacterDropdown() {
    var data = {'action': 'getCharacters'}
    $.post('eqp/requestHandler.php', data, function(response) {
      if(response) {
        var result = $.parseJSON(response);
        if(result.success) {
          $selectBox = $("#filter-by-character").empty();
          for(var k in result.characters) {
            var characterInfo = result.characters[k];
            var $option = $('<option value="'+characterInfo.internal_character_id+'">'+characterInfo.character_name+'</option>');
            $selectBox.append($option);
          }
        }
      }
    });
  }
  $("html").on("dragenter", function() {
    if($("#upload-modal").length > 0) {
      $("#upload-modal").modal("show");
    }
  });
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
  $("#parse-result h4").on('click', function() {
    $(this).next("ul").toggle();
  });
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
          if(result) {
            items = result;
            populateList('result-list');
          }
        }
      });
    }
  });
  $("a#import-dialog").on('click', function() {
    if($("#upload-modal").length > 0 && !$("#upload-modal").is(":visible")) {
      $("#upload-modal").modal("show");
    }
  });
  $("#upload-modal").on('hidden.bs.modal', function () {
    $(this).find("#file-upload-container").show();
    $(this).find("#parse-result").empty();
  });
  $(document).on('click', '.button', function() {
    var $self = $(this);
    switch($self.attr('id')) {
      case 'cancel-import':
        $("#query-modal").modal("hide");
      break;

      case 'create-and-import':
        createCharacterAndImport();
      break;

      case 'erase-and-import':
        eraseAndImport();
      break;

      case 'finish-import':
        $("#query-modal").modal("hide");
        $("#parse-result").empty();
      break;
    }
  });
});
