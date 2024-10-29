jQuery(document).ready(function($){
    $(window).scroll(function(){
        var scrollTop = $(this).scrollTop();
        $(".social-share-buttons_new").css("top", Math.max(300, 50 - scrollTop));
    });
});
