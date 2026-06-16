jQuery(document).ready(function($) {
    if (typeof em_ajax_obj !== 'undefined') {
        $.ajax({
            url: em_ajax_obj.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'em_get_exams',
                nonce: em_ajax_obj.nonce,
                page: 1,
                per_page: 10
            },
            success: function(response) {
                if (response.success) {
                    console.log("Exam data loaded successfully:", response.data.exams);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX request failed:", error);
            }
        });
    }
});