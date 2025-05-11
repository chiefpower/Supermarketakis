$(document).ready(function() {
    // Click event for loading low quantity alerts
    $('#functions-act').on('click', function (e) {
        e.preventDefault();
        //console.log('AJAX errorafaf:');
        const messageBox = document.getElementById('message');
      
        $.ajax({
          url: 'functions.php',
          type: 'GET',
          success: function (response) {
            //console.log('AJAXsssss:', response);
            // Just inject the returned HTML
            $('#content-area').html(response);
      
            // Optional: Add a success alert manually
            if (messageBox) {
              messageBox.innerHTML = `<div class="alert alert-success">Sales data loaded successfully.</div>`;
            }
          },
          error: function (xhr, status, error) {
            if (messageBox) {
              messageBox.innerHTML = `<div class="alert alert-danger">Error loading sales data: ${error}</div>`;
            }
            console.error('AJAX error:', error);
          }
        });
      });  

    //  $(document).on('click', '#show-orders', function () {
   //     $('#function-result').load('load_orders.php');
        //console.log('AJAXsssss:');
    //  });

      // Initial load
      $(document).on('click', '#show-orders', function () {
         const tableName = 'orders';
        loadTableData(tableName);
      //  loadOrders(); // default to page 1
      });

      // Function to load table data using AJAX
    function loadTableData(tableName) {
      if (!tableName) return; // Prevent loading if no table is selected
  
      currentTable = tableName; // Store the current table name
      // Check if action toggle is enabled
      const isEditMode = $('#action-toggle').is(':checked');
      const rowLimit = 10;
      const currentPage = 1;

      const targetUrl = 'table_actions.php';

      $.ajax({
        url: targetUrl,
        type: 'GET',
        data: {
          table: tableName,
          limit: rowLimit,
          page: currentPage
          
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

      // Pagination click
      $(document).on('click', '.order-page-link', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        loadOrders(page);
      });
      
      $(document).on('click', '#show-procedures', function () {
        $('#function-result').load('load_procedures.php');
      });
      
      $(document).on('click', '#show-triggers', function () {
        $('#function-result').load('load_triggers.php');
      });

      $(document).on('click', '#show-low-inv-alerts', function () {
       // $('#function-result').load('load_alerts.php');
       //console.log('AJAX111sssss:');
       $('#function-result').load('functions.php?low_inv=1');
      });
      
      $(document).on('click', '#show-stor-proc', function () {
        const procName = $(this).data('name');
        $('#function-result').load('load_procedures.php?view=' + encodeURIComponent(procName));
      });

      $(document).on('click', '#edit-stor-proc', function () {
        const procName = $(this).data('name');
        $('#function-result').load('load_procedures.php?edit=' + encodeURIComponent(procName));
      });

      $(document).on('click', '#cancel-edit-proc', function () {
        const procName = $(this).data('name');
        $('#function-result').load('load_procedures.php');
      });

      function loadOrders(page = 1, limit = 10) {
        $('#function-result').html('<div class="text-muted">Loading orders...</div>');
        $.get(`load_orders.php?page=${page}&limit=${limit}`, function (data) {
          $('#function-result').html(data);
        }).fail(function () {
          $('#function-result').html("<div class='alert alert-danger'>Error loading orders.</div>");
        });
      }

      

});