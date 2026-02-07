/**
 * SThemes
 * Created by Пользователь on 17.01.2017.
 */

(function($) {
    $(document).ready(function() {
        if($("body").find(".stmTickerPostsList").find("li").length > 0) {

            var tm = $(".stmTickerPostsList").attr("data-auto_play_speed");
            var animateSpeed = $(".stmTickerPostsList").attr("data-animate_speed");
            var timerId = setInterval(function () {
                var tickerList = $(".stmTickerPostsList");

                var tickerFirstItem = (tickerList.attr("data-direction") == "up") ? $(".stmTickerPostsList li:first-child") : $(".stmTickerPostsList li:last-child");
                var tickerFirstItemHtml = (tickerList.attr("data-direction") == "up") ? document.getElementsByClassName('tickerItem').item(0).outerHTML : document.getElementsByClassName('tickerItem').item(parseInt(tickerList.attr("data-count-posts")) - 1).outerHTML;

                if (tickerList.attr("data-direction") == "up") {
                    $(".stmTickerPostsList").append(tickerFirstItemHtml);
                    tickerFirstItem.animate({
                        'marginTop': '-60px'
                    }, animateSpeed, function () {
                        tickerFirstItem.remove();
                    });
                } else {
                    $(".stmTickerPostsList").prepend(tickerFirstItemHtml);

                    tickerFirstItem.animate({
                        'marginBottom': '-60px'
                    }, animateSpeed, function () {
                        tickerFirstItem.remove();
                    });
                }

            }, tm);
        }
    });
})(jQuery);
