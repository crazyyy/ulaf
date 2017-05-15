!function(){for(var e,l=function(){},t=["assert","clear","count","debug","dir","dirxml","error","exception","group","groupCollapsed","groupEnd","info","log","markTimeline","profile","profileEnd","table","time","timeEnd","timeline","timelineEnd","timeStamp","trace","warn"],a=t.length,o=window.console=window.console||{};a--;)e=t[a],o[e]||(o[e]=l)}(),"undefined"==typeof jQuery?console.warn("jQuery hasn't loaded"):console.log("jQuery has loaded"),function(e){"use strict";function l(){e(window).outerHeight(),e(".stm-footer").outerHeight();e("#wrapper").css({"min-height":"100%"})}function t(){e(".stm-medias-unit").length&&"function"==typeof imagesLoaded&&e(".stm-medias-unit").imagesLoaded(function(){e(".stm-medias-unit").isotope({itemSelector:".stm-media-single-unit",layoutMode:"masonry"})}),e(".stm-media-tabs-nav a").on("shown.bs.tab",function(l){var t=e(this).attr("href");e(t+" .stm-medias-unit").isotope("layout")})}function a(){var l=e(".widget_nav_menu"),t=e(".widget_categories"),a=e(".widget_recent_entries");l.length&&l.each(function(){0==e(this).closest(".footer-widgets-wrapper").length&&(e(this).addClass("stm-widget-menu"),e(this).find("a").each(function(){e(this).html("<span>"+e(this).text()+"</span>")}))}),t.length&&t.each(function(){e(this).find("a").each(function(){e(this).html("<span>"+e(this).text()+"</span>")})}),a.length&&a.each(function(){e(this).find(".post-date").length||e(this).addClass("no-date")})}function o(){var l=e(window).outerWidth(),t=e(".stm-fullwidth-row-js").find(".container").width(),a=(l-t)/2;e(".stm-fullwidth-row-js").css({"margin-left":-a+"px","margin-right":-a+"px"})}e(document).ready(function(){l(),o(),a(),t()}),e(window).load(function(){l(),o()}),e(window).resize(function(){l(),o()})}(jQuery),function(e){"use strict";function l(){if(!a.hasClass("stm-transparent-header")&&a.hasClass("stm-header-fixed-mode")){var e=a.find(".stm-header-inner").outerHeight();a.css("min-height",e+"px")}}function t(){if(a.hasClass("stm-transparent-header")&&a.hasClass("stm-header-fixed-mode")){var l=e(window).scrollTop(),t=a.offset().top;l-300>t?a.addClass("stm-header-fixed"):a.removeClass("stm-header-fixed"),l-400>t?a.addClass("stm-header-fixed-intermediate"):a.removeClass("stm-header-fixed-intermediate")}if(!a.hasClass("stm-transparent-header")&&a.hasClass("stm-header-fixed-mode")){var l=e(window).scrollTop(),t=a.offset().top;l-300>t?a.addClass("stm-header-fixed"):a.removeClass("stm-header-fixed"),l-400>t?a.addClass("stm-header-fixed-intermediate"):a.removeClass("stm-header-fixed-intermediate")}}var a=e(".stm-header");e(document).ready(function(){t(),l()}),e(window).load(function(){t(),l()}),e(window).resize(function(){t()}),e(window).scroll(function(){t()})}(jQuery),$(".grid").isotope({itemSelector:".grid-item",layoutMode:"fitRows",filter:".game-info-2017"}),$(".filters-select").on("change",function(){var e=this.value;$(".grid").isotope({filter:e}),$(".grid-item").show()}),$(".grid-2").isotope({itemSelector:".grid-item-2",layoutMode:"fitRows",filter:".team-table-position-2017"}),$(".filters-select").on("change",function(){var e=this.value;$(".grid-2").isotope({filter:e}),$(".grid-item-2").show()}),$(".grid-3").isotope({itemSelector:".grid-item-3",layoutMode:"fitRows",filter:".rating-division-a"}),$(".filters-select").on("change",function(){var e=this.value;$(".grid-3").isotope({filter:e}),$(".grid-item-3").show()}),function(){$(".sponsors-block-slider--slider").owlCarousel({loop:!0,nav:!1,items:3,autoplay:!0,autoplayTimeout:2e3,responsive:{1200:{items:3},500:{items:1},400:{items:1},300:{items:1}}}),$(".sponsors-block-slider--slider .owl-item").each(function(e,l){var t=$(this).width();$(this).height(t)})}(),function(){var e=function(){var e=$(".js-trigger"),l=$(".js-hidden"),t=$(".js-trigger-1"),a=$(".js-hidden-1");e.on("click",function(){l.slideToggle()}),t.on("click",function(){a.slideToggle()})};e()}(),function(){$(".tab-title>a").click(function(e){e.preventDefault();var l=$(this).parent().index();$(this).parent().addClass("active").siblings().removeClass("active").parent("ul.tabs").siblings(".tabs-content").children(".content").removeClass("active").eq(l).addClass("active")})}(),$(function(){$(window).scroll(function(){var e=$(window).scrollTop();e>=10?($(".stm-transparent-header .stm-header-inner").css({"background-color":"black",top:"-25px"}),$(".stm-non-transparent-header .stm-header-inner").css({top:"-25px"})):($(".stm-transparent-header .stm-header-inner").css({"background-color":"transparent",top:"50px"}),$(".stm-non-transparent-header .stm-header-inner").css({top:"50px"}))})}),AmCharts.makeChart("mapdiv",{type:"map",dataProvider:{map:"ukraineLow",getAreasFromMap:!0,showDescriptionOnHover:!0,images:[{type:"circle",label:"Lviv",latitude:49.83,longitude:24,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/lvy'>Lions Lviv</a> "},{type:"circle",label:"Odessa",latitude:46.29,longitude:30.43,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/piraty'>Pirates Odesa</a>"},{type:"circle",label:"Vinnytsia",latitude:49.14,longitude:28.28,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/vinnitskie-volki'>Wolves Vinnytsia</a>"},{type:"circle",label:"Kyiv",latitude:50.4,longitude:30.61,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/bandity'>Bandits Kyiv</a> <br><a href='http://ulafua.com/team/slavyani'> Slavs Kyiv </a> <br><a href='http://ulafua.com/team/patrioty'> Patriots Kyiv </a><br><a href='http://ulafua.com/team/rebels'> Rebels Kyiv </a><br><a href='http://ulafua.com/team/dzhokery'> Jokers Kyiv </a><br> <a href='http://ulafua.com/team/buldogi'>Bulldogs Kyiv</a>"},{type:"circle",label:"Uzhhorod",latitude:48.37,longitude:22.17,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/lesoruby'>Lumberjacks Uzhgorod</a> "},{type:"circle",label:"Kharkiv",latitude:50,longitude:36.13,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/atlanty'>Atlantes Kharkiv</a> <br> <a href='http://ulafua.com/team/tigry'>Kharkiv Tigers</a>"},{type:"circle",label:"Kamianets-Podilskyi",latitude:48.71,longitude:26.35,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/titany'>Titans-K-PNU Kamyanets-Podilsky</a>"},{type:"circle",label:"Mariupol",latitude:47.05,longitude:37.32,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/delfiny'>Azov Dolphins Mariupol</a>"},{type:"circle",label:"Mykolaiv",latitude:46.7,longitude:32,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/vikingi'>Vikings Mykolaiv</a> "},{type:"circle",label:"Kherson",latitude:46.5,longitude:32.35,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/akuly'>Sharks Kherson</a>"},{type:"circle",label:"Yuzhne",latitude:46.5,longitude:30.7,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/gepardy'>Gepards Yuzhny</a>"},{type:"circle",label:"Dnipro",latitude:48.27,longitude:34.59,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/rakety'>Rockets Dnipro</a> <br><a href='http://ulafua.com/team/spartans'>Spartans Dnipro</a> "},{type:"circle",label:"Khmelnytskyi",latitude:49.35,longitude:27,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/gladiatory'>Gladiators Khmelnytskyi</a>"},{type:"circle",label:"Zdolbuniv",latitude:50.3,longitude:26.15,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/orly'>Eagles Zdolbuniv</a> "},{type:"circle",label:"Zaporizhzhia",latitude:47.5,longitude:35.08,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/kazaki'>Cossacks Zaporizhzhia</a>"},{type:"circle",label:"Bila Tserkva",latitude:49.47,longitude:30.07,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/hawks'>Hawks Bila Tserkva</a>"}]},areasSettings:{autoZoom:!1,selectedColor:"#CC0000",color:"#999999",accessibleLabel:""},zoomControl:{zoomControlEnabled:!1,maxZoomLevel:1},smallMap:{}}),AmCharts.makeChart("mapdiv2",{type:"map",dataProvider:{map:"ukraineLow",getAreasFromMap:!0,showDescriptionOnHover:!0,images:[{type:"circle",label:"Vinnytsia",latitude:49.14,longitude:28.28,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/wolves-girls'>Wolves Vinnytsia</a>"},{type:"circle",label:"Kyiv",latitude:50.4,longitude:30.61,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/rebels-girls'></a>Rebels Kyiv <br> <a href='http://ulafua.com/team/bulldogs-girls'>Bulldogs Kyiv</a>"},{type:"circle",label:"Kamianets-Podilskyi",latitude:48.71,longitude:26.35,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/titanides'>Titanides Kamyanets-Podilsky</a>"},{type:"circle",label:"Dnipro",latitude:48.27,longitude:34.59,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/valkyrias-girls'>Valkyrias Dnipro</a>"}]},areasSettings:{autoZoom:!1,selectedColor:"#CC0000",color:"#999999",accessibleLabel:""},zoomControl:{zoomControlEnabled:!1,maxZoomLevel:1},smallMap:{}}),AmCharts.makeChart("mapdiv3",{type:"map",dataProvider:{map:"ukraineLow",getAreasFromMap:!0,showDescriptionOnHover:!0,images:[{type:"circle",label:"Lviv",latitude:49.83,longitude:24,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/lions-u-15'>Lions Lviv</a> "},{type:"circle",label:"Vinnytsia",latitude:49.14,longitude:28.28,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/wolves-u-15'>Wolves Vinnytsia</a>"},{type:"circle",label:"Kyiv",latitude:50.4,longitude:30.61,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/patriots-u-15'>Patriots Kyiv</a>"},{type:"circle",label:"Mariupol",latitude:47.05,longitude:37.32,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/azov-dolphins-u-15'>Azov Dolphins Mariupol</a>"},{type:"circle",label:"Mykolaiv",latitude:46.7,longitude:32,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/vikings-u-15'>Vikings Mykolaiv</a> "},{type:"circle",label:"Yuzhne",latitude:46.5,longitude:30.7,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/gepards-u-15'>Gepards Yuzhny</a>"},{type:"circle",label:"Khmelnytskyi",latitude:49.35,longitude:27,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/silver-bullets-u-15'>Silver Bullets Khmelnytskyi</a>"},{type:"circle",label:"Zdolbuniv",latitude:50.3,longitude:26.15,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/eagles-u-15'>Eagles Zdolbuniv</a> "},{type:"circle",label:"Bila Tserkva",latitude:49.47,longitude:30.07,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='http://ulafua.com/team/hawks-u-15'>Hawks Bila Tserkva</a>"}]},areasSettings:{autoZoom:!1,selectedColor:"#CC0000",color:"#999999",accessibleLabel:""},zoomControl:{zoomControlEnabled:!1,maxZoomLevel:1},smallMap:{}}),AmCharts.makeChart("mapdiv4",{type:"map",dataProvider:{map:"ukraineLow",getAreasFromMap:!0,showDescriptionOnHover:!0,images:[{type:"circle",label:"Lviv",latitude:49.83,longitude:24,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='https://vk.com/cheerteam_lions'>Lions Lviv</a> "},{type:"circle",label:"Odessa",latitude:46.29,longitude:30.43,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='https://vk.com/odessapirates'>Pirates Odesa</a>"},{type:"circle",label:"Vinnytsia",latitude:49.14,longitude:28.28,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='https://vk.com/wolvescheerleadingteam'>Wolves Vinnytsia</a>"},{type:"circle",label:"Kyiv",latitude:50.4,longitude:30.61,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='https://vk.com/cheer_bandits'>Bandits Kyiv</a>  <br> <a href='https://vk.com/slavs_cheerleading_girls?_smt=groups_list%3A1'>Slavs Kyiv</a>  <br> <a href='https://vk.com/patriots_cheer'>Patriots Kyiv</a> <br> <a href='https://vk.com/jokers_sweets'>Jokers Kyiv</a> <br> <a href='https://vk.com/bulldogs_cheerleaders'>Bulldogs Kyiv</a>"},{type:"circle",label:"Kamianets-Podilskyi",latitude:48.71,longitude:26.35,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='https://vk.com/kp_af'>Titans-K-PNU Kamyanets-Podilsky</a>"},{type:"circle",label:"Mariupol",latitude:47.05,longitude:37.32,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='https://vk.com/azov_dolphins'>Azov Dolphins Mariupol</a>"},{type:"circle",label:"Mykolaiv",latitude:46.7,longitude:32,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='https://vk.com/cheerleading_niko'>Vikings Mykolaiv</a> "},{type:"circle",label:"Dnipro",latitude:48.27,longitude:34.59,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='https://vk.com/show_models_dnepr'>Rockets Dnipro</a>"},{type:"circle",label:"Khmelnytskyi",latitude:49.35,longitude:27,labelColor:"#cc0000",labelRollOverColor:"#000000",title:"",description:"<a href='https://vk.com/club123202350'>Gladiators Khmelnytskyi</a>"}]},areasSettings:{autoZoom:!1,selectedColor:"#CC0000",color:"#999999",accessibleLabel:""},zoomControl:{zoomControlEnabled:!1,maxZoomLevel:1},smallMap:{}});
//# sourceMappingURL=maps/scripts.js.map
