<?php
namespace ProcessWire;

// DEMO: htmx RENDERS  MODAL
// render for products.php template file

$pages = wire('pages');
$sanitizer = wire('sanitizer');
/*
>>>>>>>>>>>>>>>>>>>>>>>>
DEMO NOTES
1. Demo 'buy now' action
2. Buy Now button click triggers a htmx ajax get request
3. htmx sends the value of the associated hidden input for the product being bought
4. At the same time, Alpine.js opens the modal and shows a 'spinner' + wait message
5. When ready, htmx populates the modal with server-rendered markup for the selected product
- includes: title, variants if any, price, etc
- @note: markup includes attributes for Alpine.js as well
- @note: the markup also includes an 'add to basket' button rendered from the server and set to trigger htmx
@NOTES:
- This is just one way of doing this
- Ideally, we should generate as much modal markup as possible before-hand instead of refetching things like quantity buttons from the server every time!
<<<<<<<<<<<<<<<<<<<<<<<
*/
/** @var Page $page */
function getBuyNowProduct($productID) {
	$pages = wire('pages');
	$fields = ['id', 'title', 'price', 'parent_id'];
	$selector = "(template=product,id={$productID})(template=product-variant,parent.id={$productID}),sort=sort,sort=title";
	$productAndItsVariants = $pages->findRaw($selector, $fields);
	// bd($productID, __METHOD__ . ': $productID at line #' . __LINE__);
	// bd($fields, __METHOD__ . ': $fields at line #' . __LINE__);
// bd($selector, __METHOD__ . ': $selector at line #' . __LINE__);
// bd($productAndItsVariants, __METHOD__ . ': $productAndItsVariants at line #' . __LINE__);
	// ----
	return $productAndItsVariants;
}

function getBuyNowProductVariants(int $productID, array $productAndItsVariants): array {
	$variantsForProduct = array_filter($productAndItsVariants, fn($item) => $item['id'] !== $productID);
	// ------
	return $variantsForProduct;
}

function getMarkupForProductVariants($variantsForProduct) {
	$store = '$store.HtmxAlpineTailwindDemosStore';

	$out =
		"<div id='htmx_alpine_tailwind_demos_buy_now_product_variants_wrapper'>" .
		"<span>Select an option</span>" .
		"<div class='mb-3'>";
	// loop each variant, building its markup
	foreach ($variantsForProduct as $variant) {
		$variantJSON = json_encode($variant, JSON_HEX_APOS);
		$out .= "<button class='btn btn-sm' @click='setCurrentBuyNowProductSelectedVariant({$variantJSON})' :class='checkIsCurrentVariantID({$variant['id']}) ?``:`btn-ghost`'>{$variant['title']}</button>";
	}
	$out .= "</div>" .
		// HIDDEN INPUT FOR CURRENT BUY NOW PRODUCT SELECTED VARIANT ID for HTMX USE
		// @note: we bind its value to Alpine.js store value 'current_buy_now_product_selected_variant_id'
		"<input name='htmx_alpine_tailwind_demos_buy_now_product_variant_id' class='htmx_alpine_tailwind_demos_buy_now' type='hidden' x-model='{$store}.current_buy_now_product_selected_variant_id'>" .
		// -----
		// end div#htmx_alpine_tailwind_demos_buy_now_product_variants_wrapper
		"</div>";
	return $out;
}


function handleAddItemToBasket() {
	// 2. MOCK RESPONSE FOR MODAL BUY NOW
	$out =
		// add to basket success confirm
		"<div class='alert alert-success shadow-lg mt-3'>
		<div>
			<svg xmlns='http://www.w3.org/2000/svg' class='stroke-current flex-shrink-0 h-6 w-6' fill='none' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>
			<span class='text-sm'>Product has been added to your basket successfully.</span>
			</div>
	</div>";
	return $out;
}

/**
 * Initial markup to show when modal is opened.
 *
 * For 'wait/loading'.
 * @return void
 */
