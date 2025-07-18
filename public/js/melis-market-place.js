window.fetchPackages = function (page, search, orderBy, order, itemPerPage, group, paginationClickOwner) {
    paginationClickOwner = (typeof paginationClickOwner == undefined) ? null : paginationClickOwner;
    //get bundle filter value
    var bundle = getBundle();

    page = page || 1;
    search = search || $("body").find("input#melis_market_place_search_input").val();
    orderBy = orderBy || 'mp_total_downloads';
    order = order || 'desc';
    itemPerPage = itemPerPage || 9;

    if (!group) {
        group = ["1", "2", "3", "4", '5'];
    }
    var modUrl = "/melis/MelisMarketPlace/MelisMarketPlace/module-list?page=" + page + "&search=" + search + "&orderBy=" + orderBy + "&group=" + group;
    var bundleUrl = "/melis/MelisMarketPlace/MelisMarketPlace/bundle-list?page=" + page + "&search=" + search + "&orderBy=" + orderBy + "&group=" + group + "&bundle=" + bundle;
    var data = {page: page, search: search, orderBy: orderBy, order: order, itemPerPage: itemPerPage, group: group, bundle: bundle};

    //on pagination click, we execute only the owner of the pagination
    if(paginationClickOwner == 'bundle' || bundle == 1) {
        getBundles(bundleUrl, data, true);
    }else if(paginationClickOwner == 'module'){
        getModules(modUrl, data, true);
    }else {
        $.when(getBundles(bundleUrl, data, false), getModules(modUrl, data, false)).done(function(){
            $(".package-list").find("div.melis-overlay").remove();
        });
    }

    $(".market-place-btn-filter-group button").prop("disabled", true);
    $("#btnMarketPlaceSearch").prop("disabled", true);
};

function getModules(url, data, removeOverlay)
{
    return $.ajax({
        type: 'POST',
        url: url,
        data: data,
        dataType: "html"
    }).done(function(data){
        $("body").find("div#melis-market-place-module-list").html(data);
        //$(".market-place-btn-filter-group button").removeAttr("disabled", "disabled");
        $(".market-place-btn-filter-group button").prop("disabled", false);
        $("#btnMarketPlaceSearch").prop("disabled", false);
        //update title of the package to add the selected group name
        changePackageHeaderTitle();
        if(removeOverlay)
            $(".package-list").find("div.melis-overlay").remove();
    });
}

function getBundles(url, data, removeOverlay)
{
    return $.ajax({
        type: 'POST',
        url: url,
        data: data,
        dataType: "html"
    }).done(function(data){
        $("body").find("div#melis-market-place-bundle-list").html(data);
        $(".market-place-btn-filter-group button").prop("disabled", false);
        $("#btnMarketPlaceSearch").prop("disabled", false);
        //update title of the package to add the selected group name
        changePackageHeaderTitle();
        if(removeOverlay)
            $(".package-list").find("div.melis-overlay").remove();

        //remove modules data
        if(getBundle() == 1) {
            $("#melis-market-place-module-list").empty();
        }
    });
}

function changePackageHeaderTitle()
{
    var groupId = $(".market-place-btn-filter-group").find('.active').not('.bundles');
    var headerBundleTitle = $(".package-bundle-header-title").find("h3");
    var headerModuleTitle = $(".package-module-header-title").find("h3");
    var modulesName = "";
    //get the active buttons Id
    for (var ctr = 0; ctr < groupId.length; ctr++) {
        modulesName += (ctr == 0) ? "" : ", ";
        modulesName += $(groupId[ctr]).attr("data-groupname");
    }

    headerBundleTitle.text("Bundles "+modulesName);
    headerModuleTitle.text("Modules "+modulesName);
}

