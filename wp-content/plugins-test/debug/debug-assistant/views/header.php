<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}

$tabs = array(
                array(
                        'label'     => 'Dashboard',
                        'slug'      => 'dashboard',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'      => 'nav-icon fa-imtda fa-imtda-superpowers',
                        'nav-title' => false

                ),
                array(
                        'label'     => 'Debug',
                        'slug'      => 'debug',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'     => 'nav-icon fa-imtda fa-imtda-bug',
                        'nav-title' => false
                ),
                array(
                        'label'     => 'Error Logs',
                        'slug'      => 'error_logs',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'     => 'nav-icon fa-imtda fa-imtda-exclamation-circle',
                        'nav-title' => false
                ),
                array(
                        'label'     => 'Raw Editor wp-config File',
                        'slug'      => 'raw_editor',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'     => 'nav-icon fa-imtda fa-imtda-code',
                        'nav-title' => false
                ),
                array(
                        'label'     => 'Database',
                        'slug'      => 'database',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'     => 'nav-icon fa-imtda fa-imtda-database',
                        'nav-title' => false
                ),

                array(
                        'label'     => 'Environment',
                        'slug'      => 'environment',
                        'clickable' => false,
                        'childs'    => array(
                                array(
                                  'label'     => 'System Report',
                                  'slug'      => 'system_report_env',
                                  'clickable' => true,
                                  'icon'      => 'nav-icon icon-screen-desktop icons'
                                ),
                                array(
                                  'label'     => 'PHP Info Environment',
                                  'slug'      => 'php_info_env',
                                  'clickable' => true,
                                  'icon'      => 'nav-icon icon-info icons'
                                ),
                        ),
                        'icon'      => 'nav-icon icon-info icons',
                        'nav-title' => false
                ),

                array(
                        'label'     => 'Crons',
                        'slug'      => 'crons',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'     => 'nav-icon icon-clock icons',
                        'nav-title' => false
                ),

                array(
                        'label'     => 'Hooks & Actions',
                        'slug'      => 'hooks_and_actions',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'     => 'nav-icon icon-speedometer',
                        'nav-title' => 'users'
                ),

                array(
                        'label'     => 'Temporary Admin',
                        'slug'      => 'temporary_admin',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'     => 'nav-icon icon-user icons',
                        'nav-title' => false
                ),
                array(
                        'label'     => 'Logged Users',
                        'slug'      => 'logged_users',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'     => 'nav-icon icon-login icons',
                        'nav-title' => 'Extras'
                ),
                array(
                        'label'     => 'Speed test',
                        'slug'      => 'speed_test',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'     => 'nav-icon icons cui-speedometer',
                        'nav-title' => false
                ),
                array(
                        'label'     => 'Advanced Settings',
                        'slug'      => 'advanced_settings',
                        'clickable' => true,
                        'childs'    => false,
                        'icon'     => 'nav-icon icons cui-settings',
                        'nav-title' => false
                ),

);

$breadcrumbs = [];

$currentTab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'dashboard';
foreach ( $tabs as $tabArray ){
    /// NO PARENTS

    if ( $tabArray['clickable'] ){
        if ( $tabArray['slug'] == $currentTab ){
            $breadcrumbs[] = array( 'label' => $tabArray['label'], 'class' => 'active', 'slug' => $tabArray['slug'] );
            break;
        }
    } else {
        /// with parents
        foreach ( $tabArray['childs'] as $subtabArray ){
          if ( $subtabArray['slug'] == $currentTab ){

              $breadcrumbs[] = array( 'label' => $subtabArray['label'], 'class' => 'active', 'slug' => $subtabArray['slug']);
              break;
          }
        }
    }

}

 ?>