function getModalMarkupForIsFetchingProduct(): string {
	// $store = '$store.HtmxAlpineTailwindDemosStore';
	// ++++++++++++++
	// @note: htmx will show/hide div#loading if making a request to fetch details of the buy now product
	// hence, no need for the alpine.js x-show
	// @see hx-indicator in  getMarkupForHtmxFetchBuyNowProduct() below as well as the class 'htmx-indicator' on div#loading
	// @see: https://htmx.org/attributes/hx-indicator/
	// ++++++++++++++
	$loadingImagePath = wire('config')->urls->templates . "demos/demo_htmx_renders_modal/assets/bars.svg";
	$out =
		"<div id='loading' class='htmx-indicator'>" .
		// "<p x-show='{$store}.is_fetching_product_for_modal'>" .
		"<span>Please wait... </span>" .
		// @note: htmx will show/hide this loading indicator per the progress of its request
		"<img class='inline-block' src='{$loadingImagePath}'/>" .
		// "</p>";
		"</div>";
	return $out;
}

function getModalMarkupForForFetchedProduct(int $productID): string {

	$variantMarkupForCurrentBuyNowProduct = "";
	$productAndItsVariants = getBuyNowProduct($productID);
	$product = $productAndItsVariants[$productID];
	$variantsForProduct = getBuyNowProductVariants($productID, $productAndItsVariants);
	// PROCESS VARIANTS IF AVAILABLE
	if (!empty($variantsForProduct)) {
		// we have variants
		$variantMarkupForCurrentBuyNowProduct = getMarkupForProductVariants($variantsForProduct);
	}
	$productValues = [
		'product_id' => $productID,
		'product_price' => $product['price'],
		'title' => $product['title'],
	];
	if (!empty($variantsForProduct)) {
		$productValues['variants'] = $variantsForProduct;
	}
	// bd($productAndItsVariants, __METHOD__ . ': $productAndItsVariants at line #' . __LINE__);
// bd($variantsForProduct, __METHOD__ . ': $variantsForProduct at line #' . __LINE__);
// bd($productValues, __METHOD__ . ': $productValues at line #' . __LINE__);

	$productValuesJSON = json_encode($productValues, JSON_HEX_APOS);


	///////////////

	// HTMX attributes markup for when item 'add to basket' action is triggered
	$htmxMarkupForUpdateCart = getMarkupForHtmxUpdateBasket();
	# --------------
	$store = '$store.HtmxAlpineTailwindDemosStore';
	$out =
		"<div id='htmx_alpine_tailwind_demos_fetch_buy_now_product_wrapper' x-ref='htmx_alpine_tailwind_demos_fetch_buy_now_product_wrapper' x-init='initFetchedProductValues({$productValuesJSON})'>" .
		// 1. RESPONSE FOR MODAL BUY NOW (product title, variants if any, increase/decrease quantity, add to basket button, etc.)
		// $titleMarkupForCurrentBuyNowProduct
		"<h4 class='font-bold XXXtext-lg'>{$product['title']}</h4>" .

		# ======================
		// VARIANTS MARKUP IF AVAILABLE
		$variantMarkupForCurrentBuyNowProduct .
		# =====================

		# CART ACTIONS: -/+ BUTTONS, QUANTITY INPUT + ADD TO BASKET BUTTON
		// $cartActionsMarkupForCurrentBuyNowProduct
		"<div class='form-control'>" .
		"<span class='text-md'>" .
		// unit price
		"$<span class='mr-1' x-text='getProductOrSelectedVariantPrice()'></span>" .
		// total price
		"($<span x-text='getCurrentTotalPrice()'></span>)" .
		"</span>" . // @note: using this so we get the updated value if manual quantity inputted
		"<div class='input-group'>" .
		// @note: note the :disabled bind! '-' & '+' buttons, the quantity input and 'add to basket' will be disabled if product has variants and none is yet selected
		"<button class='btn btn-outline btn' @click='handleBuyNowQuantity(-1)' :disabled='{$store}.is_need_to_select_a_variant'>&minus;</button>" .
		"<input name='htmx_alpine_tailwind_demos_add_to_basket_quantity' class='w-14 border border-x-0 border-black bg-transparent text-center input input-bordered htmx_alpine_tailwind_demos_buy_now' type='number' value='1' min='1' x-model.number='{$store}.current_buy_now_product_quantity'  :disabled='{$store}.is_need_to_select_a_variant'/>" .
		"<button class='btn btn-outline' @click='handleBuyNowQuantity(1)' :disabled='{$store}.is_need_to_select_a_variant'>&plus;</button>" .

		# ADD TO BASKET BUTTON
		// @UPDATE: no need for alpine.js on this one! htmx can send the needed values directly, i.e. quantity and product_id
		// "<button class='btn btn-primary uppercase ml-1' @click='handUpdateCart' :disabled='{$store}.is_need_to_select_a_variant'>Add to basket</button>" .
		"<button class='btn btn-primary uppercase ml-1' :disabled='{$store}.is_need_to_select_a_variant||!{$store}.current_buy_now_product_quantity' $htmxMarkupForUpdateCart>Add to basket</button>" .
		// @TODO ADD THIS HIDDEN INPUT AS PART OF THE MODAL RESPONSE OF FETCH BUY NOW PRODUCT; IT WILL BE FOR ADD TO BASKET (I.E., SIMILAR TO THE ALPINE JS 'BUY NOW' INPUT) => ALSO CHANGE HTMX CLASSES! THIS IS BUY NOW BUT OTHER BELOW SHOULD BE FETCH!
		"<input name='htmx_alpine_tailwind_demos_add_to_basket_product_id' class='htmx_alpine_tailwind_demos_buy_now' type='hidden' value='{$productID}'>" .
		"</div>" .
		"</div>" .
		// END: div#htmx_alpine_tailwind_demos_fetch_buy_now_product_wrapper
		"</div>";
	// END: modal markup for fetch current buy now product using htmx
	// ----------

	// END: modal markup for fetch response to 'add to basket' using htmx
	# ============================== END MODAL MARKUP ==============
	return $out;
}