function getActiveGroupIdFilter() {
    var groupId = $(".market-place-btn-filter-group").find('.active').not('.bundles');
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

function getBundle(){
    var bundleButton = $(".market-place-btn-filter-group").find("button.bundles");
    if(bundleButton.hasClass('active'))
        return 1;

    return 0;
}

function initSlick(tab) {
    // Big Slider
    $('#' + tab + ' .slider-single').not('.slick-initialized').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        adaptiveHeight: true
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
                    slidesToScroll: 6
                }
            }, {
                breakpoint: 992,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 4
                }
            }, {
                breakpoint: 767,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
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
    var preventModalClose = true,
        $body = $("body");

    // Tab Click
    $body.on('shown.bs.tab', "#melis-id-nav-bar-tabs [data-tool-meliskey='melis_market_place_tool_package_display'] a[data-bs-toggle='tab']", function (e) {
        initSlick(activeTabId);
    });

    $body.on('click', '#melis-marketplace-setup-modal-submit', function () {
        var form = $('#melis-marketplace-setup-modal-submit').parents().find('form');

        if (form.length) {
            var modal = $('#id_melis_market_place_module_setup_form_content');
            var data = form.serializeArray();
            var action = modal.data().action;
            var module = modal.data().module;

            data.push({name: 'module', value: module});
            data.push({name: 'action', value: action});
            melisCoreTool.pending("#melis-marketplace-setup-modal-submit");

            doAjax('POST', '/melis/MelisMarketPlace/MelisMarketPlace/validateSetupForm', $.param(data), function (response) {
                // display errors if it has
                if (response.result.errors != null || typeof response.result.errors !== 'undefined') {
                    if (response.result.success) {
                        // if everything went well, call the submitAction to process the data
                        doAjax('POST', '/melis/MelisMarketPlace/MelisMarketPlace/submitSetupForm', $.param(data), function (response) {
                            if (response.success) {
                                melisHelper.melisOkNotification(response.module, response.message);
                                // unplug module
                                doAjax('POST', '/melis/MelisMarketPlace/MelisMarketPlace/unplugModule', {module: module}, function (response) {
                                    if (response.success === true) {
                                        preventModalClose = false;
                                        // $('#id_melis_market_place_module_setup_form_content_ajax_container').modal('hide');
                                        melisCoreTool.hideModal("id_melis_market_place_module_setup_form_content_ajax_container");

                                        // inform the user that everything is good
                                        addSuccessCmdText(translations.tr_melis_marketplace_setup_config_ok);

                                        // ask the user if they want to activate the module, this will only happen if the action is "download"
                                        if (action === 'download' || action === 'require' && response.moduleSite === false) {
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

    $body.on("click", "button.melis-marketplace-product-action", function () {
        var action = $(this).data().action;
        var pkg = $(this).data().package;
        var module = $(this).data().module;

        var zoneId = "id_melis_market_place_tool_package_modal_content";
        var melisKey = "melis_market_place_tool_package_modal_content";
        var modalUrl = "/melis/MelisMarketPlace/MelisMarketPlace/toolProductModalContainer";

        var objData = {action: action, package: pkg, module: module};

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
                    melisCoreTool.closeDialog(
                        translations.tr_meliscore_delete_module_header,
                        translations.melis_market_place_tool_package_remove_no_no_msg_1.replace("%s", module) +
                        modules + "<br/>" + translations.melis_market_place_tool_package_remove_no_no_msg_2
                    );
                }

                if (moduleList === "") {
                    melisCoreTool.confirm(
                        translations.tr_meliscore_common_yes,
                        translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,
                        translations.tr_meliscore_delete_module_header,
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
                                                execDbDeploy(module, response, action, resolve, reject);
                                            }else{
                                                updateCmdText('<span style="color: #ff190d;">' + translations.tr_meliscore_error_message + "</span>");
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
                            axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/plugModule', {module: module})
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

                                    // Check for composer scripts to be executed
                                    addLazyCmdText('span_c_scripts_setup', translations.tr_melis_core_composer_scrpts_executing);

                                    setTimeout(function(){
                                        $.get("/melis/MelisMarketPlace/MelisMarketPlace/executeComposerScripts").done(function(res){
                                            updateCmdText(res);
                                            clearLazyCmdText('span_c_scripts_setup', translations.tr_melis_core_composer_scrpts_executed);

                                            // check if the module has a form setup
                                            var hasSetupForm = false;
                                            var form = null;

                                            addLazyCmdText('span_get_setup', translations.tr_melis_marketplace_check_addtl_setup);

                                            setTimeout(function(){
                                                axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/getSetupModuleForm', {
                                                    action: payload.action,
                                                    module: payload.module
                                                })
                                                .then(function (response) {

                                                    clearLazyCmdText('span_get_setup', translations.tr_melis_marketplace_check_addtl_setup_ok);

                                                    if (response.data.form !== '' && response.data.form !== null) {
                                                        hasSetupForm = true;
                                                    }

                                                    return Object.assign(payload, {hasSetupForm: hasSetupForm});
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
                                                                skip = false;
                                                                // open a new modal with the setup form
                                                                melisHelper.createModal('id_melis_market_place_module_setup_form_content_ajax',
                                                                    'melis_market_place_module_setup_form_content', false, payload, modalUrl, function () {
                                                                        melisCoreTool.done("button");
                                                                    });
                                                            },
                                                            function () {
                                                                modalActivateReloadBtns(module, payload);
                                                            }
                                                        );
                                                    }else{
                                                        modalActivateReloadBtns(module, payload);
                                                    }

                                                    return Object.assign(payload, {skip: skip});
                                                })
                                                .catch(function (error) {
                                                    updateCmdText('<span style="color: #ff190d;">' + translations.tr_melis_marketplace_check_addtl_setup_ko + "</span>");
                                                });
                                            }, 5000);
                                        });
                                    }, 5000);
                                });

                            return payload;

                        })
                        .catch(function (err) {
                            console.log(err);
                        });
                }
            );

            melisCoreTool.done("button");
        }

    });

    function execDbDeploy(module, response, action, resolve, reject){
        axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/execDbDeploy', {module: response.data.module})
            .then(function (res) {

                if (res.data.success === -1) {
                    execDbDeploy(module, response, action, resolve, reject);
                }else{
                    if (res.data.success === true) {
                        // replace this text with "Checking additional setup..."
                        updateCmdText(translations.tr_melis_market_place_task_done);
                        // stored to an object, since native Promise object doesn't pass multiple args
                        var payload = Object.assign({action: action}, {data: response.data}, {module: module});
                        resolve(payload);
                    } else {
                        reject(response);
                    }
                }
            });
    }

    function modalActivateReloadBtns(module, payload) {
        // ask the user if they want to activate the module, this will only happen if the action is "download"
        updateCmdText(translations.tr_melis_marketplace_check_addtl_setup_skipped);

        // make sure to unplug module
        axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/unplugModule', {module : module});
        if (payload.action === 'require' || payload.form === '' || payload.form === null && payload.moduleSite === false) {
            $("button.melis-marketplace-modal-activate-module").removeClass("hidden");
        }

        $("button.melis-marketplace-modal-reload").removeClass("hidden");
    }

    $body.on("click", "button.melis-marketplace-modal-activate-module", function () {
        var module = $(this).data().module;
        doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/activateModule", {module: module}, function () {
            $.get("/melis", function(){
                setTimeout(function(){
                    $("button.melis-marketplace-modal-reload").trigger("click");
                }, 1000);
            });
        });
    });


    $body.on("click", "button.melis-marketplace-modal-reload", function () {
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
                        data: {module: module.module, tables: tables, files: files}
                    })
                    .done(function(data, textStatus, request) {
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
            encode: true
            // processData: false
        }).done(function (data) {
            try {
                if (callbackOnSuccess !== undefined || callbackOnSuccess !== null) {
                    if (callbackOnSuccess) {
                        callbackOnSuccess(data);
                    }
                }
            } catch (err) {
                addErrorCmdText('<i class="fa fa-close"></i> ' + err.toString());
                melisHelper.melisKoNotification(err.toString());
                console.error(err);
            }
        }).fail(function (e) {
            if (callbackOnFail !== undefined || callbackOnFail !== null) {
                if (callbackOnFail) {
                    callbackOnFail(data);
                }
            }
        });
    }

    function axiosPost(url, data) {
        return axiosXhr('POST', url, data);
    }

    function axiosGet(url, data) {
        return axiosXhr('GET', url, data);
    }

    function axiosXhr(method, url, data) {
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
            config: {headers: {'Content-Type': 'multipart/form-data'}}
        });
    }

    function doEvent(data, callback) {
        setTimeout(function () {
            var vConsole = $("body").find("#melis-marketplace-event-do-response");

            var vConsoleText = vConsole.html();
            var lastResponseLen = false;

            $.ajax({
                type: 'POST',
                url: '/melis/MelisMarketPlace/MelisMarketPlace/melisMarketPlaceProductDo',
                data: data,
                dataType: "html",
                xhrFields: {
                    onprogress: function (e) {

                        var vConsole = $("body").find("#melis-marketplace-event-do-response");

                        if (vConsole.html().includes("pre-task-action")) {
                            vConsole.html("");
                        }

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
                }
            })
            .done(function (data) {
                setTimeout(function () {
                    // Composer re-Dumpautoload
                    $.get("/melis/MelisMarketPlace/MelisMarketPlace/reDumpAutoload", function(){

                        vConsoleText = "" + vConsole.html();
                        vConsole.html(vConsoleText + '<span style="color:#02de02"><i class="fa fa-info-circle"></i> ' + translations.tr_melis_market_place_exec_do_done + '<br/>');
                        vConsole.animate({
                            scrollTop: vConsole.prop("scrollHeight")
                        }, 1115);
                        $("#melis-marketplace-product-modal-hide").prop("disabled", false);
                        $("#melis-marketplace-product-modal-hide").removeClass("disabled");
                        $("body").find("p#melis-marketplace-console-loading").remove();
                        if (callback !== undefined || callback !== null) {
                            if (callback) {
                                callback();
                            }
                        }
                    });
                }, 3000);
            })
            .fail(function (e) {
                var vConsole = $("body").find("#melis-marketplace-event-do-response");
                vConsole.html("An error has occured, please try again");
                $("#melis-marketplace-product-modal-hide").prop("disabled", false);
                $("#melis-marketplace-product-modal-hide").removeClass("disabled");
            });

        }, 801);
    }

    $body.on("click", ".melis-market-place-pagination", function () {
        var divOverlay = '<div class="melis-overlay"></div>';
        $(".package-list").append(divOverlay);
        var page = $(this).data("goto-page");
        var groupId = getActiveGroupIdFilter();
        var paginationOwner = 'module';
        if($(this).hasClass('bundle-pagination')) {
            paginationOwner = 'bundle';
        }

        fetchPackages(page, null, null, null, null, groupId, paginationOwner);
    });

    $body.on("keypress", "input#melis_market_place_search_input", function (e) {
        if (e.which === 13) {
            $body.find("button#btnMarketPlaceSearch").trigger("click");
        }
    });

    $body.on("click", "button#btnMarketPlaceSearch", function () {
        var divOverlay = '<div class="melis-overlay"></div>';
        $(".package-list").append(divOverlay);
        var search = $("body").find("input#melis_market_place_search_input").val();
        var groupId = getActiveGroupIdFilter();
        fetchPackages(null, search, null, null, null, groupId);

    });

    $body.on("submit", "form#melis_market_place_search_form", function (e) {
        e.preventDefault();
    });

    $body.on("click", ".melis-market-place-view-details", function () {
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

    $body.on("click", "#btnMinus", minus);
    $body.on("click", "#btnPlus", plus);

    $body.on("hide.bs.modal", "#id_melis_market_place_tool_package_modal_content_container, #id_melis_market_place_module_setup_form_content_ajax_container", function (e) {
        if (preventModalClose === true) {
            e.preventDefault();
        }
    });


    $body.on("click", "#melis-marketplace-product-modal-hide", function () {
        preventModalClose = false;

        // $("#id_melis_market_place_tool_package_modal_content_container").modal("hide");
        melisCoreTool.hideModal("id_melis_market_place_tool_package_modal_content_container");

        preventModalClose = true;
    });

    function updateCmdText(message) {
        var vConsole = $("body").find("#melis-marketplace-event-do-response");
        var vConsoleText = "" + vConsole.html();

        vConsole.html(vConsoleText + '<br/>' + message);
        vConsole.animate({
            scrollTop: vConsole.prop("scrollHeight")
        }, 1115);
    }

    function addSuccessCmdText(message)
    {
        updateCmdText('<span style="color: #02de02;">' + message + '</span>');
    }

    function addWarningCmdText(message)
    {
        updateCmdText('<span style="color: #fbff0f;">' + message + '</span>');
    }

    function addErrorCmdText(message)
    {
        updateCmdText('<span style="color: #ff190d;">' + message + '</span>');
    }

    function addLazyCmdText(id, message) {
        updateCmdText('<br/><span id="' + id + '"><i class="fa fa-spinner fa-spin"></i> ' + message + '</span> <br/>');
    }

    function clearLazyCmdText(id, message) {
        $("#" + id).html('<i class="fa fa-info-circle"></i> ' + message + '<br/>');
    }
});

function initSlick() {
    $("#" + activeTabId + ' .slider-single').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        asNavFor: '.slider-nav',
        adaptiveHeight: true
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
$(function() {
    var $body = $("body");

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
    $body.on("click", ".dashboard-downloaded-packages", function () {

        var melisKey = "market_place_most_downloaded_modules";
        var zoneId = "id_market_place_most_downloaded_modules";

        //Zone Reload
        melisHelper.zoneReload(zoneId, melisKey, {}, function () {
            initDashboardSlider();
        });

    });

    //link to market-place
    $body.on("click", "#link-to-marketplace", function () {
        // tabOpen(title, icon, zoneId, melisKey, parameters, navTabsGroup, callback){

        melisHelper.tabOpen(translations.tr_market_place, "fa-shopping-cart", "id_melis_market_place_tool_display", "melis_market_place_tool_display", {}, null, null);
    });


    /*
   * This is for filtering button
   */
    $body.on("click", ".market-place-btn-filter-group .btn", function () {

        var flag = 0;
        //put overlay for loading
        var divOverlay = '<div class="melis-overlay"></div>';
        $(".package-list").append(divOverlay);

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
    $body.on("click", "#outdated-module-link", function () {
        var packageId = $(this).data().packageid;
        var packageTitle = $(this).data().packagetitle;

        melisHelper.disableAllTabs();
        melisHelper.tabOpen(packageTitle, 'fa-shopping-cart', packageId + '_id_melis_market_place_tool_package_display', 'melis_market_place_tool_package_display', {packageId: packageId}, "id_melis_market_place_tool_display", function () {

        });
        melisHelper.enableAllTabs();
    });
});