<div class="imlt-full sidebar-lg-show">
<div class="header-fixed app-header aside-menu-fixed pace-done">
 <header class="app-header navbar">
   <button class="navbar-toggler sidebar-toggler d-lg-none mr-auto" type="button" data-toggle="sidebar-show">
     <span class="navbar-toggler-icon"></span>
   </button>
   <a class="navbar-brand" href="#">
     <!--<img class="navbar-brand-full" src="img/brand/logo.svg" width="89" height="25" alt="Ziggy Logo">
     <img class="navbar-brand-minimized" src="img/brand/sygnet.svg" width="30" height="30" alt="Ziggy Logo">-->
   </a>
   <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button">
     <span class="navbar-toggler-icon"></span>
   </button>

   <ul class="nav navbar-nav d-md-down-none">
     <li class="nav-item px-3">
       <a class="nav-link" href="https://www.wpindeed.com" target="_blank">Purchase</a>
     </li>
     <li class="nav-item px-3">
       <a class="nav-link" href="https://support.wpindeed.com" target="_blank">Support</a>
     </li>
     <li class="nav-item px-3">
       <a class="nav-link" href="https://help.wpindeed.com/" target="_blank">Knowledge Base</a>
     </li>
   </ul>
   <ul class="nav navbar-nav ml-auto">
     <li class="nav-item dropdown d-md-down-none">
       <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
           <!--<i class="icon-bell"></i>-->
          <!-- <span class="badge badge-pill badge-danger"></span>-->
           <span class="fa-imtda fa-imtda-list-alt fa-lg"></span>
         </a>
       <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg">
         <div class="dropdown-header text-center">
           <strong>Summary report</strong>
         </div>
         <a class="dropdown-item" href="<?php echo admin_url('admin.php?page=imlt_manage&tab=temporary_admin'); ?>">
             <i class="icon-user-follow text-success"></i><div class="badge badge-pill badge-danger"><?php echo imlt_count_admin_users(); ?></div> New temporary admin users</a>
         <a class="dropdown-item" href="<?php echo admin_url('users.php'); ?>">
             <i class="fa-imtda fa-imtda-user-o text-info "></i><div class="badge badge-pill badge-danger"><?php echo imlt_all_wordpress_users(); ?></div> All users</a>

        <?php $loggedUsers = get_option( 'imlt_track_user' );?>


        <?php if ( isset($_POST['imlt_track_user']) ) $loggedUsers = $_POST['imlt_track_user'];?>
        <?php if ( $loggedUsers ):?>

          <a class='dropdown-item' href="<?php echo admin_url('admin.php?page=imlt_manage&tab=logged_users'); ?>">
            <i class='fa-imtda fa-imtda-circle text-success'></i><div class='badge badge-pill badge-danger'>
               <?php echo imlt_all_users_online();?>
             </div>Connected users</a>
         <?php else : ?>
           <a class="dropdown-item" href="<?php echo admin_url('admin.php?page=imlt_manage&tab=logged_users'); ?>">
             <div id="imlt_notice"><i class='fa-imtda fa-imtda-circle text-danger'></i>Enable connected users from Logged Users</div>
         <?php endif;?>

         <a class="dropdown-item" href="<?php echo admin_url('user-new.php'); ?>">
             <i class="icon-user-follow text-success"></i> Add new user</a>
         <a class="dropdown-item" href="<?php echo admin_url('post-new.php'); ?>">
             <i class="icon-speedometer text-warning"></i> Add new post</a>
         <!--<div class="dropdown-header text-center">
           <strong>Server</strong>
         </div>
         <a class="dropdown-item" href="<?php //echo admin_url('admin.php?page=imlt_manage&tab=system_report_env'); ?>">
           <div class="text-uppercase mb-1">
             <small>
               <b>System Environment</b>
             </small>
           </div>
           <span class="progress progress-xs">
             <div class="progress-bar bg-info" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
           </span>
           <small class="imlt-tx text-muted">348 Processes. 1/4 Cores.</small>
         </a>
         <a class="dropdown-item" href="<?php //echo admin_url('admin.php?page=imlt_manage&tab=php_info_env'); ?>">
           <div class="text-uppercase mb-1">
             <small>
               <b>PHP Info</b>
             </small>
           </div>
           <span class="progress progress-xs">
             <div class="progress-bar bg-warning" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
           </span>
           <small class="imlt-tx text-muted">11444GB/16384MB</small>
         </a>
         <a class="dropdown-item" href="#">
           <div class="text-uppercase mb-1">
             <small>
               <b>SSD 1 Usage</b>
             </small>
           </div>
           <span class="progress progress-xs">
             <div class="progress-bar bg-danger" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
           </span>
           <small class="imlt-tx text-muted">243GB/256GB</small>
         </a>
       </div>-->
     </li>

     <li class="nav-item dropdown">
       <div><?php echo get_avatar(get_current_user_id(), get_avatar_url(get_current_user_id()) ); ?></div>
      <!-- <a class="nav-link nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
         <?php
          //echo get_avatar(get_current_user_id(), get_avatar_url(get_current_user_id()) );
         ?>
       </a>
       <div class="dropdown-menu dropdown-menu-right">
         <div class="dropdown-header text-center">
           <strong>Settings</strong>
         </div>
         <a class="dropdown-item" href="#">
             <i class="fa fa-user"></i> Profile</a>

         <div class="dropdown-divider"></div>

         <a class="dropdown-item" href="#">
             <i class="fa fa-lock"></i> Logout</a>
       </div>-->
     </li>
   </ul>
 </header>
