<?php
namespace ProcessWire;

// DEMO: HTMX RENDERS  MODAL
// render for products.php template file

$pages = wire('pages');
$sanitizer = wire('sanitizer');
$input = wire('input');
// $config = wire('config');

function getBuyNowProduct($productID) {
	$pages = wire('pages');
	$fields = ['id', 'title', 'price', 'parent_id'];
	$selector = "(template=product,id={$productID})(template=product-variant,parent.id={$productID}),sort=sort,sort=title";
	$productAndItsVariants = $pages->findRaw($selector, $fields);
	bd($productID, __METHOD__ . ': $productID at line #' . __LINE__);
	bd($fields, __METHOD__ . ': $fields at line #' . __LINE__);
	bd($selector, __METHOD__ . ': $selector at line #' . __LINE__);
	bd($productAndItsVariants, __METHOD__ . ': $productAndItsVariants at line #' . __LINE__);
	// ----
	return $productAndItsVariants;
}

function getMarkupForProductVariants($variantsForProduct) {
	$store = '$store.HtmxAlpineTailwindDemosStore';
	$out =
		// @NOTE: <template> can have only one root element
		"<div id='htmx_alpine_tailwind_demos_buy_now_product_variants_wrapper'>" .
		"<span>Select an option</span>" .
		"<div class='mb-3'>";

	foreach ($variantsForProduct as $variant) {
		$variantJSON = json_encode($variant);
		$out .=
			"<button class='btn btn-sm' @click='setCurrentBuyNowProductSelectedVariant({$variantJSON})' :class='checkIsCurrentVariantID({$variant['id']}) ?``:`btn-ghost`'>{$variant['title']}</button>";
	}

	// HIDDEN INPUT FOR CURRENT BUY NOW PRODUCT SELECTED VARIANT ID for HTMX USE
	// @note: we bind its value to Alpine.js store value 'current_buy_now_product_selected_variant_id'
	$out .= "</div><input name='htmx_alpine_tailwind_demos_get_buy_now_product_variant_id' class='htmx_alpine_tailwind_demos_buy_now' type='hidden' x-model='{$store}.current_buy_now_product_selected_variant_id'>";

	// -----
	// end div#htmx_alpine_tailwind_demos_buy_now_product_variants_wrapper
	$out .= "</div>";
	// -
	return $out;
}

