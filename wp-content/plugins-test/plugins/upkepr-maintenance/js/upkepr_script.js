/* Active Sidebar url on page scroll */
document.addEventListener('DOMContentLoaded', function() {
    //updateLinkActiveOnScroll();
    if(jQuery('.click-to-complete')){
        jQuery('.click-to-complete').trigger('click');
    }
    jQuery('#upkepr-loader').show();
    if (window.location.hash == '#popup1' || window.location.hash == '#popup12' ) {
        scrollToPopup(window.location.hash);
        // updateUrlHash('#popup1');
        updateUrlHash(window.location.hash);
    }
    // Add click event listener to the Regenerate button
    const regenerateButton = document.querySelector('a.primary-btn.usButton');
    if (regenerateButton) {
        regenerateButton.addEventListener('click', function (event) {
            event.preventDefault(); 
            const targetHash = regenerateButton.getAttribute('href');
            scrollToPopup(targetHash);
            // updateUrlHash('#popup1');
            updateUrlHash(targetHash);
            const baseUrl = window.location.href.split('#')[0];
            window.location.href = baseUrl + targetHash;
            // window.location.href = baseUrl + '#popup12';
        });
    }
});
function scrollToPopup(selector) {
    const targetElement = document.querySelector(selector);
    if (targetElement) {
        targetElement.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        });
    }
}
function updateUrlHash(hash) {
    const baseUrl = window.location.href.split('#')[0];
    history.replaceState(null, null, baseUrl + hash);
}
function UPKPR_copykey() {
    var copyText = document.getElementById("upkepr_maintainance_validationkey");
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */
    navigator.clipboard.writeText(copyText.value);
    alert('Key Copied');
}

function UPKPR_copyurl() {
    var copyText = document.getElementById("upkepr_maintainance_url");
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */
    navigator.clipboard.writeText(copyText.value);
    alert('Link Copied');
}

function UPKPR_check_connection(type, eventhis) {
    // jQuery(eventhis).attr('disabled', true);
    jQuery('#upkepr-loader').show();
    jQuery('#upkepr-loader-2').show();
    
    jQuery('.registerButton').addClass('disabled');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', upkpr_ajax_object.upkpr_ajax_url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onreadystatechange = function () {

        if (xhr.readyState === 4 && xhr.status === 200) {
            jQuery('#upkepr-loader').hide();
            respose = JSON.parse(xhr.responseText);

            if (respose.status == 1 && respose.type == 'get_details') {
                jQuery('.vulnerability_section').html(respose.html);
                triggerUPkprAccordian();
                // jQuery(eventhis).show();

                jQuery('.upkpr-success').html('Data retrieval successful. <i class="fa fa-check" aria-hidden="true"></i>');
                jQuery('.upkpr-success').show();

                jQuery('#upkpr-Modal').find('.step1').addClass('completed');
                jQuery('#upkpr-Modal').find('.step2').addClass('completed');
                jQuery('#upkpr-Modal').find('.step3').addClass('completed');

                // setTimeout(function () {
                    window.location.reload();
                    jQuery('.upkpr-success').html('');
                    jQuery('.upkpr-success').hide();
                // }, 1000);

            } else {
                jQuery('.registerButton').removeClass('disabled');
                // setTimeout(function () {
                    jQuery('.upkpr-errors').text('');
                    jQuery('.upkpr-errors').hide();
                    window.location.reload();
                // }, 5000);
                jQuery('.upkpr-errors').show();
                jQuery('.upkpr-errors').html(respose.message + '<i class="fa fa-times" aria-hidden="true"></i>');
                // jQuery(eventhis).show();
            }
        } else {
            jQuery('.registerButton').removeClass('disabled');
            jQuery('#upkepr-loader').hide();
            // jQuery(eventhis).show();
        }
        jQuery('#upkepr-loader-2').hide();
        jQuery('#upkepr-loader').hide();
    };
    xhr.send('action=upkpr_ajax_action&scan_type=' + type);
}