</div>
 <div class="app-body">
   <div class="imlt_fixed_nav">
   <div  id="icon_id" class="sidebar">
     <nav class="sidebar-nav">
         <ul class="nav">
           <?php foreach ( $tabs as $tabData ):?>
                  <?php $extraClass = ($tabData['childs']) ? 'nav-dropdown' : '';
                  $imlt_brd_class_active = ($tabData['slug'] == @$_GET['tab']) ? "imlt-active" : ""; ?>
                  <li class='nav-item <?php echo $extraClass; ?>'>
                          <?php if ( $tabData['clickable']):?>
                              <a class="nav-link <?php echo $imlt_brd_class_active; ?>" href="<?php echo admin_url('admin.php') . "?page=imlt_manage&tab=" . $tabData['slug'];?>"><?php echo "<i class='imt_icons " .$tabData['icon'] . "'></i>" . $tabData['label'];?></a>
                          <?php else :?>
                              <?php echo '<a  class="nav-link nav-dropdown-toggle" href="#"><i class="' .$tabData['icon'] . '"></i>'. $tabData['label'] .'</a>';?>
                          <?php endif;?>
                          <?php if ( $tabData['childs'] ):?>
                              <ul class="nav-dropdown-items">
                              <?php foreach ( $tabData['childs'] as $childData ):?>
                                <?php $imlt_brd_class_drop_active = ($childData['slug'] == @$_GET['tab']) ? "imlt-active" : ""; ?>
                                <li class="nav-item">
                                <?php if ( $childData['clickable']):?>

                                    <a  class="nav-link  <?php echo $imlt_brd_class_drop_active; ?>" href="<?php echo admin_url('admin.php') . "?page=imlt_manage&tab=" . $childData['slug'];?>"><?php echo "<i class='" .$childData['icon'] . "'></i>" . $childData['label'];?></a>
                                <?php else :?>
                                    <?php echo $childData['label'];?>
                                <?php endif;?>
                                </li>
                              <?php endforeach;?>
                              </ul>
                          <?php endif;?>
                      </li>
                        <?php if($tabData['nav-title']) : ?>
                        <li class="nav-title"><?php echo $tabData['nav-title']; ?></li>
                      <?php endif; ?>
                      <?php endforeach;?>

              <li class="nav-title">System Usage</li>
              <li class=" px-3 d-compact-none d-minimized-none">
               <div class="text-uppercase mb-1">
                 <small>
                   <b>Memory Limit</b>
                 </small>
               </div>
               <div class="progress progress-xs">
                 <div class="progress-bar bg-info" role="progressbar" style="width: 85%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
               </div>
               <small class="text-muted"><?php echo ini_get( 'memory_limit' ); ?></small>
             </li>

             <li class="px-3 d-compact-none d-minimized-none">
               <div class="text-uppercase mb-1">
                 <small>
                   <b>Post max size</b>
                 </small>
               </div>
               <div class="progress progress-xs">
                 <div class="progress-bar bg-warning" role="progressbar" style="width: 85%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
               </div>
               <small class="text-muted"><?php echo ini_get('post_max_size'); ?></small>
             </li>

             <li class="px-3 d-compact-none d-minimized-none">
               <div class="text-uppercase mb-1">
                 <small>
                   <b>Upload max filesize</b>
                 </small>
               </div>
               <div class="progress progress-xs">
                 <div class="progress-bar bg-danger" role="progressbar" style="width: 90%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
               </div>
               <small class="text-muted"><?php echo ini_get( 'upload_max_filesize' ); ?></small>
             </li>
         </ul>

     </nav>
     <button class="sidebar-minimizer brand-minimizer" type="button"></button>
   </div>
 </div>
   <main class="main">
     <!-- Breadcrumb-->
     <ol class="breadcrumb">
       <?php foreach ( $breadcrumbs as $breadcrumb ):?>
         <?php $parent_lbl = ($breadcrumb['slug'] == 'dashboard') ? "" : "Dashboard / "; ?>
         <?php $current_lbl = ($breadcrumb['slug'] !== 'dashboard') ?  $breadcrumb['label'] : 'Dashboard' ?>
          <li class="breadcrumb-item <?php echo $breadcrumb['class'];?>"><?php echo $parent_lbl . $current_lbl; ?></li>
       <?php endforeach ;?>
       <!-- Breadcrumb Menu-->
       <li class="breadcrumb-menu d-md-down-none">
         <div class="btn-group" role="group" aria-label="Button group">
           <a class="btn" href="./">
               <i class="icon-graph"></i> &nbsp; WordPress Dashboard</a>
           <a class="btn" href="#">
               <i class="icon-speech"></i> &nbsp;Updates</a>
           <!--<a class="btn" href="#">
               <i class="icon-settings"></i> &nbsp;Register Product</a>-->
         </div>
       </li>
     </ol>
     <div class="container-fluid">
       <div id="ui-view"></div>
