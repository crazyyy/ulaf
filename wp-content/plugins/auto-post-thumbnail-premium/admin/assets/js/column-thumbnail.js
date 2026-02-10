/**
 * This function:
 *
 * Generates a custom image uploader / selector tied to a post where the click action originated
 * Upon clicking "Use as thumbnail" the image selected is set to be the post thumbnail
 * A thumbnail image is then shown in the All Posts / All Pages / All Custom Post types Admin Dashboard view
 *
 * @since 1.0.0
 *
 * global ajaxurl, apt_thumb - language array
 */
(function($){
    jQuery(document).ready(function($){
        //Клик по изображению. Выбор одного изображения
        jQuery(document).on('click', '#wapt_thumbs div.wapt-image-box', function(event) {
            var $wapt_grid_item = jQuery('#wapt_thumbs div.wapt-image-box');
            var $btnuse = jQuery('#doit');

            var $img = jQuery(this);
            $wapt_grid_item.removeClass("wapt-image-box-checked");
            $btnuse.removeAttr('disabled');
            $img.toggleClass("wapt-image-box-checked");
        });

        //Замена картинки
        jQuery(document).on('click', '#doit', function(event) {
            var $thumb_id = null;
            var $postid;
            var $wpnonce;
            var $image = null;

            $checked_item = jQuery('#wapt_thumbs div.wapt-image-box-checked');
            jQuery.each($checked_item, function(index, value){
                $postid = value.dataset.postid;
                $wpnonce = value.dataset.nonce;
                if(isEmpty(value.dataset.thumbid))
                    $image = value.dataset.src;
                else $thumb_id = value.dataset.thumbid;
            });

            var $loader = jQuery('#loader_'+$postid);
            var $img = jQuery('#modal-init-js_'+$postid);

            $img.hide();
            $loader.show();
            tb_remove();

            // AJAX запрос для обновления картинки поста
            jQuery.post(ajaxurl, {
                action: 'apt_replace_thumbnail',
                thumbnail_id: $thumb_id,
                image: $image,
                post_id: $postid,
                _ajax_nonce: $wpnonce,
            }).done(function (thumb_url) {
                $loader.hide();
                $img.show();
                jQuery( '.apt-image', '#post-' + $postid ).html( thumb_url );
                jQuery( '.apt-image', '#post-' + $postid ).hide().fadeIn();

            });
        });

        function isEmpty(str) {
            return (typeof str === "undefined" || str === null || str ===  "");
        }
    });
})(jQuery);