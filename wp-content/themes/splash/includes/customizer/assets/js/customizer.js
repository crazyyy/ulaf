jQuery(document).ready(function ($) {
    "use strict";

    $(".stm-icons-wrapper label").on("click", function () {
        $(this).closest("ul").find("li.active").removeClass("active");
        $(this).closest("li").addClass("active");
    });

    $(".stm-color-selector").wpColorPicker({
        change: _.throttle(function () {
            $(this).trigger('change');
        })
    });
    $(".stm-multiple-checkbox-wrapper input[type='checkbox']").on("change", function () {

        var checkbox_values = jQuery(this).parents(".customize-control").find("input[type='checkbox']:checked").map(function () {
            return this.value;
        }).get().join(",");

        $(this).parents(".stm-multiple-checkbox-wrapper").find("input[type='hidden']").val(checkbox_values).trigger("change");
    });

    $(".stm-customize-control input[id='header_position']").on("change", function() {

        console.log(currentTheme);

        if($(this).prop("checked")){
            $("#customize-control-sticky_logo").addClass("active");
        } else {
            $("#customize-control-sticky_logo").removeClass("active");
        }
    });

    if($(".stm-customize-control input[id='header_position']").prop("checked")){
        $("#customize-control-sticky_logo").addClass("active");
    }

    $(".stm-socials-wrapper input[type='text']").on("change, keyup", function () {

        var data = $(this).closest("form").serialize();

        $(this).parents('.stm-socials-wrapper').find('input[type="hidden"]').val(data).trigger('change');
    });

    var bg_image = $("#customize-control-bg_image input");
    var site_layout_checked = $("#customize-control-site_boxed input:checked");

    var colorCustom = $('#site_style').val();
    if(colorCustom=='site_style_custom') {
        $('#customize-control-site_style_secondary_color_listing,#customize-control-site_style_base_color_listing,#customize-control-site_style_base_color,#customize-control-site_style_secondary_color')
            .addClass('active');
    }

    $('#site_style').on('change', function(){
       if($(this).val()=='site_style_custom') {
           $('#customize-control-site_style_secondary_color_listing,#customize-control-site_style_base_color_listing,#customize-control-site_style_base_color,#customize-control-site_style_secondary_color')
               .addClass('active');
       } else {
           $('#customize-control-site_style_secondary_color_listing,#customize-control-site_style_base_color_listing,#customize-control-site_style_base_color,#customize-control-site_style_secondary_color')
               .removeClass('active');
       }
    });

    wp.customize('site_boxed', function (value) {
        value.bind(function (to) {
            if (to) {
                $("#customize-control-bg_image").show();
                $("#customize-control-custom_bg_image").show();
            } else {
                $("#customize-control-bg_image").hide();
                $("#customize-control-custom_bg_image").hide();
            }
        });
    });

    if (site_layout_checked.val()) {
        $("#customize-control-bg_image").show();
        $("#customize-control-custom_bg_image").show();
    } else {
        $("#customize-control-bg_image").hide();
        $("#customize-control-custom_bg_image").hide();
    }

    bg_image.on('change', function () {
        $(".theme_bg li.active").removeClass('active');
        $(this).closest('li').addClass('active');
    });

    $("#customize-control-bg_image input[name='bg_image']:checked").closest('li').addClass('active');

    if($("#show_socials_after_footer_img").is(":checked")) {
        showSocialsColorPicker(true);
        console.log("checked");
    } else {
        showSocialsColorPicker(false);
        console.log("unchecked");
    }

    $("#show_socials_after_footer_img").on("change", function () {
        if(this.checked){
            showSocialsColorPicker(true);
        } else {
            showSocialsColorPicker(false);
        }
    });

    function footerStyleUpdate(footer_style) {
        if (footer_style == 'footer_style_three') {
            $('#customize-control-footer_left_text, #customize-control-footer_right_text').hide();
            $('#customize-control-footer_center_text').show();
        } else {
            $('#customize-control-footer_left_text, #customize-control-footer_right_text').show();
            $('#customize-control-footer_center_text').hide();
        }
    }
    wp.customize('footer_style', function (value) {
        value.bind( footerStyleUpdate );
    });
    footerStyleUpdate( $('#footer_style').val() );


    function showSocialsColorPicker(show) {
        if(show){
            $("#customize-control-socials_after_footer_image_bg_color").show();
            $("#customize-control-socials_after_footer_image_icon_color").show();
        } else {
            $("#customize-control-socials_after_footer_image_bg_color").hide();
            $("#customize-control-socials_after_footer_image_icon_color").hide();
        }
    }
});