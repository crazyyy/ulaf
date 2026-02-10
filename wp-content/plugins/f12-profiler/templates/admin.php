<div class="f12-profiler">
    <div class="headline-container">
        <div class="logo">
            <img src="<?php echo esc_url( plugins_url( 'assets/Forge12-Bildmarke.png', dirname(__FILE__) ) ); // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>"
                 alt="<?php esc_attr_e( 'Forge12 Interactive', 'f12_profiler' ); ?>"
                 title="<?php esc_attr_e( 'Forge12 Interactive', 'f12_profiler' ); ?>"/>
        </div>
        <h1><?php esc_html_e( 'F12-Profiler', 'f12_profiler' ); ?></h1>
    </div>
    <form action="" method="post" name="f12_profiler_options">
        <div class="row">
            <div class="left">
                <table width="100%">
                    <tr>
                        <td colspan="2">
                            <h2><?php esc_html_e( 'Allgemeine Einstellungen', 'f12_profiler' ); ?></h2>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="active">
                                <?php esc_html_e( 'Activate?', 'f12_profiler' ); ?>
                            </label>
                        </td>
                        <td>
                            <input type="checkbox" name="active" value="1" id="active"
                                <?php checked( $atts['active'], 1 ); ?>/>
                            <span>
                                <?php esc_html_e( 'Enable / Disable the F12-Profiler plugin', 'f12_profiler' ); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="page_metrics">
                                <?php esc_html_e( 'Show Page Resource Metrics?', 'f12_profiler' ); ?>
                            </label>
                        </td>
                        <td>
                            <input type="checkbox" name="page_metrics" value="1" id="page_metrics"
                                <?php checked( $atts['page_metrics'], 1 ); ?>/>
                            <span>
                                <?php esc_html_e( 'Enable / Disable Page Resource Metrics', 'f12_profiler' ); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="hardware_metrics">
                                <?php esc_html_e( 'Show Hardware Metrics?', 'f12_profiler' ); ?>
                            </label>
                        </td>
                        <td>
                            <input type="checkbox" name="hardware_metrics" value="1" id="hardware_metrics"
                                <?php checked( $atts['hardware_metrics'], 1 ); ?>/>
                            <span>
                                <?php esc_html_e( 'Enable / Disable Hardware Metrics', 'f12_profiler' ); ?>
                            </span>
                            <p>
                                <?php esc_html_e( 'Important: These functions are in beta and may affect the performance for users logged in within the backend of your system.', 'f12_profiler' ); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <!-- Nonce hinzufügen -->
                            <?php wp_nonce_field( 'f12_profiler_save_action', 'f12_profiler_nonce' ); ?>

                            <input type="submit" name="f12_profiler_save" class="button button-primary"
                                   value="<?php esc_attr_e( 'Save', 'f12_profiler' ); ?>"/>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="right">
                <div class="tipps">
                    <h2>
                        <?php esc_html_e( 'Tipps', 'f12_profiler' ); ?>
                    </h2>
                    <ul>
                        <li>
                            <?php esc_html_e( 'You can let the plugin enabled; it will only affect administrators.', 'f12_profiler' ); ?>
                        </li>
                        <li>
                            <?php esc_html_e( 'The color of the times indicates how they affect your loading time. Red should be optimized.', 'f12_profiler' ); ?>
                        </li>
                        <li>
                            <?php esc_html_e( 'Enable the resource metrics to display the loading time of each file.', 'f12_profiler' ); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>

    <?php
    if ( isset( $atts['hardware'] ) ) {
        echo wp_kses_post( $atts['hardware'] );
    }
    ?>
</div>