<?php
if (isset($_POST['cwebco_updatekey'])) {
    if (wp_verify_nonce($_POST['cwebcoupkeper_key_cstm_field'], 'cwebcoupkeper_key_cstm')) {
        UPKPR_upkepr_regenerate_key();
    }
}

$_upkepr_maintainance_validationkey = get_option('upkeprvalidationkeycstm', true);
$upkepr_admin_page = admin_url("admin.php?page=vulnerability-detector#upkr-formsection");

$tabSection = isset($_GET['section']) ? $_GET['section'] : 'apikey';
$subsection = isset($_GET['sub-section']) ? $_GET['sub-section'] : 'all';
//delete_option('upkpr_vulnerability_all');
$isConnected = get_option('upkpr_vulnerability_all');
?>

<div id="upkepr-loader"></div>
<div class="wrap" id="community-profile-page">
    <!--  <h1 class="wp-heading-inline">Upkepr Maintenance</h1> -->

    <hr class="wp-header-end">
</div>
<div class="upkepr-div-tabmain-section">
    <header>
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-md-6">
                    <a target="_blank" href="https://upkepr.com/"><img src="<?php echo esc_html(UPKPR_UPKEPR_WS_PATH1); ?>images/logo.png" /></a>
                </div>
                <div class="col-md-4 text-end">
                    <a href="https://upkepr.com/" target="_blank"><i class="fa fa-external-link by-webgarh" aria-hidden="true"></i> https://upkepr.com/</a>
                </div>
            </div>
        </div>
    </header>
    <div class="upker-summry-outer">
        <div class="sticky-links">
            <div class="upkepr-side-links scan-summry-left">
                <div class="scan-summry-header">
                    <img class="site-logo" src="<?php echo esc_html(UPKPR_UPKEPR_WS_PATH1); ?>images/health-report-icon.png" alt="site-logo 2">
                    <h2 class="how-upkepr-protect-wordpress-websites outfit-bold-white-32px">Scan summary</h2>
                </div>
                <ul class="vulnerabiliti-side-link">
                    <?php if (!empty($isConnected)) : ?>
                        <li class="nav-link active"><a href="javascript:void(0)" onclick="scrollToSection('.health-report-banner-header',this)" data-class="health-report-banner-header"><i
                                    class="fa-solid fa-angles-right "></i> Vulnerabilities Summary</a>
                        </li>
                        <li class="nav-link">
                            <a href="javascript:void(0)"
                                onclick="scrollToSection('.connection-page-table',this)" data-class="connection-page-table"><i class="fa-solid fa-angles-right "></i>
                                Detailed Vulnerabilities</a>
                        </li>
                        <li class="nav-link">
                            <a href="javascript:void(0)" onclick="scrollToSection('.website-health-report',this)" data-class="website-health-report"><i
                                    class="fa-solid fa-angles-right "></i> Website health report</a>
                        </li>
                        <li class="nav-link">
                            <ul>
                                <li class="nav-link">
                                    <a href="javascript:void(0)" onclick="scrollToSectionSub('.scan-summry-main', '#scans-summry-tab1',this)" data-class="scan-summry-main-perfromance">
                                        Performance/Speed detail</a>
                                </li>
                                <li class="nav-link"><a href="javascript:void(0)" onclick="scrollToSectionSub('.scan-summry-main', '#scans-summry-tab2',this)" data-class="scan-summry-main-seo"> SEO</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-link  <?php if (empty($isConnected)) {
                                                echo "active";
                                            } ?>"><a href="javascript:void(0)"
                            onclick="scrollToSection('.wordpress-pulgin-welcome',this)" data-class="wordpress-pulgin-welcome"><i
                                class="fa-solid fa-angles-right "></i> Configuration with UpKepr</a>
                    </li>

                    <li class="nav-link"><a href="javascript:void(0)" onclick="scrollToSection('.plan-and-connection',this)" data-class="plan-and-connection"><i
                                class="fa-solid fa-angles-right "></i> UpKepr Plan & Connection Status</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="right-side-summry">

            <?php if (!empty($isConnected)) : ?>

            <?php endif; ?>

            <?php if (isset($isConnected) && !empty($isConnected)) :
                $subsection = 'all';
                $pluginVulnebility = get_option('upkpr_vulnerability_all');
            ?>

                <!--<a href="javascript:void(0)" class="usButton upkpr_scannow_refresh" onclick="UPKPR_check_connection('plugin',this)">Refresh Data </a> -->

                <div class="vulnerability_section">
                    <?= UPKPRRenderVulnerabiliy(strtolower('all'), $pluginVulnebility) ?>
                </div>
            <?php endif; ?>
            <? //php if ($tabSection == 'apikey') : 
            ?>
            <!-- model on page -->
            <div id="upkpr-Modal" class="section <?php if (empty($isConnected)) { ?> wordpress-pulgin-welcome <?php } ?> upkpr-modal connect-popup ">
                <div class="container">
                <div class="upkpr-modal-content signup-more-details-popup cstm-card">
                    <!-- <span class="upkpr-close">&times;</span> -->
                    <div class="header">
                        <h2> Configuration with <span class="highlight"> UpKepr! </span></h2>
                    </div>
                    <p class="upkpr-errors errors error" style=" text-align: center; font-size: 15px;display: none;"></p>
                    <p class="upkpr-success " style=" text-align: center; font-size: 15px; color: #6f746f;display: none;"></p>
                    <div class="stepper-wrapper">
                        <div class="stepper-item step1">
                            <div class="step-counter">1</div>
                            <!-- <div class="step-name">Add website to UpKepr</div> -->
                        </div>
                        <div class="stepper-item step2">
                            <div class="step-counter">2</div>
                            <!-- <div class="step-name">Configure key on upkepr</div> -->
                        </div>
                        <!--div class="stepper-item step3">
                            <div class="step-counter">3</div>
                            <div class="step-name">Scan to fetch website details</div>
                        </div-->
                    </div>
                    <!-- <div id="upkepr-loader-2"></div> -->
                    <div class="scan-now"><a href="javascript:void(0)" class="primary-btn scan-now-btn" onclick="UPKPR_check_connection('all',this)"><i class="fa-solid fa-expand"></i> Scan Now</a></div>
                    <div class="coonection-with-upkepr-outer">
                        <div class="coonection-with-upkepr-left">
                            <h2 class="step-for-link" style="display:none"> Step 1 - Add website to <span class="highlight">UpKepr </span></h2>
                            <h2 class="step2-key" style="display:none"> Step 2 - Configure key on <span class="highlight">UpKepr </span></h2>
                            <p class="model-body pop-heading-new"></p>
                            <div class="key-configration-input" >
                                <input type="text" id="upkepr_maintainance_validationkey" value="<?php if (isset($_upkepr_maintainance_validationkey)) {
                                                                                                        echo esc_html($_upkepr_maintainance_validationkey);
                                                                                                    } ?>" readonly>
                                <i alt="upkepr" onclick="UPKPR_copykey()" class="fa-solid fa-copy"></i>
                            </div>
                            <div class="step-for-link steps-descriptions" style="display:none">
                                <p>👉 Please create an <a href="https://app.upkepr.com/register" target="_blank">account</a> or <a href="https://app.upkepr.com/" target="_blank">log in</a> to your existing account to add your website and configure your key.</p>
                                
                                <p>👉 <strong>Add Your Website:</strong> Log in to UpKepr and add your WordPress website.</p>
                                <p>👉 <strong>Configure Connection:</strong> </p>
                                <ul>
                                    <li><p>Go to the WordPress CMS section in UpKepr.</p></li>
                                    <li><p>Enter the key name and your WordPress admin username.</p></li>
                                </ul>
                                <p>👉 <strong>Connect:</strong> This will connect your UpKepr account with your WordPress site.</p>
                                
                                <p>👉 Complete this step to unlock all the features!</p>
                            </div>
                            <div class="step2-key steps-descriptions" style="display:none">
                                <p>👉 Copy the key and  <a href="https://app.upkepr.com/" target="_blank">log in</a> or <a href="https://app.upkepr.com/register" target="_blank">sign up</a> on UpKepr to configure the key and Complete this step to unlock all the features</p>
                                <p>📽️ Watch this video for a step-by-step guide on how to configure your key correctly. <a href="https://www.youtube.com/watch?v=TnNxQXtAreg" target="_blank">Click Here To Watch Video</a>.</p>
                            </div>
                            <div class="addButton">
                                <a href="https://app.upkepr.com/register" class="usButtonref primary-btn registerButton" target="_blank"><i class="fa-solid fa-lock"></i> Login/Signup To UpKepr </a>
                                <?php if ($tabSection == 'apikey') : ?>
                                    <a href="#popup1" class="step2-key primary-btn usButton" class="usButton">Regenerate</a>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                        <div class="coonection-with-upkepr-right">
                        <p class="step-for-link">📽️ Need assistance? Watch this  <a href="https://www.youtube.com/watch?v=TnNxQXtAreg" target="_blank">video</a> for a simple, step-by-step guide on how to connect with UpKepr.</p>
                            <p class="step2-key">📽️ Need assistance? Watch this  <a href="https://www.youtube.com/watch?v=TnNxQXtAreg" target="_blank">video</a> for a simple, step-by-step guide on how to configration key with UpKepr.</p>
                            <div class="coonection-with-upkepr-right-video">
                                <iframe  src="https://www.youtube.com/embed/TnNxQXtAreg?si=QoAvlKvaw89oFKig&controls=0&autoplay=1&mute=1&rel=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
                <form action="<?php echo esc_html($upkepr_admin_page); ?>" method="post">
                    <div class="" style="display: none;">
                        <?php if ($tabSection == 'apikey') : ?>

                            <div class="key-configration-input-copy">
                                <div class="key-configration-input">

                                    <input type="text" id="upkepr_maintainance_validationkey" value="<?php if (isset($_upkepr_maintainance_validationkey)) {
                                                                                                            echo esc_html($_upkepr_maintainance_validationkey);
                                                                                                        } ?>" readonly>
                                    <i alt="upkepr" onclick="UPKPR_copykey()" class="fa-solid fa-copy"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="health-report-banner-header" id="upkrre-Generate-kay">
                            <div class="health-report-header-right">
                                <?php if (empty($isConnected)) : ?>
                                    <a href="javascript:void(0);" style="display: none;" onclick="UPKPR_check_user('check',this)" class="primary-btn click-to-complete"> Click to complete setup </a>
                                <?php else : ?>
                                    <!--  <a href="javascript:void(0);" onclick="UPKPR_check_connection('all',this)" class="primary-btn scan-now"> Scan Now </a> -->
                                <?php endif; ?>
                                <?php if ($tabSection == 'apikey') : ?>
                                    <a href="#popup1" class="step2-key primary-btn usButton" class="usButton">Regenerate</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div id="popup1" class="overlay">
                        <div class="popup signup-more-details-popup signup-more-details-popup-key">
                            <a class="close" href="#upkr-formsection">&times;</a>
                            <div class="signUpPopupPage">
                                <h2>IMPORTANT <span class="highlight">ALERT</span></h2>
                                <p> Regenerating the key will render old key as invalid. If you have already used the old key in UpKepr, you
                                    have to update new key in UpKepr.</p>
                                <?php wp_nonce_field("cwebcoupkeper_key_cstm", "cwebcoupkeper_key_cstm_field"); ?>
                                <input type="submit" name="cwebco_updatekey" class="usButton primary-btn" value="Yes, I am aware Update the Key">
                            </div>
                        </div>

                    </div>

                </form>
                </div>
            </div>
            <?php if (!empty($isConnected)) { ?>
            <section class="section wordpress-pulgin-welcome" <?php if (empty($isConnected)) { ?> style="margin-top: 0px;" <?php } ?> id="upkr-formsection">
                <div class="container">
                    <div class="cstm-card pulgin-welcome-banner">
                        <div class="pulgin-welcome-banner-text">
                            <h2 class="plugin-heading">Configure Key with <span class="highlight">UpKepr!</span></h2>
                            <p>
                                UpKepr is a suite of security-focused tools designed by WordPress experts to safeguard your WordPress site while enhancing performance and growth.
                                <br>
                            </p>

                            <form action="<?php echo esc_html($upkepr_admin_page); ?>" method="post">
                                <div class="">
                                    <?php if ($tabSection == 'apikey') : ?>

                                        <div class="key-configration-input-copy">
                                            <div class="key-configration-input">

                                                <input type="text" id="upkepr_maintainance_validationkey" value="<?php if (isset($_upkepr_maintainance_validationkey)) {
                                                                                                                        echo esc_html($_upkepr_maintainance_validationkey);
                                                                                                                    } ?>" readonly>
                                                <i alt="upkepr" onclick="UPKPR_copykey()" class="fa-solid fa-copy"></i>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="health-report-banner-header" id="upkrre-Generate-kay">
                                        <div class="health-report-header-right">
                                            <?php if (empty($isConnected)) : ?>
                                                <a href="javascript:void(0);" style="display: none;" onclick="UPKPR_check_user('check',this)" class="primary-btn click-to-complete"> Click to complete setup </a>
                                            <?php else : ?>
                                                <!--  <a href="javascript:void(0);" onclick="UPKPR_check_connection('all',this)" class="primary-btn scan-now"> Scan Now </a> -->
                                            <?php endif; ?>
                                            <?php if ($tabSection == 'apikey') : ?>
                                                <a href="#popup12" class="primary-btn usButton" class="usButton">Regenerate</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div id="popup12" class="overlay">
                                    <div class="popup signup-more-details-popup signup-more-details-popup-key">
                                        <a class="close" href="#upkr-formsection">&times;</a>
                                        <div class="signUpPopupPage">
                                            <h2>IMPORTANT <span class="highlight">ALERT</span></h2>
                                            <p> Regenerating the key will render old key as invalid. If you have already used the old key in UpKepr, you
                                                have to update new key in UpKepr.</p>
                                            <?php wp_nonce_field("cwebcoupkeper_key_cstm", "cwebcoupkeper_key_cstm_field"); ?>
                                            <input type="submit" name="cwebco_updatekey" class="usButton primary-btn" value="Yes, I am aware Update the Key">
                                        </div>
                                    </div>

                                </div>

                            </form>
                        </div>

                        <div class="pulgin-welcome-banner-text">
                            <!-- <h2 class="plugin-heading">How to Configure <span class="highlight">UpKepr</span></h2> -->
                            <!-- <ul class="how-configure-list" >
                                <li><strong>Add Your Website:</strong> Log in to UpKepr and add your WordPress website.</li>
                                <li><strong>Configure Connection:</strong>
                                    <ul class="how-configure-inner-list">
                                        <li>Go to the WordPress CMS section in UpKepr.</li>
                                        <li>Enter the key name and your WordPress admin username.</li>
                                    </ul>
                                </li>
                                <li><strong>Connect:</strong> This will connect your UpKepr account with your WordPress site.</li>
                                <li><strong>Scan for Updates:</strong> Press the scan button in UpKepr inside the wordpress to fetch the latest information from your site.</li>
                            </ul> -->
                            
                                <div class="coonection-with-upkepr-right-video">
                                    <iframe  src="https://www.youtube.com/embed/TnNxQXtAreg?si=QoAvlKvaw89oFKig&controls=0&autoplay=1&mute=1&rel=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                                </div>
                            
                        </div>
                    </div>
                </div>
            </section>
            <?php } else { ?>
                <a href="javascript:void(0);" style="display: none;" onclick="UPKPR_check_user('check',this)" class="primary-btn click-to-complete"> Click to complete setup </a>
            <?php } ?>
            <? //php endif; 
            ?>

            <?php if ($tabSection == 'apikey') :
                // Key details
            ?>

            <?php elseif ($tabSection == 'vulnerabilities') : ?>
                <p class="upkpr-success " style=" text-align: center; font-size: 15px; color: #6f746f;display: none;"></p>
                <p class="upkpr-errors errors error" style=" text-align: center; font-size: 15px;display: none;"></p>
                <?php
                if ($subsection == 'core') :
                    $coreVulnebility = get_option('upkpr_vulnerability_' . strtolower($subsection));
                ?>
                    <div class="vulnerability_section">
                        <?= UPKPRRenderVulnerabiliy(strtolower('core'), $coreVulnebility) ?>
                    </div>

                <?php elseif ($subsection == 'theme') :
                    $themeVulnebility = get_option('upkpr_vulnerability_' . strtolower($subsection));

                ?>

                    <!--  <a href="javascript:void(0)" class="usButton upkpr_scannow_refresh" onclick="UPKPR_check_connection('theme',this)"> Refresh Data </a> -->
                    <div class="vulnerability_section">
                        <?= UPKPRRenderVulnerabiliy(strtolower('theme'), $themeVulnebility) ?>
                    </div>

                <?php elseif ($subsection == 'plugin') :
                    $pluginVulnebility = get_option('upkpr_vulnerability_' . strtolower($subsection));
                ?>


                    <!--<a href="javascript:void(0)" class="usButton upkpr_scannow_refresh" onclick="UPKPR_check_connection('plugin',this)">Refresh Data </a> -->
                    <div class="vulnerability_section">
                        <?= UPKPRRenderVulnerabiliy(strtolower('plugin'), $pluginVulnebility) ?>
                    </div>

                <?php elseif ($subsection == 'all') :
                    $pluginVulnebility = get_option('upkpr_vulnerability_' . strtolower($subsection));
                ?>


                    <!--<a href="javascript:void(0)" class="usButton upkpr_scannow_refresh" onclick="UPKPR_check_connection('plugin',this)">Refresh Data </a> -->
                    <div class="vulnerability_section">
                        <?= UPKPRRenderVulnerabiliy(strtolower('all'), $pluginVulnebility) ?>
                    </div>
                <?php else : ?>
                    <p>Status 404, Page not found.</p>
                <?php endif; ?>

            <?php endif; ?>

            <section class="section plan-and-connection">
                <div class="container">
                    <div class="row justify-content-between">
                        <div class="col-md-6">
                            <div class="plan-connetion-inner plans-inner">
                                <?php $plandetails = !empty($isConnected) ? json_decode($isConnected) : '' ?>
                                <h2 class="plugin-heading">Your plans</span></h2>

                                <?php if (isset($plandetails->plan) && $plandetails->plan != 'upgraded') : ?>
                                    <p>Currently you are on FREE Plan, Power up your plan for more features</p>
                                <?php elseif (isset($plandetails->plan) && $plandetails->plan == 'upgraded') : ?>
                                    <p>Currently you are on <?= $plandetails->plan_name ?> paid plan.</p>
                                <?php else : ?>
                                    <p>Want to power up your UpKepr?</p>
                                <?php endif; ?>

                                <div class="b-tns">
                                    <a href="https://app.upkepr.com/checkpayment" target="_blank" class="bg-btn">View Plan</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="plan-connetion-inner Connection-inner">
                                <div class="site-connected-heading">
                                    <h2 class="plugin-heading">Connection status</h2>
                                    <div class="site-connected">
                                    </div>
                                </div>
                                <p>Here is the quick overview of Wordpress Website with UpKepr.</p>
                                <div class="b-tns">
                                    <!--  <a href="#" class="bg-btn">Learn More</a> -->
                                    <?php if (empty($isConnected)) : ?>
                                        <a href="#upkpr-Modal" class="upkprConnect brdr-btn">Connect</a>
                                        <!-- <a href="javascript:void(0);" onclick="UPKPR_check_user('check',this)" class="upkprConnect brdr-btn">Connect</a> -->
                                    <?php endif; ?>
                                </div>
                                <div class="connect-list-outer upkepr-keyRemainStatus">
                                    <?php if (!empty($isConnected)) :
                                        $responseData = json_decode($isConnected);
                                    ?>
                                        <div class="list">
                                            <span class="connect-list-icon check active"><i class="fa-solid fa-check"></i></span>
                                            <p>Site is added on upkepr</p>
                                        </div>
                                        <div class="list">
                                            <span class="connect-list-icon check active"><i class="fa-solid fa-check"></i></span>
                                            <p>Key is connected </p>
                                        </div>
                                        <div class="list">
                                            <span class="connect-list-icon check active"><i class="fa-solid fa-check"></i></span>
                                            <p>Last scan on <?= date('d F, Y H:i:s', strtotime($responseData->created_at)); ?></p>
                                        </div>
                                    <?php else: ?>
                                        <span class="upkprLoadListToCheckConnected" style="display: none;">

                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="connect-list-outer upkepr-keyStatus" style="display: none;">
                                    <span class="upkprLoadListToCheckConnected">

                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <footer>
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-md-8">
                    <ul>
                        <li><a href="https://upkepr.com/" target="_blank" title="UpKepr"><img src="<?php echo esc_html(UPKPR_UPKEPR_WS_PATH1); ?>images/logo.png" /></a></li>
                        <li><a href="https://upkepr.com/about/" target="_blank">About</a></li>
                        <li><a href="https://upkepr.com/terms-and-conditions/" target="_blank">Privacy Policy & Terms</a></li>

                    </ul>
                </div>
                <div class="col-md-4 text-end">
                    <a href="https://upkepr.com/" target="_blank" class="by-webgarh">By UpKepr</a>
                </div>
            </div>
        </div>
    </footer>