function getModalOutput() {
	$out = "";
	$store = '$store.HtmxAlpineTailwindDemosStore';
	$variantMarkupForCurrentBuyNowProduct = "";
	$input = wire('input');
	$pages = wire('pages');
	$productID = (int) $input->get('htmx_alpine_tailwind_demos_get_buy_now_product_id');

	if ($productID) {

		$variantsForProduct = [];
		$productAndItsVariants = getBuyNowProduct($productID);
		$product = $productAndItsVariants[$productID];
		$defaultBuNowValues = [
			'product_id' => $productID,
			'product_price' => $product['price'],
			'product_title' => $product['title'],
		];
		$defaultBuNowValuesJSON = json_encode($defaultBuNowValues);
		// unset($productAndItsVariants[$productID]);
		$variantsForProduct = array_filter($productAndItsVariants, fn($item) => $item['id'] !== $productID);
		if (!empty($variantsForProduct)) {
			// we have variants
			$variantMarkupForCurrentBuyNowProduct = getMarkupForProductVariants($variantsForProduct);
		}
		bd($product, __METHOD__ . ': $product at line #' . __LINE__);
		bd($variantsForProduct, __METHOD__ . ': $variantsForProduct at line #' . __LINE__);
		$htmxMarkupForGetBuyNowProduct2 = ''; // @TODO FOR TESTING ONLY; DELETE WHEN DONE
		$out =
			"<h4 class='font-bold XXXtext-lg''>{$product['title']}</h4>" .
			$variantMarkupForCurrentBuyNowProduct .
			# +++++++++++++++++++
			// BUTTONS + INCREASE/DECREASE QUANTITY BUTTONS + PRICES
			"<div class='form-control'>" .
			// "<span>$<span x-text='{$store}.current_buy_now_product_total_price'></span></span>" .
			"<span class='text-md'>" .
			// unit price
			"$<span class='mr-1' x-text='getProductOrSelectedVariantPrice()'></span>" .
			// total price
			"($<span x-text='getCurrentTotalPrice()'></span>)" .
			"</span>" . // @note: using this so we get the updated value if manual quantity inputted
			"<div class='input-group'>" .
			// @note: note the :disabled bind! '-' & '+' buttons, the quantity input and 'add to basket' will be disabled if product has variants and none is yet selected
			"<button class='btn btn-outline btn' @click='handleBuyNowQuantity(-1)' :disabled='{$store}.is_need_to_select_a_variant'>&minus;</button>" .
			"<input name='htmx_alpine_tailwind_demos_get_buy_now_quantity' class='w-14 border border-x-0 border-black bg-transparent text-center input input-bordered htmx_alpine_tailwind_demos_buy_now' type='number' value='1' min='1' x-model.number='{$store}.current_buy_now_product_quantity'  :disabled='{$store}.is_need_to_select_a_variant'/>" .
			"<button class='btn btn-outline' @click='handleBuyNowQuantity(1)' :disabled='{$store}.is_need_to_select_a_variant'>&plus;</button>" .
			"<button class='btn btn-primary uppercase ml-1' @click='handUpdateCart' :disabled='{$store}.is_need_to_select_a_variant'>Add to basket</button>" .
			"</div>" .
			"</div>" .

			// ------
			// ELEMENT FOR HTMX SWAP
			// @note: will show success/fail of add to basket
			"<div id='htmx_alpine_tailwind_demos_get_buy_now_product_notice' x-ref='htmx_alpine_tailwind_demos_get_buy_now_product_notice'>" .
			"</div>" .
			// ----------
			"</div>" . // end #htmx_alpine_tailwind_demos_get_buy_now_product_wrapper
			// HIDDEN INPUT FOR CURRENT BUY NOW PRODUCT ID for HTMX USE
			// @note: we bind its value to Alpine.js store value 'current_buy_now_product_values.product_id' [@NOTE an object whose property 'product_id' we bind]
			// @note: IN THIS DEMO, VARIANTS MARKUP FOR THE MODAL IS BUILD ON THE FLY @see the variable '$variantMarkupForCurrentBuyNowProduct'
			"<input name='htmx_alpine_tailwind_demos_get_buy_now_product_id' class='htmx_alpine_tailwind_demos_buy_now' type='hidden' x-model='{$store}.current_buy_now_product_values.product_id'>";

		;
		$outOLD =
			"<div>
		<div>
		<p>WIP CONTENT FOR HTMX RENDERS MODAL!</p>
		</div>
		</div>";
	}


	// -----
	return $out;
}

bd($input->get('htmx_alpine_tailwind_demos_get_buy_now_product_id'), __METHOD__ . ': $input->get(\'htmx_alpine_tailwind_demos_get_buy_now_product_id\') at line #' . __LINE__);

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
/*
/** @var Page $page */

// $title = $alpineJSVariable;

// Primary content is the page's body copy
// $content = $page->get('body');


# >>>> HTMX <<<<

