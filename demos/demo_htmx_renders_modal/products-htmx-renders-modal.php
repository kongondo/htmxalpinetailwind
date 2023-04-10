<?php
namespace ProcessWire;

// DEMO: ALPINE.js RENDERS  MODAL
// render for products.php template file

$pages = wire('pages');
$sanitizer = wire('sanitizer');
/*
>>>>>>>>>>>>>>>>>>>>>>>>
DEMO NOTES
1. Demo 'buy now' action
2. Alpine.js handles the action
-  opens/closes a modal
- set ID of the current buy now product
- this ID is modeled by a hidden input #htmx_alpine_tailwind_demos_add_to_basket_product_id
- Alpine dispatches a custom event that htmx is listening to.
3. htmx picks up the custom event 'HtmxAlpineTailwindDemosGetBuyNowProduct' [purposefully verbosely long for clarity!]
- htmx sends a get request to the server
- server sends response to htmx: if product found; the markup for that, else fail markup
- htmx populates the 'buy now modal' and listens to increment/decrement product in basket
@NOTES:
- This is just one way of doing this
- An alternative (@TODO DEMO FOR THIS/WIP) is to populate the modal with details on the client side
- We use Alpine.js for this
- This would mean Alpine.js gets all products (those in view) details and stores this in the $store.HtmxAlpineTailwindDemosStore
- When populating the modal, Alpine gets the information from the store
- the increment/decrement product in cart/basket are handled by htmx as usual
- This is trickier if your store has variants since it would mean fetching them all for all current products
- Ideally you want to use findRaw() in that case
<<<<<<<<<<<<<<<<<<<<<<<
/*
/** @var Page $page */

function getModalOutput() {
	$input = wire('input');
	// if
	$out = "<p>MODAL OUTPUT HERE: EITHER FETCHED PRODUCT OR NOTICE OF ADD TO BASKET OR EMPTY ON LOAD<p>";
	// -----
	return $out;
}

// Primary content is the page's body copy
// $content = $page->get('body');


# >>>> HTMX <<<<

// WE ARE MANUALLY SPECIFYING A TRIGGER for HTMX; in this case, a custom event 'HtmxAlpineTailwindDemosGetBuyNowProduct'
$hxTrigger = 'HtmxAlpineTailwindDemosGetBuyNowProduct';
// SENDING HTMX GET REQUEST TO THE 'root' ProcessWire page.
$hxGet = '/';
// WE WILL REPLACE THE CONTENTS OF THIS DIV with the server response
$hxTarget = '#htmx_alpine_tailwind_demos_get_buy_now_product_notice';
// THIS TELLS HTMX WHERE WITHIN (or without) THE TARGET TO PLACE THE MARKUP RETURNED BY THE SERVER
// 'innerHTML' is the default; just specifying for clarity
$hxSwap = 'innerHTML';
// WE ONLY SEND THIS/THESE comma separated NAMES of 'inputs'
// $hxParams = "htmx_alpine_tailwind_demos_add_to_basket_product_id";
$hxInclude = ".htmx_alpine_tailwind_demos_buy_now";
// --------
// we list to a custom even to trigger this htmx action
$htmxMarkupForBuyNow = "hx-trigger='{$hxTrigger}' hx-target='{$hxTarget}' hx-get='${hxGet}' hx-swap='{$hxSwap}' hx-include='{$hxInclude}'";

# >>>> ALPINE.js <<<<

$store = '$store.HtmxAlpineTailwindDemosStore';

// @note: just for consistency @see below $buyNowValues
$defaultBuNowValues = [
	'product_id' => 0,
	'product_price' => 0,
	'product_title' => '',
];
$defaultBuNowValuesJSON = json_encode($defaultBuNowValues);

$selectorArray = [
	'template' => 'product',
	'status<' => Page::statusTrash
];

