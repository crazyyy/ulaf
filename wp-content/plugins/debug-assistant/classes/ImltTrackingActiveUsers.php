<?php
if ( class_exists('ImltTrackingActiveUsers') ) return;

class ImltTrackingActiveUsers
{

    private $metaKey = 'imlt_last_login';

    public function __construct()
    {
        $justSaved = (isset( $_POST['imlt_track_user'] ) && $_POST['imlt_track_user'] == 1) ? true : false;
        if ( !get_option('imlt_track_user') && !$justSaved ){
            return;
        }
        add_action( 'wp_login', [ $this, 'saveLastLogin' ], 10, 2);
        add_action( 'init', [ $this, 'saveOnInit' ], 10, 2);
        add_action( 'wp_logout', [ $this, 'deleteSessionOnLogout'] );
    }

    public function saveOnInit()
    {
        global $current_user;
        if ( empty($current_user) ){
            return;
        }
        update_user_meta( $current_user->ID, $this->metaKey, time() );
    }

    public function saveLastLogin( $userLogin='', $user=null )
    {
        if ( !$userLogin ){
            return;
        }
        require_once IMLT_DIR_PATH . 'classes/ImltDatabase.php';
        $db = new ImltDatabase();
        $uid = $db->getUidByUsername($userLogin);
        if ( !$uid ){
            return;
        }
        update_user_meta( $uid, $this->metaKey, time() );
    }

    public function deleteSessionOnLogout()
    {
        $user = wp_get_current_user();
        if ( empty($user->ID) ){
            return;
        }
        delete_user_meta( $user->ID, $this->metaKey );
    }

}