// WE ARE MANUALLY SPECIFYING A TRIGGER for HTMX; in this case, a custom event 'HtmxAlpineTailwindDemosGetBuyNowProduct'
// $hxTriggerForGetBuyNowProduct = 'HtmxAlpineTailwindDemosGetBuyNowProduct';
// SENDING HTMX GET REQUEST TO THE 'root' ProcessWire page.
$hxGetForGetBuyNowProduct = '/';
// WE WILL REPLACE THE CONTENTS OF THIS DIV with the server response
$hxTargetForGetBuyNowProduct = '#htmx_alpine_tailwind_demos_get_buy_now_product_wrapper';
// THIS TELLS HTMX WHERE WITHIN (or without) THE TARGET TO PLACE THE MARKUP RETURNED BY THE SERVER
// 'innerHTML' is the default; just specifying for clarity
$hxSwapForGetBuyNowProduct = 'innerHTML';
// @NOTE: hx-include will be specific for the product whose 'buy now' button is clicked @see below
// we list to a custom even to trigger this htmx action
// $htmxMarkupForGetBuyNowProduct = "hx-trigger='{$hxTriggerForGetBuyNowProduct}' hx-target='{$hxTargetForGetBuyNowProduct}' hx-get='${hxGetForGetBuyNowProduct}' hx-swap='{$hxSwapForGetBuyNowProduct}' hx-include='{$hxIncludeForGetBuyNowProduct}'";
$htmxMarkupForGetBuyNowProduct = "hx-target='{$hxTargetForGetBuyNowProduct}' hx-get='${hxGetForGetBuyNowProduct}' hx-swap='{$hxSwapForGetBuyNowProduct}'";
// +++++++++++

// WE ARE MANUALLY SPECIFYING A TRIGGER for HTMX; in this case, a custom event 'HtmxAlpineTailwindDemosGetBuyNowProduct'
$hxTriggerForUpdateCart = 'HtmxAlpineTailwindDemosGetBuyNowProduct';
// SENDING HTMX GET REQUEST TO THE 'root' ProcessWire page.
$hxPostForUpdateCart = '/';
// WE WILL REPLACE THE CONTENTS OF THIS DIV with the server response
$hxTargetForUpdateCart = '#htmx_alpine_tailwind_demos_get_buy_now_product_notice';
// THIS TELLS HTMX WHERE WITHIN (or without) THE TARGET TO PLACE THE MARKUP RETURNED BY THE SERVER
// 'innerHTML' is the default; just specifying for clarity
$hxSwapForUpdateCart = 'innerHTML';
// WE ONLY SEND THIS/THESE comma separated NAMES of 'inputs'
// $hxParams = "htmx_alpine_tailwind_demos_get_buy_now_product_id";
$hxIncludeForUpateCart = ".htmx_alpine_tailwind_demos_buy_now"; // @TODO NEEDED?

//
$htmxMarkupForUpdateCart = "hx-trigger='{$hxTriggerForUpdateCart}' hx-target='{$hxTargetForUpdateCart}' hx-post='${hxPostForUpdateCart}' hx-swap='{$hxSwapForUpdateCart}'";

# >>>> ALPINE.js <<<<
// @TODO DELETE UNUSED!

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
# **********
// MOCK RESPONSE FOR MODAL BUY NOW
$modalOutput = getModalOutput();



$modalBasketUpdatedNotice =
	// add to basket success confirm
	"<div class='alert alert-success shadow-lg mt-3'>
<div>
	<svg xmlns='http://www.w3.org/2000/svg' class='stroke-current flex-shrink-0 h-6 w-6' fill='none' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>
	<span class='text-sm'>Product has been added to your basket successfully.</span>
</div>
</div>";

# **********
// CONTENT for $content for _main.php


// @TODO YOU NEED TO ADD YOUR OWN CHECKS HERE IF IMAGES EXIST!
// $content .= "<div class='not-prose XXXgrid XXXgap-4 XXXmd:grid-cols-4 XXXlg:grid-cols-5'>";
// @NOTE: 'XXXclass' are classes temporarily retained; might be deleted in future
// $content .= "<div class='flex min-h-screen w-full flex-wrap content-center justify-center p-5 bg-gray-200'>
// <div class='grid grid-cols-2 gap-3'>";
// $content .= "<div class='flex items-center justify-center min-h-screen from-[#F9F5F3] via-[#F9F5F3] to-[#F9F5F3] bg-gradient-to-br px-2 flex-wrap'>";
$content = "
<section class='not-prose' x-init='setProductsVariantsData({$idsOfProductsWithVariantsJSON},{$allProductsVariantsJSON})'>
<div class='mx-auto grid grid-cols-1 gap-6 p-6 XXXsm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5'>
";
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
		// @TODO DO WE NEED A DIFFERENT HANDLER HERE? YES; WE ONLY NEED TO OPEN THE MODAL AND SHOW A SPINNER, NO?
		"<button class='text-sm uppercase' @click.stop='handleBuyNowForGetBuyNowProduct()' value='{$product->id}' {$htmxMarkupForGetBuyNowProduct} hx-include='#htmx_alpine_tailwind_demos_get_buy_now_product_id_{$product->id}'>buy now</button>" .
		"<input name='htmx_alpine_tailwind_demos_get_buy_now_product_id' class='htmx_alpine_tailwind_demos_buy_now' type='hidden' value='{$product->id}' id='htmx_alpine_tailwind_demos_get_buy_now_product_id_{$product->id}'>" .
		"</div>" .
		// end buy now wrapper
		"</div>" .
		// end buy now + price wrapper
		"</div>" .
		"</article>";

}
// $content .= "</div>";
$content .= "			</div>
</section>";
// $content .= "	</div>
// </div>";