function UPKPR_check_user(type, eventhis) {

    // UPKPR_OpneModal('',type, eventhis);
    // jQuery(eventhis).attr('disabled', true);
    // jQuery('#upkepr-loader').show();
    //jQuery('#upkepr-loader-2').show();
    var xhr = new XMLHttpRequest();
    xhr.open('POST', upkpr_ajax_object.upkpr_ajax_url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onreadystatechange = function () {

        if (xhr.readyState === 4 && xhr.status === 200) {
            jQuery('#upkepr-loader').hide();
            respose = JSON.parse(xhr.responseText);

            if (respose.status == 1 && respose.type == 'get_details') {
                var text = "";
                jQuery('#upkpr-Modal .coonection-with-upkepr-outer').hide();
                jQuery('#upkpr-Modal .coonection-with-upkepr-left').hide();
                // var text = "Project Configured, Kindly scan now for fetch details";
                jQuery('#upkpr-Modal').find('.step1').addClass('completed');
                jQuery('#upkpr-Modal').find('.step2').addClass('completed');
                jQuery('#upkpr-Modal').find('.step1').removeClass('active');
                jQuery('#upkpr-Modal').find('.step2').removeClass('active');
                jQuery('#upkpr-Modal').find('.step2-key').hide();
                jQuery('#upkpr-Modal').find('.step-for-link').hide();
                //jQuery('#upkpr-Modal').find('.step3').addClass('active');
                jQuery('.coonection-with-upkepr-right').hide();
                jQuery('#upkpr-Modal .model-body').hide();
                // jQuery(eventhis).hide();
                UPKPR_OpneModal(text,type, eventhis);
                //'<a href="https://app.upkepr.com/register" class="usButton primary-btn registerButton" target="_blank"> Click Here </a>'
                jQuery('#upkpr-Modal .addButton').html(`<a href="javascript:void(0);" onclick="UPKPR_check_connection('all',this)" class="primary-btn scan-now registerButton"> Scan Now </a>`);
                /*setTimeout(function(){
                    //window.location.reload();
                },3000); */
                triggerUPkprAccordian();
                // jQuery(eventhis).show();
                // setTimeout(() => {
                    jQuery('#upkpr-Modal .addButton').find('.scan-now').trigger('click');
                    jQuery('#upkpr-Modal .addButton').hide();
                // }, 500);
                //jQuery('.upkpr-success').html('Data retrieval successful. <i class="dashicons dashicons-yes"></i>');                
                //jQuery('.upkpr-success').show();
                /* setTimeout(function () {
                    jQuery('.upkpr-success').html('');
                    jQuery('.upkpr-success').hide();
                }, 2000); */

            } else {
                jQuery('#upkpr-Modal .coonection-with-upkepr-outer').show();
                jQuery('#upkpr-Modal .coonection-with-upkepr-left').show();
                jQuery('#upkpr-Modal').find('.step-for-link').hide();
                if (respose.message == 'Website is not added.') {
                    jQuery('#upkpr-Modal').find('.step1').addClass('active');
                    jQuery('#upkpr-Modal').find('.step1').removeClass('completed');
                    jQuery('#upkpr-Modal').find('.step2').removeClass('active');
                    jQuery('#upkpr-Modal').find('.step-for-link').show();
                    jQuery('#upkpr-Modal').find('.step2-key').hide();
                    UPKPR_OpneModal('We noticed that this website has not been added to UpKepr yet.',type, eventhis);
                } else if (respose.message == 'No connecton found.') {
                    jQuery('#upkpr-Modal').find('.step1').removeClass('active');
                    jQuery('#upkpr-Modal').find('.step1').addClass('completed');
                    jQuery('#upkpr-Modal').find('.step2').addClass('active');
                    jQuery('#upkpr-Modal').find('.step2').removeClass('completed');
                    jQuery('#upkpr-Modal').find('.step2-key').show();
                    UPKPR_OpneModal('A website has been successfully added to UpKepr! To get started, please configure your key in UpKepr.',type, eventhis);
                } else if (respose.type == 'key_invalid') {
                    jQuery('#upkpr-Modal').find('.step1').addClass('completed');
                    jQuery('#upkpr-Modal').find('.step1').removeClass('active');
                    jQuery('#upkpr-Modal').find('.step2').addClass('active');
                    jQuery('#upkpr-Modal').find('.step2-key').show();
                    UPKPR_OpneModal('A website has been successfully added to UpKepr! To get started, please configure your key in UpKepr.',type, eventhis);
                } else if (respose.message) {
                    jQuery('#upkpr-Modal').find('.step1').addClass('active');
                    jQuery('#upkpr-Modal').find('.step2').removeClass('active');
                    jQuery('#upkpr-Modal').find('.step-for-link').show();
                    UPKPR_OpneModal(respose.message,type, eventhis);
                } else {
                    //UPKPR_OpneModal('Something went wrong.',type, eventhis);
                }
                // jQuery(eventhis).show();
                jQuery('#upkepr-loader').hide();
            }
        } else {
            // jQuery(eventhis).show();
            jQuery('#upkepr-loader').hide();
        }
        jQuery('#upkepr-loader').hide();
    };
    // xhr.send('action=upkpr_ajax_action&scan_type=' + type);
    xhr.send('action=upkpr_check_ajax_action&scan_type=' + type);
}

//upkprLoadListToCheckConnected
if (jQuery('.upkprLoadListToCheckConnected')) {
    //alert();
    UPKPR_check_ConnectedWithUpkpr('check');
    // setInterval(()=>{
        // UPKPR_check_ConnectedWithUpkpr('check');
    // },5000);
}
function UPKPR_check_ConnectedWithUpkpr(type = 'check') {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', upkpr_ajax_object.upkpr_ajax_url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            jQuery('#upkepr-loader').hide();
            respose = JSON.parse(xhr.responseText);

            if (respose.status == 1 && respose.type == 'get_details') {
                //var text = "Project Configured, Kindly scan now for fetch details";
                var text = "";
                jQuery('.coonection-with-upkepr-right').hide();
                jQuery('.coonection-with-upkepr-left').hide();
                jQuery('#upkpr-Modal .model-body').hide();
                jQuery('.upkprLoadListToCheckConnected').show();
                jQuery('.upkprLoadListToCheckConnected').html(`<div class="list siteAdded"><span class="connect-list-icon check active"><i class="fa-solid fa-check"></i></span><p>Site is added on UpKepr</p></div>
                <div class="list keyConnectedOrConnected"><span class="connect-list-icon check active"><i class="fa-solid fa-check"></i></span><p>Key configured properly</p></div>
                <div class="list scanPendingOrScanned"><span class="connect-list-icon info active"><i class="fa-solid fa-info"></i></span><p>Scanning pending</p></div>`);
                jQuery('.upkprConnect').hide();
                jQuery('.upkepr-keyStatus').hide();
                jQuery('.upkepr-keyRemainStatus').show();
            } else {
                jQuery('.upkepr-keyRemainStatus').hide();
                jQuery('.upkepr-keyStatus').show();
                if (respose.message == 'Website is not added.') {
                    jQuery('.upkprLoadListToCheckConnected').show();
                    jQuery('.upkprLoadListToCheckConnected').html(`<div class="list siteAdded"><span class="connect-list-icon errors active"><i class="fa-solid fa-xmark"></i></span><p>Site is not added on UpKepr</p></div>`);

                } else if (respose.message == 'No connecton found.') {
                    jQuery('.upkprLoadListToCheckConnected').show();
                    jQuery('.upkprLoadListToCheckConnected').html(`<div class="list siteAdded"><span class="connect-list-icon check active"><i class="fa-solid fa-check"></i></span><p>Site is added on UpKepr</p></div>
                    <div class="list keyConnectedOrConnected"><span class="connect-list-icon errors active"><i class="fa-solid fa-xmark"></i></span><p>Key is not configured on UpKper</p></div>`);
                } else if (respose.type == 'key_invalid') {
                    jQuery('.upkprLoadListToCheckConnected').show();
                    jQuery('.upkprLoadListToCheckConnected').html(`<div class="list siteAdded"><span class="connect-list-icon check active"><i class="fa-solid fa-check"></i></span><p>Site is added on UpKepr</p></div>
                    <div class="list keyConnectedOrConnected"><span class="connect-list-icon errors active"><i class="fa-solid fa-xmark"></i></span><p>Key mismatch issue</p></div>`);
                } else if (respose.message) {
                    jQuery('.upkprLoadListToCheckConnected').show();
                    jQuery('.upkprLoadListToCheckConnected').find('.list').hide();
                } else {
                    jQuery('.upkprLoadListToCheckConnected').show();
                    jQuery('.upkprLoadListToCheckConnected').find('.list').hide();
                }
            }
        } else {

        }
    };
    xhr.send('action=upkpr_check_ajax_action&scan_type=' + type);
}

