window.fetchPackages = function(page, search, orderBy, order, itemPerPage) {

	page   			= page || 1;
	search 			= search || $("body").find("input#melis_market_place_search_input").val();
	orderBy  		= orderBy || 'mp_total_downloads';

	var order       = order || 'desc';
    var itemPerPage = itemPerPage || 8;

	$.ajax(
	{
		type: 'POST',
		url: "/melis/MelisMarketPlace/MelisMarketPlace/package-list?page="+page+"&search="+search+"&orderBy="+orderBy,
		data: {page: page, search : search, orderBy : orderBy, order : order, itemPerPage : itemPerPage},
		dataType: "html",
		success: function(data) {
			$("body").find("div#melis-market-place-package-list").html(data);
		},
	});


}


$(function() {


    var preventModalClose = true;

    $("body").on("click", "button.melis-marketplace-product-action", function() {
        var action  = $(this).data().action;
        var package = $(this).data().package;
        var module  = $(this).data().module;

        var zoneId   = "id_melis_market_place_tool_package_modal_content";
        var melisKey = "melis_market_place_tool_package_modal_content";
        var modalUrl = "/melis/MelisMarketPlace/MelisMarketPlace/toolProductModalContainer";

        var objData  = {action : action, package : package, module : module};

        melisCoreTool.pending("button");
        if(action === "remove") {

            var tables = [];
            doAjax("POST", "http://www.melis-platform.dev/melis/MelisMarketPlace/MelisMarketPlace/getModuleTables", {module: module}, function(data) {
                tables = data.tables;
            });

            doAjax("POST", "/melis/MelisCore/Modules/getDependents", {module: module}, function(data) {
                console.log(tables);
                var modules    = "<br/><br/><div class='container'><div class='row'><div class='col-lg-12'><ul>%s</ul></div></div></div>";
                var moduleList = '';

                $.each(data.modules, function(i, v) {
                    moduleList += "<li>"+v+"</li>";

                });

                modules = modules.replace("%s", moduleList);

                if(data.success) {
                    melisCoreTool.confirm(
                        translations.tr_meliscore_common_yes,
                        translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,
                        translations.tr_meliscore_general_proceed,
                        translations.melis_market_place_tool_package_remove_confirm_on_dependencies.replace("%s", module)+modules,
                        function() {
                            melisHelper.createModal(zoneId, melisKey, false, objData,  modalUrl, function() {

                                melisCoreTool.done("button");
                                doEvent(objData, function () {
                                    postDeleteEvent(module, tables);
                                });
                            });
                        }
                    );
                }

                if(moduleList === "") {
                    melisCoreTool.confirm(
                        translations.tr_meliscore_common_yes,
                        translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,
                        translations.tr_meliscore_general_confirm,
                        translations.melis_market_place_tool_package_remove_confirm.replace("%s", module),
                        function() {
                            melisHelper.createModal(zoneId, melisKey, true, objData,  modalUrl, function() {

                                melisCoreTool.done("button");
                                doEvent(objData, function() {
                                    postDeleteEvent(module, tables);
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
            melisHelper.createModal(zoneId, melisKey, false, objData,  modalUrl, function() {
                console.log("went here");
                melisCoreTool.done("button");
                doEvent(objData);

            });
        }

    });

    $("body").on("click", "button.melis-marketplace-modal-reload", function() {
        melisCoreTool.processing();
        location.reload(true);
    });

    function postDeleteEvent(module, tables)
    {
        var vConsole     = $("body").find("#melis-marketplace-event-do-response");
        var vConsoleText = vConsole.html();

        // check if the module still exists
        doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/isModuleExists", {module: module}, function(module) {
            console.log(module);
            if(!module.isExist) {
                vConsole.html(vConsoleText + '<br/><span style="color:#02de02">' + translations.melis_market_place_tool_package_remove_ok.replace("%s", module.module) + '</span>');
                // export tables
                vConsole.html(vConsoleText + '<br/><span style="color:#fbff0f">' + translations.melis_market_place_tool_package_remove_table_dump.replace("%s", module.module) + '</span>');
                $.ajax({
                    type: 'POST',
                    url:'/melis/MelisMarketPlace/MelisMarketPlace/exportTables',
                    data: {module: module.module},
                    success: function(data, textStatus, request){
                        // if data is not empty
                        if(data) {
                            var fileName = request.getResponseHeader("fileName");
                            var blob     = new Blob([data], {type: "application/sql;charset=utf-8"});
                            saveAs(blob, fileName);
                        }
                    }
                });

                vConsoleText = vConsole.html();

                $("button.melis-marketplace-modal-reload").show();


            }
            else {
                vConsole.html(vConsoleText + '<br/><span style="color:#ff190d">' + translations.melis_market_place_tool_package_remove_ko.replace("%s", module.module) + '</span>');
            }
        });

    }

    function doAjax(type, url, data, callbackOnSuccess, callbackOnFail)
    {
        $.ajax({
            type        : type,
            url         : url,
            data		: data,
            dataType    : 'json',
            encode		: true,
        }).success(function(data){
            if ( callbackOnSuccess !== undefined || callbackOnSuccess !== null) {
                if (callbackOnSuccess) {
                    callbackOnSuccess(data);
                }
            }
        }).error(function(e) {
            if ( callbackOnFail !== undefined || callbackOnFail !== null) {
                if (callbackOnFail) {
                    callbackOnFail(data);
                }
            }
        });
    }

    function doEvent(data, callback)
    {
        setTimeout(function() {
            var vConsole = $("body").find("#melis-marketplace-event-do-response");
            vConsole.html("");

            var vConsoleText    = vConsole.html();
            var lastResponseLen = false;
            vConsole.html('<span id="melis-marketplace-console-loading"><i class="fa fa-spinner fa-spin"></i> Initializing...<br/></span>');
            $.ajax(
            {
                type: 'POST',
                url: '/melis/MelisMarketPlace/MelisMarketPlace/melisMarketPlaceProductDo',
                data: data,
                dataType: "html",
                xhrFields: {
                    onprogress: function(e) {

                        var curResponse, response = e.currentTarget.response;
                        if(lastResponseLen === false) {
                            curResponse = response;
                            lastResponseLen = response.length;
                        }
                        else {
                            curResponse = response.substring(lastResponseLen);
                            lastResponseLen = response.length;
                        }
                        vConsoleText += curResponse + "\n<br/>";
                        vConsole.html(vConsoleText);

                        // always scroll to bottom
                        vConsole.animate({
                            scrollTop: vConsole[0].scrollHeight
                        }, 1115);
                    }
                },
                beforeSend: function () {
                    // do additional task here
                },
                success: function(data) {
                    vConsoleText = vConsole.html();
                    vConsole.html(vConsoleText + '<span style="color:#02de02"><i class="fa fa-info-circle"></i> Done!</div>');
                    vConsole.animate({
                        scrollTop: vConsole[0].scrollHeight
                    }, 1115);
                    $("#melis-marketplace-product-modal-hide").removeAttr("disabled");
                    $("#melis-marketplace-product-modal-hide").removeClass("disabled");
                    $("body").find("span#melis-marketplace-console-loading").remove();
                    if ( callback !== undefined || callback !== null) {
                        if (callback) {
                            callback();
                        }
                    }
                }
            });

        }, 800);
    }



	$("body").on("click", ".melis-market-place-pagination", function() {
		var divOverlay = '<div class="melis-overlay"></div>';
		$("#melis-market-place-package-list").append(divOverlay);
		var page = $(this).data("goto-page");
        fetchPackages(page);
	});

	$("body").on("keypress", "input#melis_market_place_search_input", function(e) {
		if(e.which === 13) {
            $("body").find("button#btnMarketPlaceSearch").trigger("click");
		}
	});

	$("body").on("click", "button#btnMarketPlaceSearch", function() {
        var divOverlay = '<div class="melis-overlay"></div>';
        $("#melis-market-place-package-list").append(divOverlay);
        var search = $("body").find("input#melis_market_place_search_input").val();
        fetchPackages(null, search);

	});

	$("body").on("submit", "form#melis_market_place_search_form", function(e) {
		e.preventDefault();
	});

	$("body").on("click", ".melis-market-place-view-details", function() {
		var packageId    = $(this).data().packageid;
		var packageTitle = $(this).data().packagetitle;
		melisHelper.disableAllTabs();
        melisHelper.tabOpen(packageTitle, 'fa-shopping-cart', packageId+'_id_melis_market_place_tool_package_display', 'melis_market_place_tool_package_display', {packageId : packageId}, "id_melis_market_place_tool_display", function() {
        	$(document).ready(function() {
                $("#"+activeTabId + ' .slider-single').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: true,
                    fade: true,
                    asNavFor: '.slider-nav',
                    adaptiveHeight: true,
                });
                $("#"+activeTabId + ' .slider-nav').slick({
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
			});
		});
        melisHelper.enableAllTabs();

	});

    function plus(){
    	var qtyBox = $(this).closest(".product-quantity__box").find("#productQuantity");
    	var qtycount = parseInt(qtyBox.val()); 
    	if(qtycount !== qtycount) {
    		qtyBox.val(1);
    	} else {
	        qtycount++;
	        qtyBox.val(qtycount);
    	}
    }

    function minus(){

		var qtyBox = $(this).closest(".product-quantity__box").find("#productQuantity");
		var qtycount = parseInt(qtyBox.val());

		if (qtycount > 1) {
			qtycount--;
			qtyBox.val(qtycount);
		}  
    }

    $("body").on("click", "#btnMinus", minus);
    $("body").on("click", "#btnPlus", plus);


    $("body").on("hide.bs.modal", "#id_melis_market_place_tool_package_modal_content_container", function(e) {
        if(preventModalClose === true) {
            e.preventDefault();
        }
    });


    $("body").on("click", "#melis-marketplace-product-modal-hide", function() {
        preventModalClose = false;
        $("#id_melis_market_place_tool_package_modal_content_container").modal("hide");
        preventModalClose = true;
    });


});


function initSlick() {
 $("#"+activeTabId + ' .slider-single').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: true,
    fade: true,
    asNavFor: '.slider-nav',
     adaptiveHeight: true,
});
$("#"+activeTabId + ' .slider-nav').slick({
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