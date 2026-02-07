<?php
/**
 * Event Blocks
 *
 * @author        ThemeBoy
 * @package    SportsPress/Templates
 * @version     2.0
 */

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$defaults = array(
    'id' => null,
    'title' => false,
    'status' => 'default',
    'date' => 'default',
    'date_from' => 'default',
    'date_to' => 'default',
    'league' => null,
    'season' => null,
    'venue' => null,
    'team' => null,
    'number' => -1,
    'link_teams' => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
    'link_events' => get_option( 'sportspress_link_events', 'yes' ) == 'yes' ? true : false,
    'paginated' => get_option( 'sportspress_event_blocks_paginated', 'yes' ) == 'yes' ? true : false,
    'rows' => get_option( 'sportspress_event_blocks_rows', 5 ),
    'order' => 'default',
    'show_all_events_link' => false,
    'show_title' => get_option( 'sportspress_event_blocks_show_title', 'no' ) == 'yes' ? true : false,
    'show_league' => get_option( 'sportspress_event_blocks_show_league', 'no' ) == 'yes' ? true : false,
    'show_season' => get_option( 'sportspress_event_blocks_show_season', 'no' ) == 'yes' ? true : false,
    'show_venue' => get_option( 'sportspress_event_blocks_show_venue', 'no' ) == 'yes' ? true : false,
    'hide_if_empty' => false,
);

extract( $defaults, EXTR_SKIP );

$calendar = new SP_Calendar( $id );
if( $status != 'default' )
    $calendar->status = $status;
if( $date != 'default' )
    $calendar->date = $date;
if( $date_from != 'default' )
    $calendar->from = $date_from;
if( $date_to != 'default' )
    $calendar->to = $date_to;
if( $league )
    $calendar->league = $league;
if( $season )
    $calendar->season = $season;
if( $venue )
    $calendar->venue = $venue;
if( $team )
    $calendar->team = $team;
if( $order != 'default' )
    $calendar->order = $order;
$data = $calendar->data();

if( $hide_if_empty && empty( $data ) ) return;

if( $show_title && false === $title && $id ):
    $caption = $calendar->caption;
    if( $caption )
        $title = $caption;
    else
        $title = get_the_title( $id );
endif;

if( $title )
    echo '<h4 class="sp-table-caption">' . $title . '</h4>';