function triggerUPkprAccordian() {
    var accToggles = document.querySelectorAll('.upkpr-accordion-toggle');
    accToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            this.classList.toggle('active');
            var content = this.closest('tr').nextElementSibling;
            if (content.style.display === "none") {
                content.style.display = "";
            } else {
                content.style.display = "none";
            }
        });
    });
}

function UPKPR_OpneModal(text, sectionType, eventThis) {
    jQuery('#upkpr-Modal .model-body').html(text);
    var modal = jQuery('#upkpr-Modal');
    modal.show();
    // startRefreshInterval(sectionType, eventThis);
}
jQuery(document).ready(function ($) {
    new DataTable('.upkepr-vulnerable-datatable');

    var modal = $('#upkpr-Modal');
    var span = $(".upkpr-close");
    $('.upkpr-ModalVieDetails').click(function () {
        modal.show();
    });
    span.click(function () {
        modal.hide();
        // clearInterval(refreshInterval);
    });

    $(window).click(function (event) {
        if (event.target == modal) {
            modal.hide();
            // clearInterval(refreshInterval);
        }
    });
});
jQuery('#upkepr-loader').hide();
jQuery('#upkepr-loader-2').hide();

let refreshInterval;
function startRefreshInterval(sectionType, eventThis) {
   /*  clearInterval(refreshInterval);
    refreshInterval = setInterval(function () {
        const modal = jQuery('#upkpr-Modal');
        if (modal.css('display') === 'block') {
            console.log('Modal is visible, refreshing...'); */
            UPKPR_check_user(sectionType, eventThis);
       /*  } else {
            clearInterval(refreshInterval);
        }
    }, 5000); */
}

