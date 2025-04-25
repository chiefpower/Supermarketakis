$(document).ready(function() {
 // Click event for loading sales
  //  $('#sales-actions').click(function(e) {
   // $(document).on('click', '#sales-actions', function (e) {
    $('#sales-actions').on('click', function (e) {
        e.preventDefault();
      
        const messageBox = document.getElementById('message');
        console.log('Sales clicked');
      
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
});