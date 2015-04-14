var review = {
    setReadonlyRating: function (objName) {
        $(objName).each(function (e, obj) {
            $(obj).raty({
                readOnly: false,
                starOn: '/bundles/binidiniweb/img/raty/star-on.png',
                starOff: '/bundles/binidiniweb/img/raty/star-off.png',
                score: $(this).attr('data-rating-score')
            });
        });
    }
};
