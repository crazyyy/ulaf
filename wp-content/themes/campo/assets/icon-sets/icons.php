<?php
$icons = sanitize_text_field('

@font-face{ font-family:"Essential";src:url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/Essential/Essential.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/Essential/Essential.ttf' ) . '") format("truetype"); }
*[data-ico-essential]:before{ font-family:Essential;content:attr(data-ico-essential); }

@font-face{ font-family:"FontAwesome5Brands";src:url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome5Brands/FontAwesome5Brands.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome5Brands/FontAwesome5Brands.ttf' ) . '") format("truetype"); }
*[data-ico-fontawesome5brands]:before{ font-family:FontAwesome5Brands;content:attr(data-ico-fontawesome5brands); }

@font-face{ font-family:"FontAwesome5Regular";src:url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome5Regular/FontAwesome5Regular.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome5Regular/FontAwesome5Regular.ttf' ) . '") format("truetype"); }
*[data-ico-fontawesome5regular]:before{ font-family:FontAwesome5Regular;content:attr(data-ico-fontawesome5regular); }

@font-face{ font-family:"FontAwesome5Solid";src:url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome5Solid/FontAwesome5Solid.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome5Solid/FontAwesome5Solid.ttf' ) . '") format("truetype"); }
*[data-ico-fontawesome5solid]:before{ font-family:FontAwesome5Solid;content:attr(data-ico-fontawesome5solid); }

@font-face{ font-family:"FontAwesome6Brands";src:url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome6Brands/FontAwesome6Brands.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome6Brands/FontAwesome6Brands.ttf' ) . '") format("truetype"); }
*[data-ico-fontawesome6brands]:before{ font-family:FontAwesome6Brands;content:attr(data-ico-fontawesome6brands); }

@font-face{ font-family:"FontAwesome6Regular";src:url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome6Regular/FontAwesome6Regular.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome6Regular/FontAwesome6Regular.ttf' ) . '") format("truetype"); }
*[data-ico-fontawesome6regular]:before{ font-family:FontAwesome6Regular;content:attr(data-ico-fontawesome6regular); }

@font-face{ font-family:"FontAwesome6Solid";src:url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome6Solid/FontAwesome6Solid.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/FontAwesome6Solid/FontAwesome6Solid.ttf' ) . '") format("truetype"); }
*[data-ico-fontawesome6solid]:before{ font-family:FontAwesome6Solid;content:attr(data-ico-fontawesome6solid); }

@font-face{ font-family:"Icon7Stroke";src:url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/Icon7Stroke/Icon7Stroke.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'framework/assets/icon-sets/Icon7Stroke/Icon7Stroke.ttf' ) . '") format("truetype"); }
*[data-ico-icon7stroke]:before{ font-family:Icon7Stroke;content:attr(data-ico-icon7stroke); }

@font-face{ font-family:"AmericanFootball";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/AmericanFootball/AmericanFootball.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/AmericanFootball/AmericanFootball.ttf' ) . '") format("truetype"); }
*[data-ico-americanfootball]:before{ font-family:AmericanFootball;content:attr(data-ico-americanfootball); }

@font-face{ font-family:"Basket";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Basket/Basket.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Basket/Basket.ttf' ) . '") format("truetype"); }
*[data-ico-basket]:before{ font-family:Basket;content:attr(data-ico-basket); }

@font-face{ font-family:"Basketball";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Basketball/Basketball.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Basketball/Basketball.ttf' ) . '") format("truetype"); }
*[data-ico-basketball]:before{ font-family:Basketball;content:attr(data-ico-basketball); }

@font-face{ font-family:"Box";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Box/Box.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Box/Box.ttf' ) . '") format("truetype"); }
*[data-ico-box]:before{ font-family:Box;content:attr(data-ico-box); }

@font-face{ font-family:"Boxing";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Boxing/Boxing.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Boxing/Boxing.ttf' ) . '") format("truetype"); }
*[data-ico-boxing]:before{ font-family:Boxing;content:attr(data-ico-boxing); }

@font-face{ font-family:"Football";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Football/Football.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Football/Football.ttf' ) . '") format("truetype"); }
*[data-ico-football]:before{ font-family:Football;content:attr(data-ico-football); }

@font-face{ font-family:"Golf";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Golf/Golf.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Golf/Golf.ttf' ) . '") format("truetype"); }
*[data-ico-golf]:before{ font-family:Golf;content:attr(data-ico-golf); }

@font-face{ font-family:"IoniconsFilled";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/IoniconsFilled/IoniconsFilled.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/IoniconsFilled/IoniconsFilled.ttf' ) . '") format("truetype"); }
*[data-ico-ioniconsfilled]:before{ font-family:IoniconsFilled;content:attr(data-ico-ioniconsfilled); }

@font-face{ font-family:"IoniconsLogos";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/IoniconsLogos/IoniconsLogos.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/IoniconsLogos/IoniconsLogos.ttf' ) . '") format("truetype"); }
*[data-ico-ioniconslogos]:before{ font-family:IoniconsLogos;content:attr(data-ico-ioniconslogos); }

@font-face{ font-family:"IoniconsOutline";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/IoniconsOutline/IoniconsOutline.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/IoniconsOutline/IoniconsOutline.ttf' ) . '") format("truetype"); }
*[data-ico-ioniconsoutline]:before{ font-family:IoniconsOutline;content:attr(data-ico-ioniconsoutline); }

@font-face{ font-family:"IoniconsSharp";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/IoniconsSharp/IoniconsSharp.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/IoniconsSharp/IoniconsSharp.ttf' ) . '") format("truetype"); }
*[data-ico-ioniconssharp]:before{ font-family:IoniconsSharp;content:attr(data-ico-ioniconssharp); }

@font-face{ font-family:"MartialArts";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/MartialArts/MartialArts.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/MartialArts/MartialArts.ttf' ) . '") format("truetype"); }
*[data-ico-martialarts]:before{ font-family:MartialArts;content:attr(data-ico-martialarts); }

@font-face{ font-family:"RemixIconsBuildings";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsBuildings/RemixIconsBuildings.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsBuildings/RemixIconsBuildings.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsbuildings]:before{ font-family:RemixIconsBuildings;content:attr(data-ico-remixiconsbuildings); }

@font-face{ font-family:"RemixIconsBusiness";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsBusiness/RemixIconsBusiness.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsBusiness/RemixIconsBusiness.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsbusiness]:before{ font-family:RemixIconsBusiness;content:attr(data-ico-remixiconsbusiness); }

@font-face{ font-family:"RemixIconsCommunication";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsCommunication/RemixIconsCommunication.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsCommunication/RemixIconsCommunication.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconscommunication]:before{ font-family:RemixIconsCommunication;content:attr(data-ico-remixiconscommunication); }

@font-face{ font-family:"RemixIconsDesign";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsDesign/RemixIconsDesign.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsDesign/RemixIconsDesign.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsdesign]:before{ font-family:RemixIconsDesign;content:attr(data-ico-remixiconsdesign); }

@font-face{ font-family:"RemixIconsDevelopment";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsDevelopment/RemixIconsDevelopment.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsDevelopment/RemixIconsDevelopment.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsdevelopment]:before{ font-family:RemixIconsDevelopment;content:attr(data-ico-remixiconsdevelopment); }

@font-face{ font-family:"RemixIconsDevice";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsDevice/RemixIconsDevice.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsDevice/RemixIconsDevice.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsdevice]:before{ font-family:RemixIconsDevice;content:attr(data-ico-remixiconsdevice); }

@font-face{ font-family:"RemixIconsDocument";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsDocument/RemixIconsDocument.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsDocument/RemixIconsDocument.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsdocument]:before{ font-family:RemixIconsDocument;content:attr(data-ico-remixiconsdocument); }

@font-face{ font-family:"RemixIconsEditor";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsEditor/RemixIconsEditor.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsEditor/RemixIconsEditor.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconseditor]:before{ font-family:RemixIconsEditor;content:attr(data-ico-remixiconseditor); }

@font-face{ font-family:"RemixIconsFinance";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsFinance/RemixIconsFinance.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsFinance/RemixIconsFinance.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsfinance]:before{ font-family:RemixIconsFinance;content:attr(data-ico-remixiconsfinance); }

@font-face{ font-family:"RemixIconsHealth";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsHealth/RemixIconsHealth.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsHealth/RemixIconsHealth.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconshealth]:before{ font-family:RemixIconsHealth;content:attr(data-ico-remixiconshealth); }

@font-face{ font-family:"RemixIconsLogos";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsLogos/RemixIconsLogos.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsLogos/RemixIconsLogos.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconslogos]:before{ font-family:RemixIconsLogos;content:attr(data-ico-remixiconslogos); }

@font-face{ font-family:"RemixIconsMap";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsMap/RemixIconsMap.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsMap/RemixIconsMap.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsmap]:before{ font-family:RemixIconsMap;content:attr(data-ico-remixiconsmap); }

@font-face{ font-family:"RemixIconsMedia";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsMedia/RemixIconsMedia.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsMedia/RemixIconsMedia.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsmedia]:before{ font-family:RemixIconsMedia;content:attr(data-ico-remixiconsmedia); }

@font-face{ font-family:"RemixIconsOthers";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsOthers/RemixIconsOthers.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsOthers/RemixIconsOthers.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsothers]:before{ font-family:RemixIconsOthers;content:attr(data-ico-remixiconsothers); }

@font-face{ font-family:"RemixIconsSystem";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsSystem/RemixIconsSystem.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsSystem/RemixIconsSystem.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconssystem]:before{ font-family:RemixIconsSystem;content:attr(data-ico-remixiconssystem); }

@font-face{ font-family:"RemixIconsUser";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsUser/RemixIconsUser.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsUser/RemixIconsUser.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsuser]:before{ font-family:RemixIconsUser;content:attr(data-ico-remixiconsuser); }

@font-face{ font-family:"RemixIconsWeather";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsWeather/RemixIconsWeather.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/RemixIconsWeather/RemixIconsWeather.ttf' ) . '") format("truetype"); }
*[data-ico-remixiconsweather]:before{ font-family:RemixIconsWeather;content:attr(data-ico-remixiconsweather); }

@font-face{ font-family:"Soccer";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Soccer/Soccer.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Soccer/Soccer.ttf' ) . '") format("truetype"); }
*[data-ico-soccer]:before{ font-family:Soccer;content:attr(data-ico-soccer); }

@font-face{ font-family:"SuperBowl";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/SuperBowl/SuperBowl.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/SuperBowl/SuperBowl.ttf' ) . '") format("truetype"); }
*[data-ico-superbowl]:before{ font-family:SuperBowl;content:attr(data-ico-superbowl); }

@font-face{ font-family:"Tennis";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Tennis/Tennis.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Tennis/Tennis.ttf' ) . '") format("truetype"); }
*[data-ico-tennis]:before{ font-family:Tennis;content:attr(data-ico-tennis); }

@font-face{ font-family:"Tennis02";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Tennis02/Tennis02.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Tennis02/Tennis02.ttf' ) . '") format("truetype"); }
*[data-ico-tennis02]:before{ font-family:Tennis02;content:attr(data-ico-tennis02); }

@font-face{ font-family:"Tennis03";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Tennis03/Tennis03.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Tennis03/Tennis03.ttf' ) . '") format("truetype"); }
*[data-ico-tennis03]:before{ font-family:Tennis03;content:attr(data-ico-tennis03); }

@font-face{ font-family:"Volleyball";src:url("' . get_parent_theme_file_uri( 'assets/icon-sets/Volleyball/Volleyball.woff' ) . '") format("woff"),url("' . get_parent_theme_file_uri( 'assets/icon-sets/Volleyball/Volleyball.ttf' ) . '") format("truetype"); }
*[data-ico-volleyball]:before{ font-family:Volleyball;content:attr(data-ico-volleyball); }', array() );