function getMarkupForHtmxFetchBuyNowProduct() {
	// SENDING HTMX GET REQUEST TO THE 'root' ProcessWire page.
	$hxGetForFetchBuyNowProduct = '/';
	// WE WILL REPLACE THE CONTENTS OF THIS DIV with the server response
	$hxTargetForFetchBuyNowProduct = '#htmx_alpine_tailwind_demos_get_buy_now_product_wrapper';
	// THIS TELLS HTMX WHERE WITHIN (or without) THE TARGET TO PLACE THE MARKUP RETURNED BY THE SERVER
// @note: we use 'afterbegin' so that it gets inserted before the 'loading' markup. htmx will show/hide the div#loading per request flight
	$hxSwapForFetchBuyNowProduct = 'afterbegin';
	// @NOTE: hx-include will be specific for the product whose 'buy now' button is clicked @see below
// we list to a custom even to trigger this htmx action
// $htmxMarkupForFetchBuyNowProduct = "hx-trigger='{$hxTriggerForFetchBuyNowProduct}' hx-target='{$hxTargetForFetchBuyNowProduct}' hx-get='${hxGetForFetchBuyNowProduct}' hx-swap='{$hxSwapForFetchBuyNowProduct}' hx-include='{$hxIncludeForFetchBuyNowProduct}'";
// CSS selector for the 'loding/spinner' element. @see getModalMarkupForIsFetchingProduct()
	$hxIndicator = "#loading";
	$out = "hx-target='{$hxTargetForFetchBuyNowProduct}' hx-get='${hxGetForFetchBuyNowProduct}' hx-swap='{$hxSwapForFetchBuyNowProduct}' hx-indicator='{$hxIndicator}'";
	return $out;
}
function getMarkupForHtmxUpdateBasket() {
	// @TODO: THIS SHOULD CHANGE! UPDATE CART WILL BE TRIGGERED BY 'ADD TO BASKET' BUTTON CLICK!
	// $hxTriggerForUpdateCart = 'HtmxAlpineTailwindDemosGetBuyNowProduct';
	// SENDING HTMX GET REQUEST TO THE 'root' ProcessWire page.
	$hxPostForUpdateCart = '/';
	// WE WILL REPLACE THE CONTENTS OF THIS DIV with the server response
	$hxTargetForUpdateCart = '#htmx_alpine_tailwind_demos_get_buy_now_product_notice';
	// THIS TELLS HTMX WHERE WITHIN (or without) THE TARGET TO PLACE THE MARKUP RETURNED BY THE SERVER
// 'innerHTML' is the default; just specifying for clarity
	$hxSwapForUpdateCart = 'innerHTML';
	// WE ONLY SEND THIS/THESE comma separated NAMES of 'inputs'
// $hxParams = "htmx_alpine_tailwind_demos_get_buy_now_product_id";
	$hxIncludeForUpdateCart = ".htmx_alpine_tailwind_demos_buy_now"; // @TODO NEEDED?

	$out = "hx-target='{$hxTargetForUpdateCart}' hx-post='${hxPostForUpdateCart}' hx-swap='{$hxSwapForUpdateCart}' hx-include='{$hxIncludeForUpdateCart}'";

	return $out;
}

