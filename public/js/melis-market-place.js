window.fetchPackages = function (page, search, orderBy, order, itemPerPage, group) {

    page = page || 1;
    search = search || $("body").find("input#melis_market_place_search_input").val();
    orderBy = orderBy || 'mp_total_downloads';
    order = order || 'desc';
    itemPerPage = itemPerPage || 9;

    if (!group) {
        group = ["1", "2", "3", "4", '5'];
    }

    $(".market-place-btn-filter-group button").attr("disabled", "disabled");
    $("#btnMarketPlaceSearch").attr("disabled", "disabled");
    $.ajax({
        type: 'POST',
        url: "/melis/MelisMarketPlace/MelisMarketPlace/package-list?page=" + page + "&search=" + search + "&orderBy=" + orderBy + "&group=" + group,
        data: {page: page, search: search, orderBy: orderBy, order: order, itemPerPage: itemPerPage, group: group},
        dataType: "html",
        success: function (data) {

            $("body").find("div#melis-market-place-package-list").html(data);
            $(".market-place-btn-filter-group button").removeAttr("disabled", "disabled");
            $("#btnMarketPlaceSearch").removeAttr("disabled", "disabled");
        },
    });
};

function getActiveGroupIdFilter() {
    var groupId = $(".market-place-btn-filter-group").find('.active');
    var btnId = [];
    var tmpData = {};

    //get the active buttons Id
    for (var ctr = 0; ctr < groupId.length; ctr++) {
        var dataId = $(groupId[ctr]).val();

        btnId.push(dataId);
    }
    tmpData = [btnId];

    return tmpData;
}

function initSlick(tab) {
    // Big Slider
    $('#' + tab + ' .slider-single').not('.slick-initialized').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        adaptiveHeight: true,
    });

    // Navigation Slider
    $('#' + tab + ' .slider-nav')
        .not('.slick-initialized')
        .on('init', function (event, slick) {
            $('#' + tab + ' .slider-nav .slick-slide.slick-current').addClass('is-active');
        })
        .slick({
            slidesToShow: 6,
            slidesToScroll: 6,
            dots: false,
            focusOnSelect: false,
            infinite: false,
            responsive: [{
                breakpoint: 1400,
                settings: {
                    slidesToShow: 6,
                    slidesToScroll: 6,
                }
            }, {
                breakpoint: 992,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 4,
                }
            }, {
                breakpoint: 767,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                }
            }]
        });

    // Detect active nav slider
    $('#' + tab + ' .slider-single').on('afterChange', function (event, slick, currentSlide) {
        $('#' + tab + ' .slider-nav').slick('slickGoTo', currentSlide);
        var currrentNavSlideElem = '#' + tab + ' .slider-nav .slick-slide[data-slick-index="' + currentSlide + '"]';
        $('#' + tab + ' .slider-nav .slick-slide.is-active').removeClass('is-active');
        $(currrentNavSlideElem).addClass('is-active');
    });

    $('#' + tab + ' .slider-nav').on('click', '.slick-slide', function (event) {
        event.preventDefault();
        var goToSingleSlide = $(this).data('slick-index');

        $('#' + tab + ' .slider-single').slick('slickGoTo', goToSingleSlide);
    });
}

