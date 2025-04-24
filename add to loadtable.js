$(document).on('click', '.save-btn', function () {
    var row = $(this).closest('tr');
    var rowData = table.row(row).data();

    // Prepare data for submission
    var data = {
        table: 'your_table',
        primaryKey: 'id',
        primaryValue: rowData.id,
        // Add other fields here
    };

    // Submit data via AJAX
    $.post('update_row.php', data, function (response) {
        var responseData = JSON.parse(response);
        if (responseData.status === 'success') {
            alert('Row updated successfully');
            table.ajax.reload(); // Reload table data
        } else {
            alert('Error: ' + responseData.message);
        }
    });
});