// for findRaw
// $fields = ['id', 'title', ];
$products = $pages->find($selectorArray);
// bd($products, 'products');
$parentProductsIDsStr = '';
$productsIDs = $products->explode('id');
$allProductsVariants = [];
$idsOfProductsWithVariants = [];
$variantsScript = '';
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
// bd($allProductsVariants, 'allProductsVariants');
// bd($idsOfProductsWithVariants, 'idsOfProductsWithVariants');

// SCRIPT TO SEND VARIANTS DATA TO BROWSER
// for alpine for use in modal for 'BUY NOW'
// @NOTE: this is just one strategy to send data to the browser!
// @TODO MAYBE JUST PASS TO ALPINE DIRECTLY VIA X-INIT? THEN SET TO STORE? COULD DO SO HERE OR IN THE LOOP FOR EACH ITEM BUT FORMER IS CLEANER/BETTER? @UPDATE: YES! @SEE BELOW; WE SET DIRECTLY TO x-init
// if (!empty($allProductsVariants)) {
// $allProductsVariantsJSON = json_encode($allProductsVariants);
// $idsOfProductsWithVariantsJSON = json_encode($idsOfProductsWithVariants);
// $variantsScript =
// 	"<script>" .
// 	"const allProductsVariants = {$allProductsVariantsJSON}\n" .
// 	"const idsOfProductsWithVariants = {$idsOfProductsWithVariantsJSON}\n" .
// 	"</script>";
// }
// 'OBJECTS' for alpine x-init to set product variants values later
$allProductsVariantsJSON = json_encode($allProductsVariants);
$idsOfProductsWithVariantsJSON = json_encode($idsOfProductsWithVariants);

// ~~~~~~~~~~~~~~~~~~~~~~~~ OUTPUTS ~~~~~~~~~~~~~~~
# ********** MODAL **********

// 1. RESPONSE FOR MODAL BUY NOW (product title, variants if any, increase/decrease quantity, add to basket button, etc.)

$titleMarkupForCurrentBuyNowProduct = "<small>reminder: spinner here </small><h4 class='font-bold XXXtext-lg'>TITLE: HTMX LOAD FROM SERVER</h4>";

// @TODO NEED TO DISABLE 'ADD TO BASKET' 'INCREMENT/DECREMENT' QTY IF WE HAVE VARIANTS BUT NON SELECTED!
// setCurrentBuyNowProductSelectedVariantID
$variantMarkupForCurrentBuyNowProduct =
	"<template x-if='{$store}.is_product_with_variants'>" .
	// @NOTE: <template> can have only one root element
	"<div id='htmx_alpine_tailwind_demos_buy_now_product_variants_wrapper'>" .
	"<span>Select an option</span>" .
	"<div class='mb-3'>" .
	"<template x-for='variant in {$store}.current_buy_now_product_variants_values' :key='variant.id'>" .
	// "<li x-text='variant.title'></li>" .
	"<button class='btn btn-sm' @click='setCurrentBuyNowProductSelectedVariant(variant)' :class='checkIsCurrentVariantID(variant.id) ?``:`btn-ghost`' x-text='variant.title'></button>" .
	"</template>" .
	"</div>" .
	// HIDDEN INPUT FOR CURRENT BUY NOW PRODUCT SELECTED VARIANT ID for HTMX USE
	// @note: we bind its value to Alpine.js store value 'current_buy_now_product_selected_variant_id'
	"<input name='htmx_alpine_tailwind_demos_buy_now_product_variant_id' class='htmx_alpine_tailwind_demos_buy_now' type='hidden' x-model='{$store}.current_buy_now_product_selected_variant_id'>" .
	// -----
	// end div#htmx_alpine_tailwind_demos_buy_now_product_variants_wrapper
	"</div>" .
	"</template>";

# CART ACTIONS: -/+ BUTTONS, QUANTITY INPUT + ADD TO BASKET BUTTON