function scrollToSection(selector,element) {
    const section = document.querySelector(selector);
    if (section) {

        section.style.scrollMarginTop = '120px';
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    document.querySelectorAll('.vulnerabiliti-side-link .nav-link').forEach((nav) => {
        nav.classList.remove('active');
    });
    element.closest('.nav-link').classList.add('active');
}
function scrollToSectionSub(selector,tabID, element) {
    const section = document.querySelector(selector);
    if (section) {
        section.style.scrollMarginTop = '120px';
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        jQuery(tabID).trigger('click');
    }
    document.querySelectorAll('.vulnerabiliti-side-link li').forEach((nav) => {
        nav.classList.remove('active');
    });
    element.closest('li').classList.add('active');
    jQuery('#healthReportsSection').removeClass('section scan-summry-main-perfromance scan-summry-main scan-summry-main-seo');
    jQuery('#healthReportsSection').addClass('section ' + jQuery(element).attr('data-class') + ' scan-summry-main');
}

function updateLinkActiveOnScroll(){
   
    const links = document.querySelectorAll(".vulnerabiliti-side-link a[data-class]");
    const sections = Array.from(links).map(link =>
        document.querySelector(`.${link.getAttribute("data-class")}`)
    );
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                if(entry.intersectionRatio >= 0.60 ){
                    
                    const visibleThreshold = entry.intersectionRatio;
                    const activeLink = Array.from(links).find(
                        link => link.getAttribute("data-class") === entry.target.classList[1]
                    );
                    document.querySelectorAll(".vulnerabiliti-side-link .nav-link.active")
                        .forEach(link => link.classList.remove("active"));
                        activeLink.closest(".nav-link").classList.add("active");
                } else if(entry.intersectionRatio >= 0.1 && (entry.target.classList[1] == "scan-summry-main-perfromance" || entry.target.classList[1] == "scan-summry-main-seo")){
                    const visibleThreshold = entry.intersectionRatio;
                    const activeLink = Array.from(links).find(
                        link => link.getAttribute("data-class") === entry.target.classList[1]
                    );
                    document.querySelectorAll(".vulnerabiliti-side-link .nav-link.active")
                        .forEach(link => link.classList.remove("active"));
                        activeLink.closest(".nav-link").classList.add("active");
                }
            }
        });
    }, { threshold: [0.60, 0.1] });
    sections.forEach(section => {
        if (section) observer.observe(section);
    });
}