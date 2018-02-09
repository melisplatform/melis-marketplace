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

function initSlick(tab) {
    // Big Slider
    $('#'+tab + ' .slider-single').not('.slick-initialized').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        adaptiveHeight: true,
    });
    // Navigation Slider
    $('#'+tab + ' .slider-nav')
        .not('.slick-initialized')
        .on('init', function(event, slick) {
            $('#'+tab + ' .slider-nav .slick-slide.slick-current').addClass('is-active');
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
    $('#'+tab + ' .slider-single').on('afterChange', function(event, slick, currentSlide) {
        $('#'+tab + ' .slider-nav').slick('slickGoTo', currentSlide);
        var currrentNavSlideElem = '#'+tab + ' .slider-nav .slick-slide[data-slick-index="' + currentSlide + '"]';
        $('#'+tab + ' .slider-nav .slick-slide.is-active').removeClass('is-active');
        $(currrentNavSlideElem).addClass('is-active');
    });

    $('#'+tab + ' .slider-nav').on('click', '.slick-slide', function(event) {
        event.preventDefault();
        var goToSingleSlide = $(this).data('slick-index');

        $('#'+tab + ' .slider-single').slick('slickGoTo', goToSingleSlide);
    });
}



$(function() {

    // Tab Click
    $("body").on('shown.bs.tab', "#melis-id-nav-bar-tabs [data-tool-meliskey='melis_market_place_tool_package_display'] a[data-toggle='tab']", function (e) {
        initSlick(activeTabId);
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




