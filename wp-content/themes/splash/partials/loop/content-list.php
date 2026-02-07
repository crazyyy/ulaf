<div class="col-md-12">
    <div <?php post_class( 'stm-single-post-loop stm-single-post-loop-list' ); ?>>
        <a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>" class="stm-image-link">

            <?php if( has_post_thumbnail() ): ?>
                <div class="image <?php if( get_post_format( get_the_ID() ) ) echo get_post_format( get_the_ID() ); ?>">
                    <div class="stm-plus"></div>
                    <?php
                    $imgSize = "stm-1170-650";
                    if( splash_is_layout( "af" ) || splash_is_layout( "hockey" ) ) $imgSize = "stm-350-250";
                    elseif( splash_is_layout( "sccr" ) ) $imgSize = "blog_list";
                    elseif( splash_is_layout( 'esport' ) ) $imgSize = 'blog_list_medium';

                    the_post_thumbnail( $imgSize, array( 'class' => 'img-responsive' ) ); ?>
                    <?php if( is_sticky( get_the_id() ) ): ?>
                        <div class="stm-sticky-post heading-font"><?php esc_html_e( 'Sticky Post', 'splash' ); ?></div>
                    <?php endif; ?>
                    <!--  Hockey  -->
                    <?php if( splash_is_layout( 'hockey' ) || splash_is_layout( 'esport' ) ): ?>
                        <div class="date normal_font">
                            <?php echo esc_attr( get_the_date() ); ?>
                        </div>
                    <?php endif; ?>
                    <!--  Hockey End -->
                </div>
            <?php endif; ?>

        </a>

        <div class="stm-post-content-inner">

            <?php if( splash_is_layout( "af" ) || splash_is_layout( "sccr" ) ): ?>
                <div class="date">
                    <?php echo esc_attr( get_the_date() ); ?>
                </div>
            <?php endif; ?>

            <a href="<?php the_permalink() ?>">
                <div class="title heading-font">
                    <?php the_title(); ?>
                </div>
            </a>

            <div class="clearfix">
                <?php if( splash_is_layout( "bb" ) ): ?>
                    <div class="date <?php echo ( !splash_is_layout( "af" ) ) ? "heading-font" : "normal_font"; ?>">
                        <?php echo esc_attr( get_the_date() ); ?>
                    </div>

                    <div class="post-meta heading-font">
                        <?php $comments_num = get_comments_number( get_the_id() ); ?>
                        <?php if( $comments_num ): ?>
                            <div class="comments-number">
                                <a href="<?php the_permalink() ?>#comments">
                                    <i class="fa fa-commenting"></i>
                                    <span><?php echo esc_attr( $comments_num ); ?></span>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="comments-number">
                                <a href="<?php the_permalink() ?>#comments">
                                    <i class="fa fa-commenting"></i>
                                    <span>0</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php $posttags = get_the_tags();
                        if( $posttags ): ?>
                            <div class="post_list_item_tags">
                                <?php $count = 0;
                                foreach( $posttags as $tag ): $count++; ?>
                                    <?php if( $count == 1 ): ?>
                                        <a href="<?php echo get_tag_link( $tag->term_id ); ?>">
                                            <i class="fa fa-tag"></i>
                                            <?php echo esc_html( $tag->name ); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="content">
                <?php the_excerpt(); ?>
                <!--  Hockey  -->
                <?php if( splash_is_layout( 'hockey' ) ): ?>
                    <div class="more_">
                        <a href="<?php the_permalink() ?>"><?php echo esc_html__( 'Read more', 'splash' ); ?><i
                                    class="fa fa-angle-right"></i></a>
                    </div>
                <?php endif; ?>
                <!--  Hockey End -->
            </div>

            <?php if( splash_is_layout( "af" ) || splash_is_layout( "sccr" ) ): ?>
                <div class="post-meta normal_font">
                    <?php $cat = wp_get_post_terms( get_the_ID(), "category" ); ?>
                    <?php
                    if( count( $cat ) > 0 ) :
                        $catList = "<ul>";
                        foreach( $cat as $k => $val ) {
                            $catList = $catList . "<li><a href='" . get_term_link( $val->term_id ) . "'>" . $val->name;
                            if( ( $k + 1 ) < count( $cat ) ) $catList = $catList . ", ";
                            $catList = $catList . "</a></li>";
                        }
                        $catList = $catList . "</ul>";
                        ?>

                        <div class="stm-cat-list-wrapp">
                            <i class="fa fa-folder-o" aria-hidden="true"></i>
                            <?php echo splash_sanitize_text_field( $catList ); ?>
                        </div>
                    <?php endif; ?>

                    <?php $comments_num = get_comments_number( get_the_id() ); ?>
                    <?php if( $comments_num ): ?>
                        <div class="comments-number">
                            <a href="<?php the_permalink() ?>#comments">
                                <i class="fa fa-comment-o" aria-hidden="true"></i>
                                <span><?php echo esc_attr( $comments_num ); ?><?php if( splash_is_af() ) esc_html_e( 'comments', 'splash' ); ?></span>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="comments-number">
                            <a href="<?php the_permalink() ?>#comments">
                                <i class="fa fa-comment-o" aria-hidden="true"></i>
                                <span>0 <?php if( splash_is_af() ) esc_html_e( 'comments', 'splash' ); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>