/**
 * Single Assignment functions
 *
 * @author ThimPress
 * @package LearnPress/JS
 * @version 3.0.0
 */
;(function ($) {

    !Number.prototype.toTime && (Number.prototype.toTime = function () {

        var MINUTE_IN_SECONDS = 60,
            HOUR_IN_SECONDS = 3600,
            DAY_IN_SECONDS = 24 * 3600,
            seconds = this + 0,
            str = '';

        if (seconds > DAY_IN_SECONDS) {
            var days = Math.ceil(seconds / DAY_IN_SECONDS);
            str = days + (days > 1 ? ' day left' : ' days left');
        } else {
            var hours = Math.floor(seconds / HOUR_IN_SECONDS),
                minutes = 0;
            seconds = hours ? seconds % (hours * HOUR_IN_SECONDS) : seconds;
            minutes = Math.floor(seconds / MINUTE_IN_SECONDS);
            seconds = minutes ? seconds % (minutes * MINUTE_IN_SECONDS) : seconds;


            if (hours && hours < 10) {
                hours = '0' + hours;
            }

            if (minutes < 10) {
                minutes = '0' + minutes;
            }

            if (seconds < 10) {
                seconds = '0' + seconds;
            }

            str = hours + ':' + minutes + ':' + seconds;
        }

        return str;
    });

    function LP_Assignment(settings) {

        var self = this,
            thisSettings = $.extend({}, settings),
            remainingTime = thisSettings.remainingTime,
            timerCountdown = null,
            $timeElement = $('.assignment-countdown .progress-number'),
            callbackEvents = new LP.Event_Callback(this);

        function timeCountdown() {
            stopCountdown();
            var overtime = thisSettings.remainingTime <= 0,
                isCompleted = -1 !== $.inArray(settings.status, ['finished']);

            if (isCompleted) {
                return;
            }

            if (overtime) {
                // Disable confirm message
                $('form.save-assignment').off('submit.learn-press-confirm');
                callbackEvents.callEvent('finish');
                return;
            }
            thisSettings.remainingTime--;
            timerCountdown = setTimeout(timeCountdown, 1000);
        }

        function stopCountdown() {
            timerCountdown && clearTimeout(timerCountdown);
        }

        function initCountdown() {
            thisSettings.watchChange('remainingTime', function (prop, oldVal, newVal) {
                remainingTime = newVal;
                onTick.apply(self, [oldVal, newVal]);
                return newVal;
            });
        }

        function onTick(oldVal, newVal) {
            callbackEvents.callEvent('tick', [newVal]);
            if (newVal <= 0) {
                stopCountdown();
                callbackEvents.callEvent('finish');
            }
        }

        function showTime() {
            if (remainingTime < 0) {
                remainingTime = 0;
            }
            $timeElement.html(remainingTime.toTime());
        }

        function submit() {
            $('form.save-assignment').submit();
        }

        function init() {
            if (thisSettings.onTick) {
                self.on('tick', thisSettings.onTick);
            }

            if (thisSettings.onFinish) {
                self.on('finish', thisSettings.onFinish);
            }
            initCountdown();
            timeCountdown();
        }

        // Events
        this.on = callbackEvents.on;
        this.off = callbackEvents.off;

        if (thisSettings.totalTime > 0) {
            this.on('tick.showTime', showTime);
            this.on('finish.submit', submit);
        }

        this.getRemainingTime = function () {
            return remainingTime;
        }

        init();
    }

    var which;
    $.fn.exists = function() {
        return $(this).length > 0;
    }
    $(document).ready(function () {
        if (typeof lpAssignmentSettings !== 'undefined') {
            window.lpAssignment = new LP_Assignment(lpAssignmentSettings);
        }

        $("button").click(function () {
            which = $(this).attr("id");
        });
        $('.save-assignment').submit(function (e) {
            if (which == 'assignment-button-right') {
                var question = $('#assignment-button-right').data('confirm');
                var ok = confirm(question);
                if (ok) {
                    return true;
                }
                else {
                    //Prevent the submit event and remain on the screen
                    e.preventDefault();
                    return false;
                }
                e.preventDefault();
            }
        });

        if ($('.assignment-uploader').length > 0) {
            var options = false;
            var container = $('.assignment-uploader');
            options = JSON.parse(JSON.stringify(global_uploader_options));
            options['multipart_params']['_ajax_nonce'] = container.find('.ajaxnonce').attr('id');

            if (container.hasClass('multiple')) {
                options['multi_selection'] = true;
            }

            var uploader = new plupload.Uploader(options);
            uploader.init();

            // EVENTS
            // init
            uploader.bind('Init', function (up) {
            });

            // file added
            uploader.bind('FilesAdded', function (up, files) {
                jQuery.each(files, function (i, file) {
                    document.getElementById('filelist').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
                });

                up.refresh();
                up.start();
            });

            // upload progress
            uploader.bind('UploadProgress', function (up, file) {
            });

            // file uploaded
            uploader.bind('FileUploaded', function (up, file, response) {console.log(response)
                response = jQuery.parseJSON(response.response);

                if (response['status'] == 'success') {
                    console.log('Success', up, file, response);
                } else {
                    console.log('Error', up, file, response);
                }

            });
        }
    })


})(jQuery);

$('.remImage').live('click', function() {
    var attID = jQuery(this).attr('name');
    jQuery.ajax({
        type: 'post',
        url: '/wp-admin/admin-ajax.php',
        data: {
            action: 'delete_attachment',
            att_ID: jQuery(this).attr('name'),
            _ajax_nonce: jQuery('#nonce').val(),
            post_type: 'attachment'
        },
        success: function () {
            console.log('#file-' + attID)
            $('#file-' + attID).fadeOut();
        }
    });
})