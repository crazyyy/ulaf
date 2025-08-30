/**
 * Single Assignment functions
 *
 * @author ThimPress
 * @package LearnPress/JS
 * @version 3.0.0
 */
;(function ($) {

    !Number.prototype.AssignmenttoTime && (Number.prototype.AssignmenttoTime = function (settings) {

        var MINUTE_IN_SECONDS = 60,
            HOUR_IN_SECONDS = 3600,
            DAY_IN_SECONDS = 24 * 3600,
            seconds = this + 0,
            str = '',
            singular_day_text = ' day left',
            plural_day_text = ' days left';
        if (typeof settings.singular_day_text != 'undefined') {
            singular_day_text = settings.singular_day_text;
        }
        if (typeof settings.plural_day_text != 'undefined') {
            plural_day_text = settings.plural_day_text;
        }

        if (seconds > DAY_IN_SECONDS) {
            var days = Math.ceil(seconds / DAY_IN_SECONDS);
            str = days + (days > 1 ? plural_day_text : singular_day_text);
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

        function showTime(settings) {
            if (remainingTime < 0) {
                remainingTime = 0;
            }
            if (typeof settings != 'undefined') {
                $timeElement.html(remainingTime.AssignmenttoTime(settings));
            }
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
            this.on('tick.showTime', showTime(thisSettings));
            this.on('finish.submit', submit);
        }

        this.getRemainingTime = function () {
            return remainingTime;
        }

        init();
    }

    var which;
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
            } else if (which == 'assignment-button-left') {
                var question = $('#assignment-button-left').data('confirm');
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
        $('.assignment_action_icon').live('click', function () {
            var attName = $(this).attr('name');
            var attOrder = $(this).attr('order');
            var ajax_url = $(this).attr('ajax_url');
            var useritem_id = $(this).attr('useritem_id');
            var question = $(this).data('confirm');
            var ok = confirm(question);
            if (ok) {
                var allow_amount = $('#assignment-file-amount-allow').text();
                $.ajax({
                    type: 'post',
                    url: ajax_url,
                    data: {
                        action: 'delete_assignment_upload_file',
                        attName: attName,
                        attOrder: attOrder,
                        useritem_id: useritem_id,
                        _ajax_nonce: jQuery('#assignment-file-nonce-' + attOrder).val()
                    },
                    success: function () {
                        var new_allow_amount = parseInt(allow_amount) + 1;
                        $('#assignment-uploaded-file-' + attOrder).fadeOut();
                        $('#_lp_upload_file').prop('disabled', false);
                        $('#assignment-file-amount-allow').text(new_allow_amount);
                    }
                });
            } else {
                return false;
            }
        });
    })

})(jQuery);