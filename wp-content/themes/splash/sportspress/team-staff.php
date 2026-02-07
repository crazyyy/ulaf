<?php
/**
 * Team Staff
 *
 * @author        ThemeBoy
 * @package    SportsPress/Templates
 * @version   2.5.5
 */

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if( !isset( $id ) )
    $id = get_the_ID();

$team = new SP_Team( $id );
$members = $team->staff();
$link_staff = get_option( 'sportspress_team_link_staff', 'no' ) === 'yes' ? true : false;

if( !empty( $members ) ): ?>
    <h4 class="sp-table-caption">
        <?php esc_html_e( 'Coaching Staff', 'splash' ); ?>
    </h4>
    <div class="stm-team-staff-list">
        <div class="stm-team-staff-list-inner clearfix">
            <?php foreach( $members as $staff ):
                $id = $staff->ID;
                $name = $staff->post_title;
                $countries = SP()->countries->countries;

                $staff = new SP_Staff( $id );
                $role = $staff->role();
                $nationalities = $staff->nationalities();
                $nationality = '';
                if( !empty( $nationalities ) and !empty( $nationalities[ 0 ] ) ) {
                    $nationality = $nationalities[ 0 ];
                    if( 2 == strlen( $nationality ) ):
                        $legacy = SP()->countries->legacy;
                        $nationality = strtolower( $nationality );
                        $nationality = sp_array_value( $legacy, $nationality, null );
                    endif;
                    $country_name = sp_array_value( $countries, $nationality, null );
                }

                $role_name = '';

                if( $role ) {
                    $role_name = $role->name;
                }
                if( !splash_is_af() && !splash_is_layout( 'basketball_two' ) && !splash_is_layout( 'hockey' ) && !splash_is_layout( 'esport' ) ):
                    ?>
                    <div class="stm-single-staff">
                        <div class="inner">
                            <div class="stm-red heading-font"><?php echo esc_html( $role_name ); ?></div>
                            <h4 class="sp-staff-name heading-font"><?php echo esc_html( $name ); ?></h4>
                            <?php if( !empty( $country_name ) ): ?>
                                <div class="nationality">
                                    (<?php esc_html_e( 'Nationality', 'splash' ); ?>
                                    : <?php echo esc_html( $country_name ); ?>)
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!--hockey -->
                <?php elseif( splash_is_layout( 'hockey' ) ): ?>
                    <div class="stm-single-staff">
                        <div class="inner">
                            <div class="col-img">
                                <div class="stm-staff-img-wrapp">
                                    <?php echo get_the_post_thumbnail( $id, 'full' ) ?>
                                </div>
                            </div>
                            <div class="col-txt">
                                <div class="stm-red heading-font"><?php echo esc_attr( $role_name ); ?></div>
                                <h4 class="sp-staff-name heading-font"><?php echo esc_attr( $name ); ?></h4>
                                <?php if( !empty( $country_name ) ): ?>
                                    <div class="nationality">
                                        (<?php esc_html_e( 'Nationality', 'splash' ); ?>
                                        : <?php echo esc_attr( $country_name ); ?>)
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!--hockey end -->
                <?php
                elseif( splash_is_layout( 'esport' ) ):
                    $pastTeamsArray = $staff->past_teams();
                    $pastTeamsList = array();
                    foreach( $pastTeamsArray as $k => $val ) {
                        $pastTeamsList[ $k ] = sp_get_team_name( $val );
                    }
                    ?>
                    <div class="esport-single-staff-wrap">


                        <div class="esport-single-staff <?php if( !has_post_thumbnail( $id ) ) echo 'no_image'; ?>">
                            <?php if( has_post_thumbnail( $id ) ): ?>
                                <div class="esport-single-staff__image">
                                    <a href="<?php the_permalink( $id ); ?>">
                                        <?php echo get_the_post_thumbnail( $id, 'player_photo' ) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="esport-single-staff__content">
                                <a href="<?php the_permalink( $id ); ?>" class="staff-name"><?php echo esc_html( $name ); ?></a>
                                <div class="staff-role">
                                    <?php echo esc_html( $role_name ); ?>
                                </div>
                                <?php
                                $age = get_post_meta( $id, 'staff_age', true );
                                if( !empty( $age ) ):
                                    ?>
                                    <div class="esport-single-staff__content__info">
                                        <div class="staff-label">
                                            <?php esc_html_e( 'Age:', 'splash' ); ?>
                                        </div>
                                        <div class="value">
                                            <?php echo esc_html( $age ); ?>
                                        </div>
                                    </div>
                                <?php
                                endif;
                                $collage = get_post_meta( $id, 'staff_college', true );
                                if( !empty( $collage ) ): ?>
                                    <div class="esport-single-staff__content__info">
                                        <div class="staff-label">
                                            <?php esc_html_e( 'College:', 'splash' ); ?>
                                        </div>
                                        <div class="value">
                                            <?php
                                            echo esc_html( $collage );
                                            ?>
                                        </div>
                                    </div>
                                <?php endif;
                                $staff_experience = get_post_meta( $id, 'staff_experience', true );
                                if( !empty( $staff_experience ) ): ?>
                                    <div class="esport-single-staff__content__info">
                                        <div class="staff-label">
                                            <?php esc_html_e( 'Experience:', 'splash' ); ?>
                                        </div>
                                        <div class="value">
                                            <?php
                                            echo esc_html( $staff_experience );
                                            ?>
                                        </div>
                                    </div>
                                <?php endif;
                                if( !empty( $country_name ) ): ?>
                                    <div class="esport-single-staff__content__info">
                                        <div class="staff-label">
                                            <?php esc_html_e( 'Nationality:', 'splash' ); ?>
                                        </div>
                                        <div class="value">
                                            <?php
                                            echo esc_html( $country_name );
                                            ?>
                                        </div>
                                    </div>
                                <?php endif;
                                if( !empty( $pastTeamsList ) ): ?>
                                    <div class="esport-single-staff__content__info">
                                        <div class="staff-label">
                                            <?php esc_html_e( 'Past Teams:', 'splash' ); ?>
                                        </div>
                                        <div class="value">
                                            <?php echo implode( ", ", $pastTeamsList ); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php
                else:
                    $pastTeamsArray = $staff->past_teams();
                    $pastTeamsList = array();
                    foreach( $pastTeamsArray as $k => $val ) {
                        $pastTeamsList[ $k ] = sp_get_team_name( $val );
                    }
                    ?>
                    <div class="stm-single-staff">
                        <div class="inner">
                            <h4 class="sp-staff-name heading-font"><?php echo esc_attr( $name ); ?></h4>
                            <div class="stm-staff-info-wrapp">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="stm-staff-img-wrapp">
                                            <?php echo get_the_post_thumbnail( $id, 'full' ) ?>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-8">
                                        <div class="stm-staff-info">
                                            <div class="stm-staff-role heading-font">
                                                <?php
                                                echo esc_attr( $role_name );
                                                ?></div>
                                            <table>
                                                <?php if( get_post_meta( $id, 'staff_age' ) != null ): ?>
                                                    <tr>
                                                        <td><?php esc_html_e( 'Age:', 'splash' ); ?></td>
                                                        <td><?php $age = get_post_meta( $id, 'staff_age', true );
                                                            if( !empty( $age ) ) {
                                                                echo esc_html( $age );
                                                            } ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                <?php if( get_post_meta( $id, 'staff_college' ) != null ): ?>
                                                    <tr>
                                                        <td><?php esc_html_e( 'College:', 'splash' ); ?></td>
                                                        <td><?php $collage = get_post_meta( $id, 'staff_college', true );
                                                            echo esc_html( $collage ); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                <?php if( get_post_meta( $id, 'staff_experience' ) != null ): ?>
                                                    <tr>
                                                        <td><?php esc_html_e( 'Experience:', 'splash' ); ?></td>
                                                        <td><?php $experience = get_post_meta( $id, 'staff_experience', true );
                                                            echo esc_html( $experience ); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <td><?php esc_html_e( 'Nationality:', 'splash' ); ?></td>
                                                    <td>
                                                        <?php if( !empty( $country_name ) ): ?>
                                                            <div class="nationality">
                                                                <?php echo '<img src="' . plugin_dir_url( SP_PLUGIN_FILE ) . 'assets/images/flags/' . strtolower( $nationality ) . '.png" alt="' . $nationality . '">' . esc_attr( $country_name ); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><?php esc_html_e( 'Past Teams:', 'splash' ); ?></td>
                                                    <td><?php echo implode( ", ", $pastTeamsList ); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                endif;
            endforeach; ?>
        </div>
    </div>
<?php endif; ?>