function getModalOutput() {
	if (wire('config')->ajax) {
		$input = wire('input');
		if ((int) $input->get('htmx_alpine_tailwind_demos_fetch_buy_now_product_id')) {
			$productID = (int) $input->get('htmx_alpine_tailwind_demos_fetch_buy_now_product_id');
			// REQUESTING A BUY NOW PRODUCT DETAILS
			$out = getModalMarkupForForFetchedProduct($productID);
		} else if ((int) $input->post('htmx_alpine_tailwind_demos_add_to_basket_product_id')) {
			// UPDATING BASKET/CART (buy now)
			$out = handleAddItemToBasket();
		} else {
			// UNRECOGNISED REQUEST
			$out = "<p>Uknown error has occurred</p>";
		}
	} else {
		// NON-AJAX: INITIAL MARKUP SHOWING 'loading' when fetch now modal is open
		// @TODO ADD ALPINE X-SHOW ATTRIBUTE TO IT!
		$out = getModalMarkupForIsFetchingProduct();
	}
	// -----
	return $out;
}

// Primary content is the page's body copy
// $content = $page->get('body');


# >>>> HTMX <<<<
// @NOTE: by default, hx-get does not include any parameters
// @see: https://htmx.org/attributes/hx-get/
// WE ARE MANUALLY SPECIFYING A TRIGGER for HTMX; in this case, a custom event 'HtmxAlpineTailwindDemosGetBuyNowProduct'
// $hxTriggerForGetBuyNowProduct = 'HtmxAlpineTailwindDemosGetBuyNowProduct';
// HTMX attributes markup for when 'buy now' triggers fetching of details of the given product. this is populated in the modal
$htmxMarkupForFetchBuyNowProduct = getMarkupForHtmxFetchBuyNowProduct();
// +++++++++++

# >>>> ALPINE.js <<<<

$store = '$store.HtmxAlpineTailwindDemosStore';

// @note: just for consistency @see below $buyNowValues
$defaultBuNowValues = [
	'product_id' => 0,
	'product_price' => 0,
	'product_title' => '',
];
$defaultBuNowValuesJSON = json_encode($defaultBuNowValues);


// ~~~~~~~~~~~~~~~~~~~~~~~~ OUTPUTS ~~~~~~~~~~~~~~~
# ********** MODAL **********


# **********
// CONTENT for $content for _main.php

$selectorArray = [
	'template' => 'product',
	'status<' => Page::statusTrash
];

$products = $pages->find($selectorArray);
$parentProductsIDsStr = '';
$productsIDs = $products->explode('id');
$allProductsVariants = [];
$idsOfProductsWithVariants = [];

// bd($products, 'products');
if (!empty($productsIDs)) {
	$parentProductsIDsStr = implode("|", $productsIDs);
	// @note: don't really need the template part but just being thorough
	$variantsSelector = "parent.id={$parentProductsIDsStr},template=product-variant,sort=parent,sort=title";
	// $variantsFields = ['id', 'title', 'price', 'parent_id', 'parent.price'];
	$variantsFields = ['id', 'title', 'price', 'parent_id', 'parent'];
	$allProductsVariants = $pages->findRaw($variantsSelector, $variantsFields);
	$idsOfProductsWithVariants = array_unique(array_column($allProductsVariants, 'parent_id'));
}

// @TODO YOU NEED TO ADD YOUR OWN CHECKS HERE IF IMAGES EXIST!

$content = "
<section class='not-prose'>
<div class='mx-auto grid grid-cols-1 gap-6 p-6 XXXsm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5'>
";

