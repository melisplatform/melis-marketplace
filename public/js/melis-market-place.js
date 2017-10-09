window.fetchPackages = function(page, search, orderBy, order, itemPerPage) {

	page   			= page || 1;
	search 			= search || $("body").find("input#melis_market_place_search_input").val();
	orderBy  		= orderBy || 'mp_title';

	var order       = order || 'asc';
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


	$("body").on("click", ".melis-market-place-pagination", function() {
		var page = $(this).data("goto-page");
        fetchPackages(page);
	});

	$("body").on("keypress", "input#melis_market_place_search_input", function(e) {
		if(e.which === 13) {
            $("body").find("button#btnMarketPlaceSearch").trigger("click");
		}
	});

	$("body").on("click", "button#btnMarketPlaceSearch", function() {

        var search = $("body").find("input#melis_market_place_search_input").val();
        fetchPackages(null, search);

	});

	$("body").on("submit", "form#melis_market_place_search_form", function(e) {
		e.preventDefault();
	});

	$("body").on("click", "a#melis-market-place-view-details", function() {
		var packageId    = $(this).data().packageid;
		var packageTitle = $(this).data().packagetitle;

        melisHelper.tabOpen(packageTitle + " | " + translations.tr_market_place, 'fa-shopping-cart', packageId+'_id_melis_market_place_tool_package_display', 'melis_market_place_tool_package_display', {packageId : packageId});


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
	/*	 
	$('.slider-single').slick({
	 	slidesToShow: 1,
	 	slidesToScroll: 1,
	 	arrows: true,
	 	fade: true,
	 	adaptiveHeight: true,
	 	infinite: false,
		useTransform: true,
	 	speed: 400,
	 	cssEase: 'cubic-bezier(0.77, 0, 0.18, 1)',
	 });

	 $('.slider-nav').on('init', function(event, slick) {
	 		$('.slider-nav .slick-slide.slick-current').addClass('is-active');
	 	})
	 	.slick({
	 		slidesToShow: 4,
	 		slidesToScroll: 1,
	 		dots: false,
	 		focusOnSelect: false,
	 		infinite: false,
	 		responsive: [{
	 			breakpoint: 1024,
	 			settings: {
	 				slidesToShow: 4,
	 				slidesToScroll: 4,
	 			}
	 		}, {
	 			breakpoint: 640,
	 			settings: {
	 				slidesToShow: 2,
	 				slidesToScroll: 2,
				}
	 		}, {
	 			breakpoint: 420,
	 			settings: {
	 				slidesToShow: 2,
	 				slidesToScroll: 2,
			}
	 		}]
	 	});

	 $('.slider-single').on('afterChange', function(event, slick, currentSlide) {
	 	$('.slider-nav').slick('slickGoTo', currentSlide);
	 	var currrentNavSlideElem = '.slider-nav .slick-slide[data-slick-index="' + currentSlide + '"]';
	 	$('.slider-nav .slick-slide.is-active').removeClass('is-active');
	 	$(currrentNavSlideElem).addClass('is-active');
	 });

	 $('.slider-nav').on('click', '.slick-slide', function(event) {
	 	event.preventDefault();
	 	var goToSingleSlide = $(this).data('slick-index');

	 	$('.slider-single').slick('slickGoTo', goToSingleSlide);
	 });
	 */

	 $('.slider-single').slick({
	  slidesToShow: 1,
	  slidesToScroll: 1,
	  arrows: true,
	  fade: true,
	  asNavFor: '.slider-nav'
	});
	$('.slider-nav').slick({
	  slidesToShow: 4,
	  slidesToScroll: 1,
	  asNavFor: '.slider-single',
	  dots: false,
	  centerMode: true,
	  focusOnSelect: true,
	  arrows: false,

	});
}