$(function () {

    var preventModalClose = true;

    // Tab Click
    $("body").on('shown.bs.tab', "#melis-id-nav-bar-tabs [data-tool-meliskey='melis_market_place_tool_package_display'] a[data-toggle='tab']", function (e) {
        initSlick(activeTabId);
    });

    $('body').on('click', '#melis-marketplace-setup-modal-submit', function () { // @status: currently working
        var form = $('#melis-marketplace-setup-modal-submit').parents().find('form');

        if (form.length) {
            var modal = $('#id_melis_market_place_module_setup_form_content');
            var data = form.serializeArray(); //new FormData(form[0]);
            var action = modal.data().action;
            var module = modal.data().module

            data.push({name: 'module', value: module});
            data.push({name: 'action', value: action});
            melisCoreTool.pending("#melis-marketplace-setup-modal-submit");

            doAjax('POST', '/melis/MelisMarketPlace/MelisMarketPlace/validateSetupForm', $.param(data), function (response) {
                // display errors if it has
                if (response.result.errors != null || typeof response.result.errors !== 'undefined') {
                    if (response.result.success) {
                        alert('successful');
                        // if everything went well, call the submitAction to process the data
                        doAjax('POST' ,'/melis/MelisMarketPlace/MelisMarketPlace/submitSetupForm', $.param(data), function (response) {
                            if (response.result.success) {
                                // unplug module
                                doAjax('POST', '/melis/MelisMarketPlace/MelisMarketPlace/unplugModule', {module : module}, function (response) {
                                    if (response.success === true) {
                                        preventModalClose = false;
                                        $('#id_melis_market_place_module_setup_form_content_ajax_container').modal('hide');

                                        // inform the user that everything is good
                                        melisHelper.melisOkNotification(translations.tr_melis_market_place_setup_title.replace('%s', response.module), response.result.message);

                                        // ask the user if they want to activate the module, this will only happen if the action is "download"
                                        if (action === 'download') {
                                            $("button.melis-marketplace-modal-activate-module").removeClass("hidden");
                                        }

                                        // ask the user if they want to reload the page
                                        $("button.melis-marketplace-modal-reload").removeClass("hidden");
                                    } else {
                                        throw new Error(translations.tr_melis_market_place_plug_module_ko.replace('%s', module));
                                    }
                                });

                            } else {
                                melisHelper.melisKoNotification(translations.tr_melis_market_place_setup_title.replace('%s', response.module), response.result.message, response.result.errors);
                                melisCoreTool.highlightErrors(response.result.success, response.result.errors, form.prop('id'));
                            }
                        });

                        // end of execution
                    } else {
                        melisHelper.melisKoNotification(translations.tr_melis_market_place_setup_title.replace('%s', response.module), response.result.message, response.result.errors);
                        melisCoreTool.highlightErrors(response.result.success, response.result.errors, form.prop('id'));
                    }

                }
                melisCoreTool.done("#melis-marketplace-setup-modal-submit");
            });
        }
    });

    $("body").on("click", "button.melis-marketplace-product-action", function () {
        var action = $(this).data().action;
        var package = $(this).data().package;
        var module = $(this).data().module;

        var zoneId = "id_melis_market_place_tool_package_modal_content";
        var melisKey = "melis_market_place_tool_package_modal_content";
        var modalUrl = "/melis/MelisMarketPlace/MelisMarketPlace/toolProductModalContainer";

        var objData = {action: action, package: package, module: module};

        melisCoreTool.pending("button");
        if (action === "remove") {

            var tables = [];
            var files = [];
            doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/getModuleTables", {module: module}, function (data) {
                tables = data.tables;
                files = data.files;
            });

            doAjax("POST", "/melis/MelisCore/Modules/getDependents", {
                module: module,
                tables: tables,
                files: files
            }, function (data) {
                var modules = "<br/><br/><div class='container'><div class='row'><div class='col-lg-12'><ul>%s</ul></div></div></div>";
                var moduleList = '';

                $.each(data.modules, function (i, v) {
                    moduleList += "<li>" + v + "</li>";
                });

                modules = modules.replace("%s", moduleList);

                if (data.success) {
                    melisCoreTool.confirm(
                        translations.tr_meliscore_common_yes,
                        translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,
                        translations.tr_meliscore_general_proceed,
                        translations.melis_market_place_tool_package_remove_confirm_on_dependencies.replace("%s", module) + modules + "<br/>" + translations.melis_market_place_tool_package_remove_confirm.replace("%s", module),
                        function () {
                            melisHelper.createModal(zoneId, melisKey, false, objData, modalUrl, function () {

                                melisCoreTool.done("button");
                                checkPermission(module, function () {
                                    doEvent(objData, function () {
                                        postDeleteEvent(module, tables, files);
                                    });
                                });

                            });
                        }
                    );
                }

                if (moduleList === "") {
                    melisCoreTool.confirm(
                        translations.tr_meliscore_common_yes,
                        translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,
                        translations.tr_meliscore_general_confirm,
                        translations.melis_market_place_tool_package_remove_confirm.replace("%s", module),
                        function () {
                            melisHelper.createModal(zoneId, melisKey, true, objData, modalUrl, function () {
                                melisCoreTool.done("button");
                                checkPermission(module, function () {
                                    doEvent(objData, function () {
                                        postDeleteEvent(module, tables, files);
                                    });
                                });
                            });
                        }
                    );
                }

                melisCoreTool.done("button");
                $('div[data-module-name]').bootstrapSwitch('setActive', true);
                $("h4#meliscore-tool-module-content-title").html(translations.tr_meliscore_module_management_modules);
            });
        }
        else {
            // Download and update action
            var modalTitle = translations.tr_market_place_modal_download_title;
            var modalContent = translations.tr_market_place_modal_download_content.replace('%s', module);

            if (action === 'update') {
                modalTitle = translations.tr_market_place_modal_update_title;
                modalContent = translations.tr_market_place_modal_update_content.replace('%s', module);
            }

            melisCoreTool.confirm(
                translations.tr_meliscore_common_yes,
                translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,
                modalTitle,
                modalContent,
                function () {
                    function processWorkFlow() {
                        return new Promise(function (resolve, reject) {
                            melisHelper.createModal(zoneId, melisKey, false, objData, modalUrl, function () {
                                melisCoreTool.done("button");
                                doEvent(objData, function () {
                                    // check if the module exists
                                    axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/isModuleExists', {module: module})
                                        .then(function (response) {
                                            if (response.data.isExist || response.data.isExist === true) {
                                                // show reload and activate module buttons
                                                axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/execDbDeploy', {module: response.data.module})
                                                    .then(function (response) {
                                                        if (response.data.success === true) {
                                                            // replace this text with "Checking additional setup..."
                                                            updateCmdText(translations.tr_melis_market_place_task_done);
                                                            // stored to an object, since native Promise object doesn't pass multiple args
                                                            var payload = Object.assign({action: action}, {data: response.data}, {module: module});
                                                            resolve(payload);
                                                        } else {
                                                            reject(data);
                                                        }
                                                    });
                                            }
                                        });
                                });
                            });
                        });
                    }


                    processWorkFlow()
                        .then(function (payload) { // @status done | tested
                            // plug module
                            var module = payload.module;
                            axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/plugModule', {module : module})
                                .then(function (response) {
                                    if (response.data.success === true) {
                                        return payload;
                                    } else {
                                        throw new Error(translations.tr_melis_market_place_plug_module_ko.replace('%s', module));
                                    }
                                })
                                .then(function (payload) {
                                    if (typeof payload === 'undefined' || typeof payload == null) {
                                        melisHelper.melisKoNotification('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                                        return Promise.reject('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                                    }

                                    // check if the module has a form setup
                                    var hasSetupForm = false;
                                    var form = null;

                                    axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/getSetupModuleForm', {action: payload.action, module: payload.module})
                                        .then(function (response) {
                                            if (response.data.form !== '' || response.data.form != null) {
                                                hasSetupForm = true;
                                                return Object.assign(payload, {hasSetupForm});
                                            }
                                        })
                                        .then(function (payload) {
                                            if (typeof payload === 'undefined' || typeof payload == null) {
                                                melisHelper.melisKoNotification('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                                                return Promise.reject('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                                            }

                                            if (payload.hasSetupForm) {
                                                // ask the user to proceed or skip setup
                                                var skip = true;
                                                melisCoreTool.confirm(
                                                    translations.tr_meliscore_common_yes,
                                                    translations.tr_melis_marketplace_common_no_skip,
                                                    translations.tr_melis_market_place_setup_title.replace('%s', payload.module),
                                                    translations.tr_melis_market_place_has_setup_form.replace('%s', payload.module),
                                                    function () {
                                                        // show the setup form, but verify if the form has a content
                                                        if (payload.form) {
                                                            skip = false;

                                                            // open a new modal with the setup form
                                                            melisHelper.createModal('id_melis_market_place_module_setup_form_content_ajax',
                                                                'melis_market_place_module_setup_form_content', false, payload, modalUrl, function () {
                                                                    melisCoreTool.done("button");
                                                                });

                                                        } else {
                                                            console.log('form is empty, skipping...');
                                                        }
                                                    }
                                                );
                                            }

                                            return Object.assign(payload, {skip});
                                        })
                                        .then(function (payload) {
                                            // if user has skipped the setup form
                                            if (typeof payload === 'undefined' || typeof payload == null) {
                                                melisHelper.melisKoNotification('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                                                return Promise.reject('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                                            }

                                            if (skip) {
                                                // unplug module
                                                var module = payload.module;
                                                axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/unplugModule', {module : module})
                                                    .then(function (response) {
                                                        if (response.data.success === false) {
                                                            throw new Error(translations.tr_melis_market_place_plug_module_ko.replace('%s', module));
                                                        }
                                                    });

                                                // ask the user if they want to activate the module, this will only happen if the action is "download"
                                                if (payload.action === 'download' || payload.form === '' || payload.form === null) {
                                                    $("button.melis-marketplace-modal-activate-module").removeClass("hidden");
                                                }

                                                $("button.melis-marketplace-modal-reload").removeClass("hidden");
                                            }

                                            return payload;
                                        });
                                });

                            return payload;

                    })
                        // .then(function (payload) { // @status done | tested
                        //     if (typeof payload === 'undefined' || typeof payload == null) {
                        //         melisHelper.melisKoNotification('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                        //         return Promise.reject('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                        //     }
                        //
                        //     console.log('test', payload);
                        //
                        //     // check if the module exists in the module loader
                        //     var module = payload.module;
                        //     axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/isModuleActive', {module : module})
                        //         .then(function (response) {
                        //             if (response.data.success === true) {
                        //                 return payload;
                        //             } else {
                        //                 melisHelper.melisKoNotification('Melis Marketplace', translations.tr_melis_market_place_plug_module_ko.replace('%s', module));
                        //                 throw new Error(translations.tr_melis_market_place_plug_module_ko.replace('%s', module));
                        //             }
                        //         });
                        //
                        //     return payload;
                        // })
                        .catch(function (err) {
                        console.log(err);
                    })
                }
            );

            melisCoreTool.done("button");
        }

    });

    $("body").on("click", "button.melis-marketplace-modal-activate-module", function () {
        var module = $(this).data().module;
        doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/activateModule", {module: module}, function () {
            $("button.melis-marketplace-modal-reload").trigger("click");
        });
    });


    $("body").on("click", "button.melis-marketplace-modal-reload", function () {
        melisCoreTool.processing();
        location.reload(true);
    });

    function checkPermission(module, callback) {
        var vConsole = $("body").find("#melis-marketplace-event-do-response");
        var vConsoleText = vConsole.html();

        doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/isPackageDirectoryRemovable", {module: module}, function (resp) {
            if (resp.success == "1" || resp.success === 1) {
                callback();
            }
            else {
                doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/changePackageDirectoryPermission", {module: module}, function (response) {
                    vConsole.html(vConsoleText + '<br/><span style="color:#02de02">' + translations.tr_melis_marketplace_package_directory_change_permission.replace("%s", module) + '</span><br/>');
                    if (resp.success == "1") {
                        callback();
                    }
                    else {
                        vConsole.html(vConsoleText + '<br/><span style="color:#ff190d">' + response.message + '</span>');
                        vConsole.animate({
                            scrollTop: vConsole.prop("scrollHeight")
                        }, 1115);
                    }
                });
            }

        });
    }

    function postDeleteEvent(module, tables, files) {

        var vConsole = $("body").find("#melis-marketplace-event-do-response");
        var vConsoleText = vConsole.html() + '<br/>';

        // check if the module still exists
        doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/isModuleExists", {module: module}, function (module) {

            if (!module.isExist || module.isExist === false) {

                vConsole.html(vConsoleText + '<br/><span style="color:#02de02">' + translations.melis_market_place_tool_package_remove_ok.replace("%s", module.module) + '</span>');

                // export tables
                if (tables.length) {
                    vConsole.html(vConsoleText + '<br/><span style="color:#fbff0f">' + translations.melis_market_place_tool_package_remove_table_dump.replace("%s", module.module) + '</span>');
                    vConsole.animate({
                        scrollTop: vConsole.prop("scrollHeight")
                    }, 1115);

                    $.ajax({
                        type: 'POST',
                        url: '/melis/MelisMarketPlace/MelisMarketPlace/exportTables',
                        data: {module: module.module, tables: tables, files: files},
                        success: function (data, textStatus, request) {
                            var vConsoleText = vConsole.html();
                            // if data is not empty
                            if (data) {
                                var isError = request.getResponseHeader("error");
                                if (isError === "0") {
                                    var fileName = request.getResponseHeader("fileName");
                                    var blob = new Blob([data], {type: "application/sql;charset=utf-8"});
                                    saveAs(blob, fileName);
                                    vConsole.animate({
                                        scrollTop: vConsole.prop("scrollHeight")
                                    }, 1115);
                                    $("button.melis-marketplace-modal-reload").removeClass("hidden");
                                    vConsole.html(vConsoleText + '<br/>' + translations.tr_melis_market_place_export_table_ok + '<br/><span style="color:#02de02">Done</span>');
                                }
                                else {
                                    vConsoleText = vConsole.html();
                                    vConsole.html(vConsoleText + '<br/><span style="color:#fbff0f">' + data.message + '</span>');
                                    vConsoleText = vConsole.html();
                                    vConsole.html(vConsoleText + '<br/><span style="color:#02de02">' + translations.tr_melis_market_place_task_done + '</span>');
                                    vConsole.animate({
                                        scrollTop: vConsole.prop("scrollHeight")
                                    }, 1115);
                                }
                            }
                        }
                    });
                }
                $("button.melis-marketplace-modal-reload").removeClass("hidden");

            }
            else {
                vConsole.html(vConsoleText + '<br/><span style="color:#ff190d">' + translations.melis_market_place_tool_package_remove_ko.replace("%s", module.module) + '</span>');
                vConsole.animate({
                    scrollTop: vConsole.prop("scrollHeight")
                }, 1115);
            }
        });

    }

    function doAjax(type, url, data, callbackOnSuccess, callbackOnFail) {
        $.ajax({
            type: type,
            url: url,
            data: data,
            dataType: 'json',
            encode: true,
            // processData: false
        }).success(function (data) {
            if (callbackOnSuccess !== undefined || callbackOnSuccess !== null) {
                if (callbackOnSuccess) {
                    callbackOnSuccess(data);
                }
            }
        }).error(function (e) {
            if (callbackOnFail !== undefined || callbackOnFail !== null) {
                if (callbackOnFail) {
                    callbackOnFail(data);
                }
            }
        });
    }

    function axiosPost(url, data)
    {
        return axiosXhr('POST', url, data);
    }

    function axiosGet(url, data)
    {
        return axiosXhr('GET', url, data);
    }

    function axiosXhr(method, url, data)
    {
        if (typeof data === 'object') {
            var formData = new FormData();
            for (var obj in data) {
                formData.append(obj, data[obj]);
            }
            data = formData;
        }
        return axios({
            method: method,
            url: url,
            data: data,
            config: { headers: {'Content-Type': 'multipart/form-data' }}
        });
    }

    function doEvent(data, callback) {
        setTimeout(function () {
            var vConsole = $("body").find("#melis-marketplace-event-do-response");

            var vConsoleText = vConsole.html();
            var lastResponseLen = false;

            $.ajax(
                {
                    type: 'POST',
                    url: '/melis/MelisMarketPlace/MelisMarketPlace/melisMarketPlaceProductDo',
                    data: data,
                    dataType: "html",
                    xhrFields: {
                        onprogress: function (e) {

                            var vConsole = $("body").find("#melis-marketplace-event-do-response");
                            vConsole.html("");
                            var vConsoleText = vConsole.html();

                            var curResponse, response = e.currentTarget.response;
                            if (lastResponseLen === false) {
                                curResponse = response;
                                lastResponseLen = response.length;
                            }
                            else {
                                curResponse = response.substring(lastResponseLen);
                                lastResponseLen = response.length;
                            }
                            vConsoleText += curResponse + "\n<br/>";
                            if (typeof vConsoleText !== "undefined") {

                                vConsole.html(vConsoleText);

                                // always scroll to bottom
                                vConsole.animate({
                                    scrollTop: vConsole.prop("scrollHeight")
                                }, 1115);
                            }

                        }
                    },
                    beforeSend: function () {
                        // do additional task here
                    },
                    success: function (data) {
                        vConsoleText = "" + vConsole.html();
                        vConsole.html(vConsoleText + '<span style="color:#02de02"><i class="fa fa-info-circle"></i> ' + translations.tr_melis_market_place_exec_do_done + '<br/>');
                        vConsole.animate({
                            scrollTop: vConsole.prop("scrollHeight")
                        }, 1115);
                        $("#melis-marketplace-product-modal-hide").removeAttr("disabled");
                        $("#melis-marketplace-product-modal-hide").removeClass("disabled");
                        $("body").find("p#melis-marketplace-console-loading").remove();
                        if (callback !== undefined || callback !== null) {
                            if (callback) {
                                callback();
                            }
                        }
                    }
                }).error(function (e) {
                var vConsole = $("body").find("#melis-marketplace-event-do-response");
                vConsole.html("An error has occured, please try again");
                $("#melis-marketplace-product-modal-hide").removeAttr("disabled");
                $("#melis-marketplace-product-modal-hide").removeClass("disabled");
            });

        }, 800);
    }


    $("body").on("click", ".melis-market-place-pagination", function () {
        var divOverlay = '<div class="melis-overlay"></div>';
        $("#melis-market-place-package-list").append(divOverlay);
        var page = $(this).data("goto-page");
        var groupId = getActiveGroupIdFilter();
        fetchPackages(page, null, null, null, null, groupId);
    });

    $("body").on("keypress", "input#melis_market_place_search_input", function (e) {
        if (e.which === 13) {
            $("body").find("button#btnMarketPlaceSearch").trigger("click");
        }
    });

    $("body").on("click", "button#btnMarketPlaceSearch", function () {
        var divOverlay = '<div class="melis-overlay"></div>';
        $(".product-list-view").append(divOverlay);
        var search = $("body").find("input#melis_market_place_search_input").val();
        var groupId = getActiveGroupIdFilter();
        fetchPackages(null, search, null, null, null, groupId);

    });

    $("body").on("submit", "form#melis_market_place_search_form", function (e) {
        e.preventDefault();
    });

    $("body").on("click", ".melis-market-place-view-details", function () {
        var packageId = $(this).data().packageid;
        var packageTitle = $(this).data().packagetitle;
        melisHelper.disableAllTabs();
        melisHelper.tabOpen(packageTitle, 'fa-shopping-cart', packageId + '_id_melis_market_place_tool_package_display', 'melis_market_place_tool_package_display', {packageId: packageId}, "id_melis_market_place_tool_display", function () {

        });
        melisHelper.enableAllTabs();

    });


    function plus() {
        var qtyBox = $(this).closest(".product-quantity__box").find("#productQuantity");
        var qtycount = parseInt(qtyBox.val());
        if (qtycount !== qtycount) {
            qtyBox.val(1);
        } else {
            qtycount++;
            qtyBox.val(qtycount);
        }
    }

    function minus() {

        var qtyBox = $(this).closest(".product-quantity__box").find("#productQuantity");
        var qtycount = parseInt(qtyBox.val());

        if (qtycount > 1) {
            qtycount--;
            qtyBox.val(qtycount);
        }
    }

    $("body").on("click", "#btnMinus", minus);
    $("body").on("click", "#btnPlus", plus);

    $("body").on("hide.bs.modal", "#id_melis_market_place_tool_package_modal_content_container, #id_melis_market_place_module_setup_form_content_ajax_container", function (e) {
        if (preventModalClose === true) {
            e.preventDefault();
        }
    });


    $("body").on("click", "#melis-marketplace-product-modal-hide", function () {
        preventModalClose = false;
        $("#id_melis_market_place_tool_package_modal_content_container").modal("hide");
        preventModalClose = true;
    });

    function updateCmdText(text) {
        var vConsole = $("body").find("#melis-marketplace-event-do-response");
        var vConsoleText = "" + vConsole.html();

        vConsole.html(vConsoleText + '<br/>' + text);
        vConsole.animate({
            scrollTop: vConsole.prop("scrollHeight")
        }, 1115);
    }

    function addLazyCmdText(id, content) {
        updateCmdText('<br/><span id="' + id + '"><i class="fa fa-spinner fa-spin"></i></span> ' + content + '<br/>');
    }

    function doneLazyCmdText(id, content) {
        $("#" + id).html('<i class="fa fa-info-circle"></i>');
    }


});


