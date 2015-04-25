var loader = {
    show: function (insideObjectName) {
        $(insideObjectName).append('<span class="loader glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>');
    },
    hide: function (insideObjectName) {
        $(insideObjectName).find('.loader').remove();
    }
};

var tabAjax = {
    reloadTab: {},
    initTab: function (callBackOnSuccess, preLoadCallback) {
        $("a[data-toggle='tab']").each(function (key, obj) {
            var href = $(obj).attr('href');
            tabAjax.reloadTab[href] = tabAjax.reloadTab[href] == undefined;
            $(obj).click(function () {
                if (tabAjax.reloadTab[href]) {
                    if (preLoadCallback != undefined) {
                        preLoadCallback(this);
                    }
                    var $contentTab = $($(this).attr('data-content'));
                    var $url = $(this).attr('data-url');
                    loader.show($contentTab);
                    $.get($url, function (data) {
                        $contentTab.html(data);
                        loader.hide($contentTab);
                        tabAjax.reloadTab[href] = false;
                        if (callBackOnSuccess != undefined) {
                            callBackOnSuccess(href);
                        }
                    })
                }
            });
        })
    },
    reloadActiveTab: function (callBackOnSuccess, preLoadCallback) {
        var $activeTab = $('ul.nav-tabs li.active a[data-toggle="tab"]');
        var $contentTab = $($activeTab.attr('data-content'));
        var $url = $activeTab.attr('data-url');
        var href = $activeTab.attr('href');
        loader.show($contentTab);
        tabAjax.reloadTab[href] = true;
        if (preLoadCallback != undefined) {
            preLoadCallback($activeTab);
        }
        $.get($url, function (data) {
            $contentTab.html(data);
            loader.hide($contentTab);
            tabAjax.reloadTab[href] = false;
            if (callBackOnSuccess != undefined) {
                callBackOnSuccess(href);
            }
        })
    }
};

var flashback = {
    container: '#flashback',
    add: function (type, text) {
        $(flashback.container).append(
            '<div class="text-' + type + '">' + text + '</div>'
        );

    }
};


