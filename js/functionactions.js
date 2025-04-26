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
            console.log('AJAXsssss:', response);
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