// LOOP TO BUILD MARKUP FOR DISPLAY OF PRODUCTS
foreach ($products as $product) {
	$image = $product->images->first();
	$thumb = $image->size(260, 260);
	$productTitle = $sanitizer->truncate($product->headline, 75);

	$productPriceStr = getFormattedPrice($product->price);

	// if product has VARIANTS, amend price string to 'from...'
	if (in_array($product->id, $idsOfProductsWithVariants)) {
		// @TODO
		$productPriceStr = "from {$productPriceStr}";
	}

	// =======
	// @TODO NOT SURE ABOUT CSS 'object-attrs' below!
	$content .=
		// ** PRODUCT CARD **
		"<article class='rounded-xl XXXbg-white bg-slate-50 p-3 shadow-lg hover:shadow-xl hover:transform hover:scale-105 duration-300 '>" .
		"<a href='{$product->url}'>" .
		"<div class='relative flex items-end overflow-hidden rounded-xl'>" .
		// PRODUCT THUMB
		"<img
			class='h-48 md:w-full object-none md:object-cover object-center'
				src='{$thumb->url}'
				alt='{$image->description}' />" .
		"</div>" .
		// PRODUCT TITLE
		"<h2 class='text-slate-700'>{$productTitle}</h2>" .
		"</a>" .
		"<div class='mt-1 p-2'>" .
		// PRICE + BUY NOW BUTTON + ICON
		"<div class='mt-3 flex items-end justify-between'>" .
		"<p class='text-lg font-bold text-primary'>{$productPriceStr}</p>" .

		"<div class='flex items-center space-x-1.5 rounded-lg px-4 py-1.5 duration-100 btn btn-primary'>" .
		"<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5'
			stroke='currentColor' class='h-4 w-4'>
			<path stroke-linecap='round' stroke-linejoin='round'
			d='M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z' />
			</svg>" .
		"<button
		class='text-sm uppercase'
		@click.stop='handleModalState()' {$htmxMarkupForFetchBuyNowProduct}
		hx-include='#htmx_alpine_tailwind_demos_fetch_buy_now_product_id_{$product->id}'
		>
			buy now
		</button>" .
		// HIDDEN INPUT FOR fetch details of BUY NOW PRODUCT with given product ID for HTMX USE
		"<input
		name='htmx_alpine_tailwind_demos_fetch_buy_now_product_id'
		id='htmx_alpine_tailwind_demos_fetch_buy_now_product_id_{$product->id}'
		type='hidden'
		value='{$product->id}'
		>" .
		"</div>" .
		// end buy now wrapper
		"</div>" .
		// end buy now + price wrapper
		"</div>" .
		"</article>";

}
// @TODO ADD HIDDEN INPUT TO SIGNAL FETCH PRODUCT DETAILS (FIRST MARKUP FOR MODAL) RATHER THAN ADD TO BASKET!
// END: foreach($products as $product)
$content .= "</div>
</section>";


// fetch $modalOutput for _func processBuyNowAction() but also for $content below
$modalOutput = getModalOutput();
// ---------

$content .=
	// using 'shorthand conditional [&&]'
// @see: https://alpinejs.dev/directives/bind#shorthand-conditionals
	"<div class='modal modal-bottom sm:modal-middle' :class='{$store}.is_modal_open && `modal-open`'>" .
	// MODAL CONTENT - part of it will be 'swapped' using htmx
	"<div class='modal-box'>" .
	// ELEMENT FOR HTMX SWAP (GET CURRENT BUY NOW PRODUCT DETAILS ACTION RESPONSE {triggered by click of 'buy now' button for a product})
	// main modal content to swap out
	"<div id='htmx_alpine_tailwind_demos_get_buy_now_product_wrapper'>" .
	// >>>>>>>>>>>>>>>>>>>>
	// APPEND MODAL MARKUP TO $content
	// @note: initially this show 'loading' as we wait for htmx to fetch requested product to load in modal
	$modalOutput .
	# <<<<<<<<<<<<<<<<<<

	// ----------
	"</div>" .
	// end #htmx_alpine_tailwind_demos_get_buy_now_product_wrapper
	// ELEMENT FOR HTMX SWAP (ADD TO CART ACTION RESPONSE {NOTICE})
	// @note: will show success/fail of add to basket
	"<div id='htmx_alpine_tailwind_demos_get_buy_now_product_notice' x-ref='htmx_alpine_tailwind_demos_get_buy_now_product_notice'>" .
	"</div>" . // END: div#htmx_alpine_tailwind_demos_get_buy_now_product_notice

	// MODAL ACTION
	"<div class='modal-action'>" .
	// on click this 'close button', we set current buy now product to '0'
	// THIS WILL close the modal and reset current buy now values in the Alpine.js store 'HtmxAlpineTailwindDemosStore'
	// @TODO: DO WE STILL NEED $defaultBuNowValuesJSON???
	"<button class='btn XXXbtn-ghost btn-secondary' @click='handleBuyNow({$defaultBuNowValuesJSON})'>close</button>" .
	"</div>" . // END: div.modal-action
	// ----
	"</div>" . // END: div.modal-box
	// -----
	"</div>"; // END: div.modal

// ====
///////////////////
// echo $content;// @NOTE NO LONGER NEEDED AS WE ACCESS $content, etc by using php require_once(this_file)