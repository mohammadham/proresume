(function ($) {
    "use strict";
    $(document).on('change', '#hero_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showHeroImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })
    $(document).on('change', '#about_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showAboutImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })
    $(document).on('change', '#skills_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showSkillsImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })
    $(document).on('change', '#achievement_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showAchievementImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })

    $(document).on('change', '#call_to_action_bg_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showCallSectionBgImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })
    $(document).on('change', '#call_to_action_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showCallSectionImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })
    $(document).on('change', '#hero_background_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showHeroBgImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })
    $(document).on('change', '#features_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showFeaturesImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })
    $(document).on('change', '#hero_video_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showHeroVideoImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })
    $(document).on('change', '#about_left_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showAboutLeftImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })
    $(document).on('change', '#about_right_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showAboutRightImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })
    $(document).on('change', '#about_middle_image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showAboutMiddleImage img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })

    let fd = new FormData();


    $(".image-cross-btn").on('click', function () {
        swal({
            title: "Are you sure ?",
            text: "You won\'t be able to revert this!",
            type: 'warning',
            buttons: {
                confirm: {
                    text: "Yes, delete it",
                    className: 'btn btn-success'
                },
                cancel: {
                    visible: true,
                    text: "Cancel",
                    className: 'btn btn-danger'
                }
            }
        }).then((Delete) => {
            if (Delete) {
                $(".request-loader").addClass('show');

                $.post(imageRemoveRoute, {
                    langId: langId,
                    userId: userId,
                    type: $(this).data('type')
                },
                    function (data) {
                        if (data == "success") {
                            location.reload();
                        }
                    })

            } else {
                swal.close();
            }
        });
    });
})(jQuery);