$cartActionsMarkupForCurrentBuyNowProduct = "<div class='form-control'>" .
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
	"<button class='btn btn-primary uppercase ml-1' @click='handUpdateCart' :disabled='{$store}.is_need_to_select_a_variant'>Add to basket</button>" .
	// @TODO ADD THIS HIDDEN INPUT AS PART OF THE MODAL RESPONSE OF FETCH BUY NOW PRODUCT; IT WILL BE FOR ADD TO BASKET (I.E., SIMILAR TO THE ALPINE JS 'BUY NOW' INPUT) => ALSO CHANGE HTMX CLASSES! THIS IS BUY NOW BUT OTHER BELOW SHOULD BE FETCH!
	// "<input name='htmx_alpine_tailwind_demos_add_to_basket_product_id' class='htmx_alpine_tailwind_demos_buy_now' type='hidden' value='{$product->id}'>" .
	"</div>" .
	"</div>";
// END: modal markup for fetch current buy now product using htmx
// ----------
// 2. MOCK RESPONSE FOR MODAL BUY NOW
$modalOutput =
	// add to basket success confirm
	"<div class='alert alert-success shadow-lg mt-3'>
		<div>
			<svg xmlns='http://www.w3.org/2000/svg' class='stroke-current flex-shrink-0 h-6 w-6' fill='none' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>
			<span class='text-sm'>Product has been added to your basket successfully.</span>
			</div>
	</div>";
// END: modal markup for fetch response to 'add to basket' using htmx
# ============================== END MODAL MARKUP ==============

# **********
// CONTENT for $content for _main.php

// @TODO YOU NEED TO ADD YOUR OWN CHECKS HERE IF IMAGES EXIST!

$content = "
<section class='not-prose' x-init='setProductsVariantsData({$idsOfProductsWithVariantsJSON},{$allProductsVariantsJSON})'>
<div class='mx-auto grid grid-cols-1 gap-6 p-6 XXXsm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5'>
";

// LOOP TO BUILD MARKUP FOR DISPLAY OF PRODUCTS
foreach ($products as $product) {
	$image = $product->images->first();
	$thumb = $image->size(260, 260);
	$productTitle = $sanitizer->truncate($product->headline, 75);

	$productPriceStr = getFormattedPrice($product->price);

	$buyNowValues = [
		'product_id' => $product->id,
		'product_price' => $product->price,
		'product_title' => $product->title,
	];
	$buyNowValuesJSON = json_encode($buyNowValues);

	// ======
	$variantsForProduct = [];
	// PROCESS VARIANTS IF AVAILABLE
	if (in_array($product->id, $idsOfProductsWithVariants)) {
		// @TODO
		$variantsForProduct = getVariantsForAProduct($allProductsVariants, 'parent_id', $product->id);
		// bd($variantsForProduct, 'variantsForProduct');
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
		// buy now
		// @NOTE - THIS IS JUST ONE WAY OF PASSING VALUES TO ALPINE; e.g. we could have used data-attributes or ProcessWire config->js
		"<button class='text-sm uppercase' @click.stop='handleFetchBuyNowProduct({$buyNowValuesJSON})'>buy now</button>" .
		// HIDDEN INPUT FOR BUY NOW PRODUCT ID for HTMX USE
		"<input name='htmx_alpine_tailwind_demos_fetch_buy_now_product_id' class='htmx_alpine_tailwind_demos_buy_now' type='hidden' value='{$product->id}'>" .
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


// APPEND MODAL MARKUP TO $content

$content .=
	// using 'shorthand conditional [&&]'
// @see: https://alpinejs.dev/directives/bind#shorthand-conditionals
	"<div class='modal modal-bottom sm:modal-middle' :class='{$store}.is_modal_open && `modal-open`' {$htmxMarkupForBuyNow}>" .
	// MODAL CONTENT - part of it will be 'swapped' using htmx
	"<div class='modal-box'>" .
	// ELEMENT FOR HTMX SWAP (GET CURRENT BUY NOW PRODUCT DETAILS ACTION RESPONSE {triggered by click of 'buy now' button for a product})
	// main modal content to swap out
	"<div id='htmx_alpine_tailwind_demos_get_buy_now_product_wrapper'>" .
	// >>>>>>>>>>>>>>>>>>>>
	// @note: initially this show 'loading' as we wait for htmx to fetch requested product to load in modal
	getModalOutput() .
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