function initSlick() {
    $("#" + activeTabId + ' .slider-single').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        asNavFor: '.slider-nav',
        adaptiveHeight: true,
    });
    $("#" + activeTabId + ' .slider-nav').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        asNavFor: '.slider-single',
        dots: false,
        centerMode: false,
        focusOnSelect: true,
        arrows: true,
        infinite: false,

        responsive: [{
            breakpoint: 1400,
            settings: {
                slidesToShow: 6,
                slidesToScroll: 6,
            }
        }, {
            breakpoint: 992,
            settings: {
                slidesToShow: 4,
                slidesToScroll: 4,
            }
        }, {
            breakpoint: 767,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2,
            }
        }]
    });
}

/*Dashboard slider*/
$(document).ready(function () {
    function initDashboardSlider() {
        $(".slider-dashboard-downloaded-packages").slick({

            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 2000,
            arrows: true,
            adaptiveHeight: true,
            dots: true
        });

    }

    //Initialize dashboard slider
    initDashboardSlider();

    //Refresh button in dashboard slider
    $("body").on("click", ".dashboard-downloaded-packages", function () {

        var melisKey = "market_place_most_downloaded_modules";
        var zoneId = "id_market_place_most_downloaded_modules";

        //Zone Reload
        melisHelper.zoneReload(zoneId, melisKey, {}, function () {
            initDashboardSlider();
        });

    });

    //link to market-place
    $("body").on("click", "#link-to-marketplace", function () {
        // tabOpen(title, icon, zoneId, melisKey, parameters, navTabsGroup, callback){

        melisHelper.tabOpen(translations.tr_market_place, "fa-shopping-cart", "id_melis_market_place_tool_display", "melis_market_place_tool_display", {}, null, null);
    });


    /*
   * This is for filtering button
   */
    $("body").on("click", ".market-place-btn-filter-group .btn", function () {

        var flag = 0;
        //put overlay for loading
        var divOverlay = '<div class="melis-overlay"></div>';
        $(".product-list-view").append(divOverlay);

        var isActive = $(this).hasClass("active");


        //get ActiveButtons
        $(this).toggleClass("active");

        var data = getActiveGroupIdFilter();
        var search = $("body").find("input#melis_market_place_search_input").val();

        fetchPackages(null, search, null, null, null, data);


    });

    /*
     * For outdated melis modules
     * that needs to be updated
     */
    $("body").on("click", "#outdated-module-link", function () {
        var packageId = $(this).data().packageid;
        var packageTitle = $(this).data().packagetitle;

        melisHelper.disableAllTabs();
        melisHelper.tabOpen(packageTitle, 'fa-shopping-cart', packageId + '_id_melis_market_place_tool_package_display', 'melis_market_place_tool_package_display', {packageId: packageId}, "id_melis_market_place_tool_display", function () {

        });
        melisHelper.enableAllTabs();

    });

});