</div>
<!-- <div id="upkpr-Modal-one" class="upkpr-modal connect-popup">
    <div class="upkpr-modal-content signup-more-details-popup">
        <span class="upkpr-close">&times;</span>
        
        <p class="upkpr-errors errors error" style=" text-align: center; font-size: 15px;display: none;"></p>
        <p class="upkpr-success " style=" text-align: center; font-size: 15px; color: #6f746f;display: none;"></p>
        <div class="stepper-wrapper">
            <div class="stepper-item step1">
                <div class="step-counter">1</div>
            </div>
            <div class="stepper-item step2">
                <div class="step-counter">2</div>
            </div>
        </div>
        <div id="upkepr-loader-2"></div>
        <div class="coonection-with-upkepr-outer">
            <div class="coonection-with-upkepr-left">
                <h2 class="step-for-link" style="display:none"> Step 1 - Add website to <span class="highlight">UpKepr </span></h2>
                <h2 class="step2-key" style="display:none"> Step 2 - Configure key on <span class="highlight">UpKepr </span></h2>
                <p class="model-body pop-heading-new"></p>
                <div class="key-configration-input step2-key" style="display: none;">
                    <input type="text" id="upkepr_maintainance_validationkey" value="<?php if (isset($_upkepr_maintainance_validationkey)) {
                                                                                            echo esc_html($_upkepr_maintainance_validationkey);
                                                                                        } ?>" readonly>
                    <i alt="upkepr" onclick="UPKPR_copykey()" class="fa-solid fa-copy"></i>
                </div>
                <div class="step-for-link steps-descriptions" style="display:none">
                    <p>👉 Please create an <a href="https://app.upkepr.com/register" target="_blank">account</a> or <a href="https://app.upkepr.com/" target="_blank">log in</a> to your existing account to add your website and configure your key.</p>
                    <p>📽️ Need assistance? Watch this  <a href="https://www.youtube.com/watch?v=TnNxQXtAreg" target="_blank">video</a> for a simple, step-by-step guide on how to connect with UpKepr.</p>
                    <p>👉 Complete this step to unlock all the features!</p>
                </div>
                <div class="step2-key steps-descriptions" style="display:none">
                    <p>👉 Copy the key and  <a href="https://app.upkepr.com/" target="_blank">log in</a> or <a href="https://app.upkepr.com/register" target="_blank">sign up</a> on UpKepr to configure the key and Complete this step to unlock all the features</p>
                    <p>📽️ Watch this video for a step-by-step guide on how to configure your key correctly. <a href="https://www.youtube.com/watch?v=TnNxQXtAreg" target="_blank">Click Here To Watch Video</a>.</p>
                </div>
                <div class="addButton">
                    <a href="https://app.upkepr.com/register" class="usButton primary-btn registerButton" target="_blank"><i class="fa-solid fa-lock"></i> Login/Signup To UpKepr </a>
                </div>
            </div>
            <div class="coonection-with-upkepr-right">
                <div class="coonection-with-upkepr-right-video">
                    <iframe  src="https://www.youtube.com/embed/TnNxQXtAreg?si=QoAvlKvaw89oFKig&controls=0&autoplay=1&mute=1&rel=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

</div> -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var accToggles = document.querySelectorAll('.upkpr-accordion-togglere');
        accToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                this.classList.toggle('active');
                var content = this.closest('tr').nextElementSibling;
                if (content.style.display === "none") {
                    content.style.display = "";
                } else {
                    content.style.display = "none";
                }
            });
        });
    });
</script>