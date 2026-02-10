jQuery(document).ready(function($) {
    $('#delete-all-data').click(function() {
        // First confirm if the user wants to delete the data
        if (confirm('Are you sure you want to delete all data?')) {
            // Perform the AJAX request
            $.ajax({
                url: WPTAjax.ajax_url, // Use WPTAjax to get the correct AJAX URL
                type: 'POST',
                data: {
                    action: 'plugtracker_delete_data', // AJAX action
                    nonce: WPTAjax.nonce // The nonce for security
                },
                success: function(response) {
                    // Check if the response was successful
                    if (response.success) {
                        alert('All data has been successfully deleted!');
                    } else {
                        alert('Error deleting data.');
                    }
                },
                error: function() {
                    alert('Request failed.');
                }
            });
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            // Add active class to the clicked tab and corresponding content
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });
});