?>
<div class="sp-template sp-template-event-blocks sp-stm-template-event-blocks">
    <div class="sp-table-wrapper">
        <?php foreach( $data as $event ):

            $teams = array_unique( get_post_meta( $event->ID, 'sp_team' ) );
            $teams = array_filter( $teams, 'sp_filter_positive' );
            $team_results = get_post_meta( $event->ID, 'sp_results', false );
            $permalink = get_post_permalink( $event, false, true );
            $results = get_post_meta( $event->ID, 'sp_results', true );
            $point_system = splash_get_sportpress_points_system();

            if( count( $teams ) > 1 ):
                $team_1_id = $teams[ 0 ];
                $team_2_id = $teams[ 1 ];

                $logos = array();

                $j = 0;
                foreach( $teams as $team ):
                    $j++;
                    if( has_post_thumbnail( $team ) ):
                        if( $link_teams ):
                            $logo = '<a class="team-logo logo-' . ( $j % 2 ? 'odd' : 'even' ) . '" href="' . get_permalink( $team, false, true ) . '" title="' . get_the_title( $team ) . '">' . get_the_post_thumbnail( $team, 'stm-200-200' ) . '</a>';
                        else:
                            $logo = '<span class="team-logo logo-' . ( $j % 2 ? 'odd' : 'even' ) . '" title="' . get_the_title( $team ) . '">' . get_the_post_thumbnail( $team, 'stm-200-200' ) . '</span>';
                        endif;
                        $logos[] = $logo;
                    endif;
                endforeach; ?>

                <div
                        class="stm-single-block-event-list sp-stm-template-event-blocks-<?php echo esc_attr( $event->post_status ); ?>">
                    <a href="<?php echo esc_url( get_the_permalink( $event->ID ) ); ?>" class="stm-no-decor">
                        <div class="stm-single-block-event-list-top">
                            <div
                                    class="time h6"><?php echo esc_attr( get_the_time( get_option( 'date_format' ), $event ) ); ?></div>
                            <?php if( $show_venue ): $venues = get_the_terms( $event, 'sp_venue' );
                                if( $venues ): $venue = array_shift( $venues ); ?>
                                    <div class="venue h6"><?php echo sanitize_text_field( $venue->name ); ?></div>
                                <?php endif; endif; ?>
                            <?php if( $event->post_status == 'future' ): ?>
                                <div class="stm-future-event-list-time">
                                    <?php
                                    $date = new DateTime( get_the_time( 'Y/m/d H:i:s', $event->ID ) );
                                    if( $date ) {
                                        $date = $date->format( 'Y-m-d H:i:s' );
                                    }
                                    ?>
                                    <time class="heading-font" datetime="<?php echo esc_attr( $date ) ?>"
                                          data-countdown="<?php echo esc_attr( str_replace( "-", "/", $date ) ) ?>"></time>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="stm-single-block-unit">
                            <div class="stm-team-logo left">
                                <?php
                                if( !empty( $logos[ 0 ] ) ):
                                    echo wp_kses_post( $logos[ 0 ] );
                                endif;
                                ?>
                            </div>

                            <div class="stm-teams-info heading-font">


                                <div class="stm-title-team">
                                    <?php echo esc_attr( get_the_title( $team_1_id ) ); ?>
                                </div>

                                <div class="stm-team-results-outer">
                                    <?php if( !empty( $team_results[ 0 ] ) ): ?>
                                        <?php if( !empty( $team_results[ 0 ][ $team_1_id ] ) ): ?>
                                            <?php if( isset( $team_results[ 0 ][ $team_1_id ][ 'outcome' ] ) and !empty( $team_results[ 0 ][ $team_1_id ][ 'outcome' ][ 0 ] ) ): ?>
                                                <?php if( $team_results[ 0 ][ $team_1_id ][ 'outcome' ][ 0 ] == 'win' ): ?>
                                                    <div
                                                            class="stm-latest-result-win-label <?php echo ( splash_is_layout( "af" ) ) ? "heading-font" : "normal-font" ?>"><?php esc_html_e( 'win', 'splash' ) ?></div>
                                                <?php else: ?>
                                                    <div
                                                            class="stm-latest-result-lose-label <?php echo ( splash_is_layout( "af" ) ) ? "heading-font" : "normal-font" ?>"><?php printf( _x( '%s', 'Outcome for team', 'splash' ), $team_results[ 0 ][ $team_1_id ][ 'outcome' ][ 0 ] ); ?></div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div
                                                        class="stm-latest-result-lose-label"><?php esc_html_e( '- -', 'splash' ) ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div
                                                class="stm-latest-result-lose-label"><?php esc_html_e( '- -', 'splash' ) ?></div>
                                    <?php endif; ?>

                                    <?php if( !empty( $team_results[ 0 ] ) ): ?>
                                        <?php if( !empty( $team_results[ 0 ][ $team_1_id ] ) and !empty( $team_results[ 0 ][ $team_2_id ] ) ): ?>
                                            <?php if( isset( $team_results[ 0 ][ $team_1_id ][ $point_system ] ) and isset( $team_results[ 0 ][ $team_2_id ][ $point_system ] ) ): ?>
                                                <?php if( empty( $team_results[ 0 ][ $team_1_id ][ $point_system ] ) and empty( $team_results[ 0 ][ $team_2_id ][ $point_system ] ) ): ?>
                                                    <div
                                                            class="stm-latest-result_result"><?php esc_html_e( '- VS -', 'splash' ); ?></div>
                                                <?php else: ?>
                                                    <div
                                                            class="stm-latest-result_result"><?php echo esc_attr( $team_results[ 0 ][ $team_1_id ][ $point_system ] . ' / ' . $team_results[ 0 ][ $team_2_id ][ $point_system ] ); ?></div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div
                                                class="stm-latest-result_result"><?php esc_html_e( '- VS -', 'splash' ); ?></div>
                                    <?php endif; ?>

                                    <?php if( !empty( $team_results[ 0 ] ) ): ?>
                                        <?php if( !empty( $team_results[ 0 ][ $team_2_id ] ) ): ?>
                                            <?php if( isset( $team_results[ 0 ][ $team_2_id ][ 'outcome' ] ) and !empty( $team_results[ 0 ][ $team_2_id ][ 'outcome' ][ 0 ] ) ): ?>
                                                <?php if( $team_results[ 0 ][ $team_2_id ][ 'outcome' ][ 0 ] == 'win' ): ?>
                                                    <div
                                                            class="stm-latest-result-win-label normal-font"><?php esc_html_e( 'win', 'splash' ) ?></div>
                                                <?php else: ?>
                                                    <div
                                                            class="stm-latest-result-lose-label normal-font"><?php printf( _x( '%s', 'Outcome for team', 'splash' ), $team_results[ 0 ][ $team_2_id ][ 'outcome' ][ 0 ] ); ?></div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div
                                                        class="stm-latest-result-lose-label"><?php esc_html_e( '- -', 'splash' ) ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div
                                                class="stm-latest-result-lose-label"><?php esc_html_e( '- -', 'splash' ) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="stm-title-team opponent">
                                    <?php echo esc_attr( get_the_title( $team_2_id ) ); ?>
                                </div>

                            </div>

                            <div class="stm-team-logo right">
                                <?php
                                if( !empty( $logos[ 1 ] ) ):
                                    echo wp_kses_post( $logos[ 1 ] );
                                endif;
                                ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>