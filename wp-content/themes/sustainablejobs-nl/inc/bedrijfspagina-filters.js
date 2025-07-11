jQuery(document).ready(function($) {
    $('#filter_job_company, #filter_job_sector, #filter_certificering, #filter_job_tag').select2({
        width: '100%',
        allowClear: true,
        placeholder: function () {
            return $(this).data('placeholder');
        }
    });

    $('#bedrijfspagina-filter-form').on('change input', 'input, select', function () {
        const formData = $('#bedrijfspagina-filter-form').serialize();

        $.ajax({
            url: bedrijf_filter_ajax.ajaxurl,
            type: 'POST',
            data: formData + '&action=filter_bedrijfspaginas',
            success: function(response) {
                $('#bedrijf-resultaten').html(response);
            }
        });
    });

    // Trigger initial load
    $('#bedrijfspagina-filter-form').trigger('change');
});
