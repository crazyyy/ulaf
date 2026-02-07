<?php
final class Split_Menu_Walker extends Walker_Nav_Menu {

    public $break_point = null;
    public $displayed = 0;
    private $logoWasSplitted = false;

    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0 ) {

        global $wp_query;

        $logo_main = get_theme_mod('logo', '');

        $logo_top_margin = get_theme_mod('logo_top_margin', 22);

        if( !isset( $this->break_point ) ) {
            $menu_elements = wp_get_nav_menu_items( $args->menu );
            $top_level_elements = 0;

            foreach( $menu_elements as $el ) {
                if( $el->menu_item_parent === '0' ) {
                    $top_level_elements++;
                }
            }
            $this->break_point = ceil( $top_level_elements / 2 ) + 1;
        }



        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $class_names = $value = '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
        $class_names = ' class="' . esc_attr( $class_names ) . '"';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
        $id = strlen( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';

        if( $item->menu_item_parent === '0' ) {
            $this->displayed++;
        }
//        echo $this->break_point .'= ' . $this->displayed . "\n";

        if( $this->break_point == $this->displayed  && $this->logoWasSplitted == false) {
            $this->logoWasSplitted = true;
            $output .= $indent . '</li></ul>
            <div class="logo-main kos-header-logo" style="margin-top: ' . intval($logo_top_margin) . 'px;">';
                if(empty($logo_main)):
                    $output .= $indent . '<a class="blogname" href="' . esc_url(home_url('/')) . '" title="' . esc_html_e('Home', 'splash') . '">
                        <h1>' . esc_attr(get_bloginfo('name')) . '</h1>
                    </a>';
                else:
                    $output .= $indent . '<a class="bloglogo" href="' . esc_url(home_url('/')) . '">
                        <img
                            src="' . esc_url( $logo_main ) . '"
                            style="width: ' . get_theme_mod( 'logo_width', '157' ) . 'px;"
                            title="' . esc_html_e('Home', 'splash') . '"
                            alt="' . esc_html_e('Logo', 'splash') . '"
                        />
                    </a>';
                endif;
            $output .= $indent . '</div>
            <ul class="header-menu stm-list-duty heading-font clearfix kos-header-menu"><li' . $id . $value . $class_names . '>';
        }
        else
            $output .= $indent . '<li' . $id . $value . $class_names .'>';

        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args, $id=0 );
    }
}