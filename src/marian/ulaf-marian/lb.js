jQuery(document).ready(function () {

    $('.mobil-nav-btn').click(function () {
        $('.header-nav').slideToggle('medium');
    });

    $('.menu-item-has-children').click(function () {
        $('.menu-item-has-children').children('.sub-menu').slideToggle('medium');
    });
});