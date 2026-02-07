</div>
<!--close .container-->
<div class="post-content-esport">
    <div class="post-title-box">
        <?php get_template_part('partials/global/post-title-box') ?>
    </div>
    <div class="post-content">
        <div class="container">
            <?php
            the_content();
            ?>
            <div class="stm-post-meta-bottom">
                <div class="stm_post_tags">
                    <?php the_tags('<i class="icon-mg-tag"></i>', ', '); ?>
                </div>
                <div class="stm-share-this-wrapp">
                    <span class="stm-share-btn-wrapp">
                        <?php if (function_exists('A2A_SHARE_SAVE_pre_get_posts')) echo A2A_SHARE_SAVE_add_to_content(""); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php if (comments_open() || get_comments_number()) { ?>
        <div class="stm_post_comments">
            <div class="container">
                <?php comments_template(); ?>
            </div>
        </div>
    <?php } ?>
</div>
