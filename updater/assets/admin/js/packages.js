(function ($) {
    "use strict";
    $('input[name="is_trial"]').on('change', function () {
        if ($(this).val() == 1) {
            $('#trial_day').show();
        } else {
            $('#trial_day').hide();
        }
        $('#trial_days').val(null);
    });
})(jQuery);


"use strict";
$(document).ready(function () {

    // for blog feature
    if ($('#vcards').not(":checked")) {
        $(".v-card-box").hide();

    }
    if ($('#vcards').is(":checked")) {
        $(".v-card-box").show();
    }

    // for blog feature
    if ($('#blogs').not(":checked")) {
        $(".blog-number").hide();
        $(".blog-category-number").hide();
    }
    if ($('#blogs').is(":checked")) {
        $(".blog-number").show();
        $(".blog-category-number").show();
    }
    // for skill feature 
    if ($('#skills').not(":checked")) {
        $(".skill-number").hide();
    }
    if ($('#skills').is(":checked")) {
        $(".skill-number").show();
    }

    // for Service feature 
    if ($('#services').not(":checked")) {
        $(".service-number").hide();
    }
    if ($('#services').is(":checked")) {
        $(".service-number").show();
    }
    // for portfolio feature
    if ($('#portfolios').not(":checked")) {
        $(".portfolio-number").hide();
        $(".portfolio-category-number").hide();
    }
    if ($('#portfolios').is(":checked")) {
        $(".portfolio-number").show();
        $(".portfolio-category-number").show();
    }

    // for education feature
    if ($('#expriences').not(":checked")) {
        $(".exprience-number").hide();
        $(".education-number").hide();
    }
    if ($('#expriences').is(":checked")) {
        $(".exprience-number").show();
        $(".education-number").show();
    }




    $(".selectgroup-input").on('click', function () {

        var val = $(this).val()
        if (val == 'vCard') {
            if ($(this).is(":checked")) {
                $(".v-card-box").show();
            } else {
                $(".v-card-box").hide();
            }
        } else if (val == 'Blog') {
            if ($(this).is(":checked")) {
                $(".blog-number").show();
                $(".blog-category-number").show();
            } else {
                $(".blog-number").hide();
                $(".blog-category-number").hide();
            }
        } else if (val == 'Skill') {
            if ($(this).is(":checked")) {
                $(".skill-number").show();
            } else {
                $(".skill-number").hide();
            }
        } else if (val == 'Service') {
            if ($(this).is(":checked")) {
                $(".service-number").show();
            } else {
                $(".service-number").hide();
            }
        } else if (val == 'Portfolio') {
            if ($(this).is(":checked")) {
                $(".portfolio-number").show();
                $(".portfolio-category-number").show();
            } else {
                $(".portfolio-number").hide();
                $(".portfolio-category-number").hide();
            }
        } else if (val == 'Experience') {
            if ($(this).is(":checked")) {
                $(".exprience-number").show();
                $(".education-number").show();
            } else {
                $(".exprience-number").hide();
                $(".education-number").hide();
            }
        }
    })
});
