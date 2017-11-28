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

    $("body").on("click", "button.melis-marketplace-product-action", function() {
        var action  = $(this).data().action;
        var package = $(this).data().package;
        var module  = $(this).data().module;

        var zoneId   = "id_melis_market_place_tool_package_modal_content";
        var melisKey = "melis_market_place_tool_package_modal_content";
        var modalUrl = "/melis/MelisMarketPlace/MelisMarketPlace/toolProductModalContainer";

        var data     = {action : action, package : package, module : module};

        melisCoreTool.pending("button");
        melisHelper.createModal(zoneId, melisKey, false, data,  modalUrl, function() {

            melisCoreTool.done("button");
            setTimeout(function() {
                var vConsole = $("body").find("#melis-marketplace-event-do-response");
                vConsole.html("");

                var vConsoleText    = vConsole.html();
                var lastResponseLen = false;

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
                    }
                });

            }, 800);

        });
        /**
         * @todo add a confirm dialog when updating/removing
         */


    });



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
            melisHelper.enableAllTabs();
		});


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