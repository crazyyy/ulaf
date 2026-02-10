<?php $option = get_option( 'urap-ui-style' ); ?>
<div class="wrap">
    <div class="modal-change-ui" role="dialog" aria-labelledby="ui-modal-title">
        <h2 id="ui-modal-title" class="screen-reader-text"><?php _e('Change UI Theme', 'url-rewrite-analyzer'); ?></h2>
        <form action="" method="post">
            <div class="input-wrapper">
                <label class="ui-select <?php echo $option === 'light' ? 'selected' : ''; ?>" for="light">
                    <span class="screen-reader-text"><?php _e('Light Theme', 'url-rewrite-analyzer'); ?></span>
                    <?php _e('Light', 'url-rewrite-analyzer'); ?>
                </label>
                <input type="radio" name="ui" id="light" value="light" <?php checked($option, 'light'); ?>>
            </div>
            <div class="input-wrapper">
                <label class="ui-select <?php echo $option === 'dark' ? 'selected' : ''; ?>" for="dark">
                    <span class="screen-reader-text"><?php _e('Dark Theme', 'url-rewrite-analyzer'); ?></span>
                    <?php _e('Dark', 'url-rewrite-analyzer'); ?>
                </label>
                <input type="radio" name="ui" id="dark" value="dark" <?php checked($option, 'dark'); ?>>
            </div>
        </form>
    </div>
    <div class="head-wrapper">
        <h2><?php _e('Url Rewrite Analyzer', 'url-rewrite-analyzer'); ?></h2>
        <button id="update-ui" aria-label="<?php _e('Change UI Theme', 'url-rewrite-analyzer'); ?>">
            <span><?php _e('Change UI', 'url-rewrite-analyzer'); ?></span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
        </button>
        <button id="flush-rewrite-rules" aria-label="<?php _e('Flush permalinks and refresh rewrite rules', 'url-rewrite-analyzer'); ?>">
            <span><?php _e('Flush permalinks', 'url-rewrite-analyzer'); ?></span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw" aria-hidden="true"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
        </button>
    </div>

    <?php if ( !$rewrite_rules ) : ?>
        <div class="error" role="alert"><p>
        <?php
        printf(
            __('Pretty permalinks are disabled, you can change this on <a href="%s">the Permalinks settings page</a>.', 'url-rewrite-analyzer'),
            admin_url('options-permalink.php')
        ); ?></p></div>
    <?php else : ?>

    <div id="_regex-search-bar" role="search">
        <label for="regex-tester" class="screen-reader-text"><?php _e('Test URL Pattern', 'url-rewrite-analyzer'); ?></label>
        <div class="prefix-url" aria-hidden="true"><?php echo esc_html($url_prefix); ?></div>
        <input type="text" id="regex-tester" placeholder="<?php esc_attr_e('Enter URL to test...', 'url-rewrite-analyzer'); ?>">
        <button class="clear" aria-label="<?php _e('Clear input', 'url-rewrite-analyzer'); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>

    <div class="table-responsive">
        <table class="widefat fixed" cellspacing="0" role="grid">
            <caption class="screen-reader-text"><?php _e('URL Rewrite Rules', 'url-rewrite-analyzer'); ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?php _e('Pattern', 'url-rewrite-analyzer'); ?></th>
                    <th scope="col"><?php _e('Substitution', 'url-rewrite-analyzer'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $rewrite_rules_ui as $idx => $rewrite_rule_ui ) : ?>
                <tr id="rewrite-rule-<?php echo $idx; ?>" class="rewrite-rule-line">
                    <?php if ( array_key_exists( 'error', $rewrite_rule_ui ) ) : ?>
                        <td colspan="2">
                            <?php echo esc_html($rewrite_rule_ui['pattern']); ?>
                            <p class="error" role="alert"><?php printf(__('Error parsing regex: %s', 'url-rewrite-analyzer'), esc_html($rewrite_rule_ui['error'])); ?></p>
                        </td>
                    <?php else : ?>
                        <td><?php echo $rewrite_rule_ui['print']; ?></td>
                        <td>
                            <?php
                            foreach ( $rewrite_rule_ui['substitution_parts'] as $substitution_part_ui ) {
                                if ( $substitution_part_ui['is_public'] ) {
                                    echo '<span class="queryvar-public">';
                                } else {
                                    echo '<span class="queryvar-unread" title="' . esc_attr(__('This query variable is not public and will not be saved', 'url-rewrite-analyzer')) . '">';
                                }
                                printf("%' 15s: <span class='queryvalue'>%s</span>\n", 
                                    esc_html($substitution_part_ui['query_var']), 
                                    $substitution_part_ui['query_value_ui']
                                );
                                echo '</span>';
                            } ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1) : ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num">
                <?php printf(_n('%s item', '%s items', $total_rules, 'url-rewrite-analyzer'), number_format_i18n($total_rules)); ?>
            </span>
            <span class="pagination-links">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $current_page
                ));
                ?>
            </span>
        </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<div class="urap-btt-wrapper">
    <button aria-label="<?php _e('Back to top', 'url-rewrite-analyzer'); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-up" aria-hidden="true"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
    </button>
</div>
