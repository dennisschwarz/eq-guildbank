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
  var selectedCharacterId = 0;

/*
  $("html").dropable(function() {
  });
*/

  function populateList(listItem = 'parse-result', isImport = false) {
    if((items && items != undefined) && (listItem && $("#"+listItem).length > 0)) {
      $("#"+listItem).empty();
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

        if(isImport) {
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
        }

        switch(k) {
          case 'characterName':
            var $header = $('<div class="header-bar"></div>');
            $header.append('<h4>Character Name: '+items[k]+'</h4>');
            var $titleBar = '';
            if(!isImport) {
              $header.append('<div id="export-list-by-character" class="button" data-toggle="modal" data-target="#export-modal">Export List</div>');
              var $titleBar = $('<div class="row" id="sorting"><div class="col-xs-8"><img src="'+rootUrl+'/img/icon-arrow-down-b-128.png" id="sort-name" class="sort-button order-arrow" data-order="asc">Name:<img src="'+rootUrl+'/img/icon-arrow-up-b-128.png" id="sort-name" class="sort-button order-arrow" data-order="desc"></div><div class="col-xs-4"><img src="'+rootUrl+'/img/icon-arrow-down-b-128.png" id="sort-amount" class="sort-button order-arrow" data-order="asc">Amount<img src="'+rootUrl+'/img/icon-arrow-up-b-128.png" id="sort-amount" class="sort-button order-arrow" data-order="desc"></div></div>');
            }
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
            var $header = $('<h4>Bank Items:</h4>');
            $header.on("click", function() {
              $("ul#bankItems").toggle();
            });
            $header.append($selector);
          break;
          case 'hoardItems':
            var $header = $('<h4>Hoard Items:</h4>');
            $header.on("click", function() {
              $("ul#hoardItems").toggle();
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
               $item = $('<li data-item-id="'+item.id+'" data-item-slot="'+k+'" data-item-key="'+j+'" class="item"><span class="link"><a href="http://lucy.allakhazam.com/itemraw.html?id='+item.id+'" class="zam-link">'+item.name+'</a></span><span class="count">x'+item.count+'</span></li>');
               $item.on("click", function() {
                var $self = $(this);
                if(!$self.hasClass("selected")) {
                  $self.addClass("selected");
                } else {
                  $self.removeClass("selected");
                }
              });
            }
    
            $list.append($item);
          }
          $container.append($header, $selector, $titleBar, $list);
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

          $("#parse-result #import-form li.item.selected").each(function() {
            var $self = $(this);
            for(var k in items) {
              var itemsBlock = items[k];
              for(var j in itemsBlock) {
                var item = itemsBlock[j];
                if(item && item.id && parseInt(item.id) == parseInt($self.data("item-id")) && k == $self.data('item-slot') && parseInt(j) == parseInt($self.data('item-key'))) {
                  selectedItems.push(item);
                }
              }
            }
          });
        } else {
          // To Do: Dropdown with Server?
          var $responseHTML = $(result.html);
          $("#query-modal .modal-body").html($responseHTML);
          $("#query-modal").modal('show');
        }
      }
    });
  }
  function createCharacterAndImport() {
    if(selectedItems == undefined || selectedItems === null || selectedItems.length == 0) {
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
            reloadCharacterDropdown();
            selectedItems = [];
          }
        }
      });
    }
  }
  function eraseAndImport(characterId = items.characterId) {
    if(parseInt(characterId) > 0) {
      if(selectedItems == undefined || selectedItems === null || selectedItems.length == 0) {
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
            selectedItems = [];
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
          $selectBox.append($('<option value="0">Please select ...</option>'));
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
  $("#parse-result a.zam-link").on('click', function(e) {
    e.preventDefault();
  });
  $("#filter-by-character").on('change', function() {
    var $self = $(this);
    selectedCharacterId = $self.val();
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
  $("#export-modal").on('change', '#select-export-option', function() {
    $self = $(this);
    switch($self.val()) {
      case 'bbcode':
        if(selectedCharacterId > 0) {
          var requestData = {
            'action': 'exportItemsByTypeAndCharacter',
            'characterId': selectedCharacterId,
            'exportType': $self.val(),
            'field': 'item_name',
            'direction': 'asc'
          }
          $.post('eqp/requestHandler.php', requestData, function(response) {
            if(response) {
              var result = $.parseJSON(response);
              if(result.success) {
                $("#export-result").empty().html(result.exportData);
                $("#select-export-and-copy").show();
              }
            }
          });
        }
      case 'csv':
        if(selectedCharacterId > 0) {
          var requestData = {
            'action': 'exportItemsByTypeAndCharacter',
            'characterId': selectedCharacterId,
            'exportType': $self.val(),
            'field': 'item_name',
            'direction': 'asc'
          }
          $.post('eqp/requestHandler.php', requestData, function(response) {
            if(response) {
              var result = $.parseJSON(response);
              if(result.success) {
                $("#export-result").empty().html(result.exportData);
                $("#select-export-and-copy").show();
              }
            }
          });
        }
      break;
    }
  });
  $(document).on('click', '.button, .sort-button', function() {
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

      case 'sort-name':
        var requestData = {
          'action': 'getItems',
          'characterId': $("#filter-by-character").val(),
          'field': 'item_name',
          'direction': $self.data('order')
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
      break;

      case 'sort-amount':
        var requestData = {
          'action': 'getItems',
          'characterId': $("#filter-by-character").val(),
          'field': 'count',
          'direction': $self.data('order')
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
      break;

      case 'export-list-by-character':
//         selectedCharacterId = $self.data("character-id");
//         $("#export-modal").toggle('show');
      break;

      case 'select-export-and-copy':
        var $temp = $('<input>');
        $("#export-result").append($temp);
        $temp.val($("#export-result").text()).select();
        document.execCommand("copy");
        $temp.remove();
      break;
    }
  });
});
