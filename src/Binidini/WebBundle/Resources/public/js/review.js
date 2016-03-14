var review = {
    starOn: '/bundles/binidiniweb/img/raty/star-on.png',
    starOff: '/bundles/binidiniweb/img/raty/star-off.png',
    setReadonlyRating: function (objName) {
        $(objName).each(function (e, obj) {
            $(obj).raty({
                readOnly: true,
                starOn: review.starOn,
                starOff: review.starOff,
                score: $(this).attr('data-rating-score')
            });
        });
    },
    setRatingControl: function (objName, onClick) {
        $(objName).raty({
            starOn: review.starOn,
            starOff: review.starOff,
            click: onClick
        });
    }
};
