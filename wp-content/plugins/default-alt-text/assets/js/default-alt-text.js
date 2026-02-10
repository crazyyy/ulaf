document.addEventListener('DOMContentLoaded', function() {
    var images = document.querySelectorAll('img');

    images.forEach(function(img) {
        var alt = img.getAttribute('alt');
        var title = img.getAttribute('title');

        if (!alt || alt.trim() === '') {
            if (title) {
                img.setAttribute('alt', title);
            } else {
                var src = img.getAttribute('src');
                if (src) {
                    var filename = src.substring(src.lastIndexOf('/') + 1).split('.')[0];
                    img.setAttribute('alt', filename);
                }
            }
        }
    });
});
