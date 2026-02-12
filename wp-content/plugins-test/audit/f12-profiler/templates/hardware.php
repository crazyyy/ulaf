<h2>
    <?php esc_html_e( 'Hardware', 'f12_profiler' ); ?>
</h2>

<table>
    <tr>
        <td>
            <?php esc_html_e( 'CPU', 'f12_profiler' ); ?>
        </td>
        <td>
            <?php
            $cpu_percentage = json_decode( $atts['hardware']['CPU'] );
            echo isset( $cpu_percentage[0] ) ? esc_html( $cpu_percentage[0] ) : '0';
            ?> %
        </td>
    </tr>
    <tr>
        <td>
            <?php esc_html_e( 'RAM', 'f12_profiler' ); ?>
        </td>
        <td>
            <?php
            echo isset( $atts['hardware']['RAM_USAGE'][0] ) ? esc_html( $atts['hardware']['RAM_USAGE'][0] ) : '0';
            ?> GB
            (<?php
            $ram_percentage = json_decode( $atts['hardware']['RAM_PERCENTAGE'] );
            echo isset( $ram_percentage[0] ) ? esc_html( $ram_percentage[0] ) : '0';
            ?>%)
            / <?php echo isset( $atts['hardware']['RAM_TOTAL'] ) ? esc_html( $atts['hardware']['RAM_TOTAL'] ) : '0'; ?> GB
        </td>
    </tr>
    <tr>
        <td>
            <?php esc_html_e( 'RAM (PHP)', 'f12_profiler' ); ?>
        </td>
        <td>
            <?php
            $ram_php = json_decode( $atts['hardware']['RAM_PHP'] );
            echo isset( $ram_php[0] ) ? esc_html( $ram_php[0] ) : '0';
            ?> MB
        </td>
    </tr>
</table>

<div class="hardware">
    <div class="cpu">
        <canvas id="canvas_cpu"></canvas>
        <div class="data" style="display:none;">
            <?php
            /**
             * Output the Hardware CPU Data as a JSON formatted string.
             */
            echo isset( $atts['hardware']['CPU'] ) ? esc_html( $atts['hardware']['CPU'] ) : '[]';
            ?>
        </div>
    </div>

    <div class="ram">
        <canvas id="canvas_ram"></canvas>
        <div class="data" style="display:none;">
            <?php
            /**
             * Output the Hardware RAM Data as a JSON formatted string.
             */
            echo isset( $atts['hardware']['RAM_PERCENTAGE'] ) ? esc_html( $atts['hardware']['RAM_PERCENTAGE'] ) : '[]';
            ?>
        </div>
    </div>

    <div class="ram_php">
        <canvas id="canvas_ram_php"></canvas>
        <div class="data" style="display:none;">
            <?php
            /**
             * Output the Hardware RAM PHP Data as a JSON formatted string.
             */
            echo isset( $atts['hardware']['RAM_PHP'] ) ? esc_html( $atts['hardware']['RAM_PHP'] ) : '[]';
            ?>
        </div>
    </div>
</div>