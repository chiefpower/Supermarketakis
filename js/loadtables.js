$(document).ready(function() {
    // Click event for loading tables
    $('#load-tables').click(function(e) {
        e.preventDefault();
        loadTables();
      });

      $(document).on('click', '#back-to-tables', function () {
        isActionEnabled = false;
        //console.log('Cback-to-tables:', action);
        loadTables();
      });

      $(document).on('click', '#back-to-tables-insert', function () {
        const tableName = $(this).data('table');
        if (tableName) {
            loadTableData(tableName);
        } else {
            console.error('Table name not found on Back button.');
        }
      });
    
  
    // Event delegation for dynamically loaded table links
    function initTableLinks() {
      $(document).on('click', '.table-link', function(e) {
        e.preventDefault();
        
        const tableName = $(this).data('table');
        currentPage = 1; // reset page
        currentTable = tableName;
        console.log('Clicked table:', tableName);


      
        // Load table data dynamically
        loadTableData(tableName);
      });
    }
  
    let currentPage = 1;
    let rowLimit = 10; // Default rows per page
    let currentTable = ''; // Placeholder for dynamic table name
    let isActionEnabled = false; // Variable to track if the Add/Delete/Update checkbox is selected
   
    // Handle row-limit buttons (10, 20, 50, etc.)
    $(document).on('click', '.row-limit', function () {
      rowLimit = $(this).data('limit');
      currentPage = 1;  // Reset to the first page when row limit changes
      loadTableData(currentTable);
    });
  
    // Handle the "Prev" button click
    $(document).on('click', '#prev-page', function () {
      if (currentPage > 1) {   
        currentPage--;
        loadTableData(currentTable);
      }
    });
  
    // Handle the "Next" button click
    $(document).on('click', '#next-page', function () {
        const totalPages = $('#content-area').data('total-pages');
      if (currentTable && currentPage < totalPages) {  
         currentPage++;
         loadTableData(currentTable);
      }
    });
  
    // First page
    $(document).on('click', '#first-page', function () {
        if (currentPage !== 1 && currentTable) {
          currentPage = 1;
          loadTableData(currentTable);
        }
    });

    // Last page (we need totalPages — fetch from a data attribute or estimate it)
    $(document).on('click', '#last-page', function () {
        const totalPages = $('#content-area').data('total-pages');
        if (currentTable && totalPages && currentPage !== totalPages) {
          currentPage = totalPages;
          loadTableData(currentTable);
        }
    });

    $(document).on('change', '#action-toggle', function() {
        isActionEnabled = $(this).prop('checked'); // Check if the checkbox is selected

        // Handle any logic when the checkbox state changes (e.g., enable/disable table actions)
        console.log('Add/Delete/Update enabled:', isActionEnabled);

    });

    // Select all checkboxes
    $(document).on('change', '#select-all', function () {
        $('.row-checkbox').prop('checked', this.checked).trigger('change');
    });
  
    // Enable/Disable buttons based on selection
    $(document).on('change', '.row-checkbox', function () {
        const selectedCount = $('.row-checkbox:checked').length;
        $('#edit-selected, #delete-selected').prop('disabled', selectedCount === 0);
    });
  
      // Handle Edit Selected
      $(document).on('click', '#edit-selected, #delete-selected', function () {
        const selectedIds = $('.row-checkbox:checked').map(function () {
          return $(this).val();
        }).get();
        
        //console.log('selectedIds:', selectedIds);
        if (selectedIds.length === 0) {
          alert('Please select at least one row.');
          return;
        }
      
        //const action = $(this).attr('id') === 'edit-selected' ? 'edit_rows.php' : 'delete_rows.php';
        const action = $(this).attr('id') === 'edit-selected' ? 'edit_rows.php' : 'confirm-delete-view.php';
       // console.log('1',selectedIds);
        $.ajax({
          url: action,
          type: 'POST',
          data: {
            limit: rowLimit,
            table: currentTable,
            ids: selectedIds
          },
          success: function (response) {
            $('#content-area').html(response); // Load edit/delete response
          },
          error: function (xhr, status, error) {
            console.log('1',error);
           // alert('Error performing action: ' + error);
          }
        });
      });      

      $(document).on('submit', '.edit-row-form', function(e) {
        e.preventDefault();
        
       // const messageBox = document.getElementById('message');
        const messageBox = $(this).find('.message-box');
        const $form = $(this);
        const formData = $form.serialize();
        const $feedback = $form.find('.save-feedback');
        //console.log('Submitting form data:', formData);
       // console.log('Form data being submitted:');
       // $form.serializeArray().forEach(f => console.log(f.name + ' = ' + f.value));
        $.ajax({
            url: 'update_row.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                const messageId = $form.data('id');
                const messageBox = $('#message-' + messageId); // Get the unique message div for this form
                if (response.status === 'success') {
                   // $feedback.text(response.message).removeClass('text-danger').addClass('text-success');
                   // messageBox.innerHTML = `<div class="alert alert-success">${response.message}</div>`;
                   messageBox.html(`<div class="alert alert-success">${response.message}</div>`);
                   console.log('Cback-to-tables:',messageBox);
                } else {
                    //$feedback.text(response.message).removeClass('text-success').addClass('text-danger');
                   // messageBox.innerHTML = `<div class="alert alert-danger">${response.message}</div>`;
                   messageBox.html(`<div class="alert alert-danger">${response.message}</div>`);
                   console.log('Full response1:', response);
                }
            },
            error: function(response) {
                //$feedback.text('An error occurred').removeClass('text-success').addClass('text-danger');
             //   messageBox.innerHTML = `<div class="alert alert-danger">${response.message}</div>`;
                messageBox.html(`<div class="alert alert-danger">${response.message}</div>`);
                console.log('Full response2:', response);
            }
        });
    });

     // Handle Add new row
     $(document).on('click', '#add-new-row', function () {
      
      $.ajax({
        url: 'insert_form.php',
        type: 'POST',
        data: {
          limit: rowLimit,
          table: currentTable
        },
        success: function (response) {
          $('#content-area').html(response); // Load edit/delete response
        },
        error: function (xhr, status, error) {
          console.log('2',error);
          //alert('Error performing action1: ' + error);
        }
      });
    });      
   
    $(document).on('submit', '.insert-row-form', function(e) {
      e.preventDefault();
      const insertForm = document.getElementById('insert-row-form');
      const messageBox = document.getElementById('message');
      messageBox.innerHTML = "";
      //if (!insertForm) return;
      const $form = $(this);
      const formData = $form.serialize();
      if (!insertForm){
       // console.log('Full response:', currentTable);
        console.log('Full response:', formData);
      }
  
      $.ajax({
        url: 'insert_row.php',
        type: 'POST',
        data: formData + '&table=' + encodeURIComponent(currentTable),
        dataType: 'json',
        success: function (response) {
        //  if (response.status === 'success') {
          if (response.success) {
           // $('#content-area').html(response); // Load edit/delete response
           // messageBox.text(response.message).removeClass('text-danger').addClass('text-success');
            messageBox.innerHTML = `<div class="alert alert-success">${response.message}</div>`;
            console.log('Cback-to-tables:',messageBox);
          } else {
            messageBox.innerHTML = `<div class="alert alert-danger">${response.message}</div>`;
            console.log('Cback-to-table2s:',messageBox);
          }
          
        },
        error: function (xhr, status, error) {
          //console.log('a response',response);
          console.log('a',error);
         // alert('Error performing action2: ' + error);
        }
      });
    });

    $(document).on('click', '#confirm-delete', function () {
      const table = $(this).data('table');
      const ids = JSON.parse(this.dataset.ids);  
      
    // console.log("Table: ", table);
    //  console.log("IDs: ", ids);
    
      $.post('delete_rows.php', { table, ids }, function (response) {
        const messageBox = document.getElementById('message');
        console.log("Delete response: ", response);
       
       if (response.success) {
         // $('#message').html(`<div class="alert alert-success">${response.message}</div>`);
          messageBox.innerHTML = `<div class="alert alert-success">${response.message}</div>`;
          //alert('Rows deleted successfully.');  
         // loadTableData(table); // Reload table data
        } else {
          alert('Error: ' + response.message);
        }
      }, 'json');
    });
    
    $(document).on('click', '#cancel-delete', function () {
      const tableload = $(this).data('table');
      // Reload original table
      loadTableData(tableload);
    });

    function loadTables() { 
        $('#content-area').html('<p class="text-black">Loading tables...</p>');
      
        $.ajax({
          url: 'load_tables.php',
          type: 'GET',
          success: function(data) {
            $('#content-area').html(data);
            initTableLinks(); // Re-initialize event listeners if needed
          },
          error: function() {
            $('#content-area').html('<p class="text-danger">Error loading tables.</p>');
            console.log('Error loading tables');
          }
        });
    }      

    // Function to load table data using AJAX
    function loadTableData(tableName) {
      if (!tableName) return; // Prevent loading if no table is selected
  
      currentTable = tableName; // Store the current table name
      // Check if action toggle is enabled
      const isEditMode = $('#action-toggle').is(':checked');
     // console.log('Error loading tables', isEditMode);
     // console.log('isActionEnabled', isActionEnabled);
      // Decide which file to call
      //const targetUrl = isEditMode ? 'table_actions.php' : 'load_table_data.php';
      //const targetUrl = isActionEnabled ? 'table_actions.php' : 'load_table_data.php';
      const targetUrl = 'table_actions.php';
      //console.log('Error loading tables', targetUrl);
      $.ajax({
        url: targetUrl,
        type: 'GET',
        data: {
          table: tableName,
          limit: rowLimit,
          page: currentPage,
          action: isActionEnabled
        },
        success: function (data) {
          $('#content-area').html(data);
        },
        error: function(xhr, status, error) {
          $('#content-area').html(error);
          console.log('AJAX error:', status, error);
        }
      });
    }

  });