$(document).ready(function() {
  $('#load-tables').click(function(e) {
    e.preventDefault();

   //$('#content-area').html('<p>Loading...</p>'); // Optional: show loading message
   //console.log('Tables link clicked'); // Check if this prints in console
    $.ajax({
      url: 'load_tables.php', // Server-side script that returns tables
      type: 'GET',
      success: function(data) {
        $('#content-area').html(data);
         // 2. Extract any <h4> from the returned content
        // var newHeading = $('<div>').html(data).find('h4').text();

         // 3. Replace the <h3> in the tab header with new heading
        // $('.tabs-header h3').text(newHeading);
      },
      error: function() {
        $('#content-area').html('<p class="text-danger">Error loading tables.</p>');
        console.log(error);
      }
    });
  });
});
// Event delegation in case table links are loaded dynamically
$(document).on('click', '.table-link', function(e) {
    e.preventDefault();
    
    const tableName = $(this).data('table');
    console.log('Clicked table:', tableName);
  
    // You can now do whatever you want, e.g.:
    // Load data from that table via AJAX:
    $.ajax({
      url: 'load_table_data.php',
      type: 'GET',
      data: {
        table: tableName,
        page: currentPage,
        limit: rowLimit
      },
      success: function(data) {
        $('#content-area').html(data);
      },
      error: function(xhr, status, error) {
        console.log('AJAX error:', status, error);
        $('#content-area').html('<p class="text-danger">Failed to load table data.</p>');
      }
    });
  });

  let currentPage = 1;
let rowLimit = 10; // Default rows per page

// Handle row-limit buttons (10, 20, 50, etc.)
$(document).on('click', '.row-limit', function () {
  rowLimit = $(this).data('limit');
  currentPage = 1;  // Reset to the first page
  loadTableData();
});

// Handle the "Prev" button click
$('#prev-page').click(function () {
  if (currentPage > 1) {
    currentPage--;
    loadTableData();
  }
});

// Handle the "Next" button click
$('#next-page').click(function () {
  currentPage++;
  loadTableData();
});

// Function to load table data using AJAX
function loadTableData() {
  $.ajax({
    url: 'load_table_data.php',
    type: 'GET',
    data: {
      table: tableName,  // Replace with the dynamic table name if needed
      limit: rowLimit,
      page: currentPage
    },
    success: function (data) {
      $('#content-area').html(data);
    }
  });
}

// Initial load
loadTableData();
