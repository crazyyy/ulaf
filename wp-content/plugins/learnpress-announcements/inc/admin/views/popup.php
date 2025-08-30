<?php
/**
 * Admin popup course view.
 *
 * @since 3.0.0
 */
?>

<div class="lp-courses-popup-window">
    <div class="message-box-wrap">
        <div class="lp-modal-search-items">
            <div class="modal-inner">
                <header>
                    <div class="learnpress-course-notices">
                        <div class="message-box-content">
                            <p><strong><?php _e( 'Course Lists', 'learnpress-announcements' ); ?></strong></p>
                        </div>
                        <input class="lp-course-search" type="text"
                               placeholder="<?php _e( 'Type here to search item', 'learnpress-announcements' ); ?>">
                    </div>
                </header>
                <article>
                    <ul class="lp-list-items">
                    </ul>
                </article>
                <footer>
                    <input type="checkbox" class="chk-checkall" disabled="disabled"/>
                    <div>
                        <button class="lp-add-item button" disabled="disabled"
                                data-text="<?php _e( 'Select', 'learnpress-announcements' ); ?>"><?php _e( 'Select', 'learnpress-announcements' ); ?></button>
                        <button class="lp-add-item close button" disabled="disabled"
                                data-text="<?php _e( 'Select', 'learnpress-announcements' ); ?>"><?php _e( 'Select', 'learnpress-announcements' ); ?></button>
                        <button class="close-modal button"><?php _e( 'Close', 'learnpress-announcements' ); ?></button>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</div>