// MODAL
$content .=
	// using 'shorthand conditional [&&]'
// @see: https://alpinejs.dev/directives/bind#shorthand-conditionals
	"<div class='modal modal-bottom sm:modal-middle' :class='{$store}.is_modal_open && `modal-open`' {$htmxMarkupForUpdateCart}>" .
	// MODAL CONTENT - part of it will be 'swapped' using htmx
	"<div class='modal-box'>" .
	// main modal content to swap out
	"<div id='htmx_alpine_tailwind_demos_get_buy_now_product_wrapper'>" .
	# @TODO RENAME INPUTS AS NEEDED

	# >>>>>>>>>>>>
# @TODO MOVE TO AJAX RESPONSE HANDLER
# <<<<<<<<<<<<<
// @TODO NEED TO DISABLE 'ADD TO BASKET' 'INCREMENT/DECREMENT' QTY IF WE HAVE VARIANTS BUT NON SELECTED!
// setCurrentBuyNowProductSelectedVariantID
// $variantMarkupForCurrentBuyNowProduct =
// 	"<template x-if='{$store}.is_product_with_variants'>" .
// 	// @NOTE: <template> can have only one root element
// 	"<div id='htmx_alpine_tailwind_demos_buy_now_product_variants_wrapper'>" .
// 	"<span>Select an option</span>" .
// 	"<div class='mb-3'>" .
// 	"<template x-for='variant in {$store}.current_buy_now_product_variants_values' :key='variant.id'>" .
// 	// "<li x-text='variant.title'></li>" .
// 	"<button class='btn btn-sm' @click='setCurrentBuyNowProductSelectedVariant(variant)' :class='checkIsCurrentVariantID(variant.id) ?``:`btn-ghost`' x-text='variant.title'></button>" .
// 	"</template>" .
// 	"</div>" .
// 	// HIDDEN INPUT FOR CURRENT BUY NOW PRODUCT SELECTED VARIANT ID for HTMX USE
// 	// @note: we bind its value to Alpine.js store value 'current_buy_now_product_selected_variant_id'
// 	"<input name='htmx_alpine_tailwind_demos_get_buy_now_product_variant_id' class='htmx_alpine_tailwind_demos_buy_now' type='hidden' x-model='{$store}.current_buy_now_product_selected_variant_id'>" .
// 	// -----
// 	// end div#htmx_alpine_tailwind_demos_buy_now_product_variants_wrapper
	"</div>" .
	// 	"</template>";
// MODAL ACTION
	"<div class='modal-action'>" .
	// on click this 'close button', we set current buy now product to '0'
// THIS WILL close the modal and reset current buy now values in the Alpine.js store 'HtmxAlpineTailwindDemosStore'
	"<button class='btn XXXbtn-ghost btn-secondary' @click='handleBuyNow({$defaultBuNowValuesJSON})'>close</button>" .
	"</div>" .
	// ----
	"</div>" .
	// -----
	"</div>";

// ====
// @SEE ABOVE @UPDATE: WE NOW SET DIRECTLY IN AN x-init
// variants script if available
// $content .= $variantsScript;
///////////////////
// echo $content;