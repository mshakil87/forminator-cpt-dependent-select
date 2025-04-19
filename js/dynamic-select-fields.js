/**
 * Dynamic Select Fields Script
 * This script handles the dynamic population of select fields based on user selections.
 * It uses AJAX to fetch data from the server and updates the select fields accordingly.
 * It also initializes the Select2 plugin for better UI experience.
 * You can customize the AJAX actions and data structure as per your requirements.
 * Make sure to enqueue this script in your WordPress theme or plugin.
 */

jQuery(document).ready(function ($) {
    var modelSelect = $('select[name="select-2"]');
    updateSelectModels(modelSelect);
    // Listen for changes in the select-make field
    $('select[name="select-1"]').on('change', function () {
        var selectedMake = $(this).val();
        updateSelectModels(modelSelect);
        // Send AJAX request to fetch models
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'get_models_by_make',
                make: selectedMake,
            },
            success: function (response) {
                if (response.success) {

                    // Populate new options
                    $.each(response.data, function (index, option) {
                        modelSelect.append(
                            $('<option>', {
                                value: option.value,
                                text: option.label,
                            })
                        );
                    });
                    
                    // Reinitialize Select2 for the updated select field
                    modelSelect.trigger('change'); // Trigger change for Select2 to refresh
                }
            },
        });
    });
});

function updateSelectModels(modelSelect){
    modelSelect.empty(); // Clear existing options
    // Reinitialize Select2 for the updated select field
    modelSelect.trigger('change'); // Trigger change for Select2 to refresh
}