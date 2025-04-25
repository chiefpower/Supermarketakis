$(document).ready(function() {
    // Click event for loading sales
    $('#sales-actions').on('click', function (e) {
        e.preventDefault();
      
        const messageBox = document.getElementById('message');
      
        $.ajax({
          url: 'sales_actions.php',
          type: 'GET',
          success: function (response) {
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

      $(document).on('click', '#load-sales', function () {
        const selectedDate = $('#sales-date').val();
        const messageBox = $('#message');
      
        if (!selectedDate) {
          messageBox.html('<div class="alert alert-warning">Please select a date first.</div>');
          return;
        }
        //console.log('AJAX error:', selectedDate);
        $.ajax({
          url: 'sales_actions.php',
          type: 'POST',
          data: { selected_date: selectedDate },
          success: function (response) {
            $('#content-area').html(response);
            messageBox.html('');
          },
          error: function (xhr, status, error) {
            messageBox.html(`<div class="alert alert-danger">Error: ${error}</div>`);
          }
        });
      });
      
});