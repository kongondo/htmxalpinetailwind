<?php
namespace ProcessWire;

// products.php template file

/*
>>>>>>>>>>>>>>>>>>>>>>>>
DEMO NOTES
1. Demo 'buy now' action
2. Alpine.js handles the action
-  opens/closses a modal
- set ID of the current buy now product
- this ID is modeled by a hidden input #htmx_alpine_tailwind_demos_buy_now_product_id
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

// Primary content is the page's body copy
// $content = $page->get('body');

$breadcrumb = buildBreadCrumb($page);

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
// $hxParams = "htmx_alpine_tailwind_demos_get_buy_now_product_id";
$hxInclude = ".htmx_alpine_tailwind_demos_buy_now";
// --------
// we list to a custom even to trigger this htmx action
$htmxMarkupForBuyNow = "hx-trigger='{$hxTrigger}' hx-target='{$hxTarget}' hx-get='${hxGet}' hx-swap='{$hxSwap}' hx-include='{$hxInclude}'";

# >>>> ALPINE.js <<<<

$store = '$store.HtmxAlpineTailwindDemosStore';

// @note: just for consistency @see below $buyNowValues
$defaultByNowValues = [
	'product_id' => 0,
	'product_price' => 0,
	'product_title' => '',
];
$defaultByNowValuesJSON = json_encode($defaultByNowValues);

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
bd($products, 'products');
if (!empty($productsIDs)) {
	$parentProductsIDsStr = implode("|", $productsIDs);
	// @note: don't really need the template part but just being thorough
	$variantsSelector = "parent.id={$parentProductsIDsStr},template=product-variant,sort=parent,sort=title";
	// $variantsFields = ['id', 'title', 'price', 'parent_id', 'parent.price'];
	$variantsFields = ['id', 'title', 'price', 'parent_id', 'parent'];
	$allProductsVariants = $pages->findRaw($variantsSelector, $variantsFields);
	$idsOfProductsWithVariants = array_unique(array_column($allProductsVariants, 'parent_id'));
}
bd($allProductsVariants, 'allProductsVariants');
bd($idsOfProductsWithVariants, 'idsOfProductsWithVariants');

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


// @TODO YOU NEED TO ADD YOUR OWN CHECKS HERE IF IMAGES EXIST!
// $content .= "<div class='not-prose XXXgrid XXXgap-4 XXXmd:grid-cols-4 XXXlg:grid-cols-5'>";
// @NOTE: 'XXXclass' are classes temporarily retained; might be deleted in future
// $content .= "<div class='flex min-h-screen w-full flex-wrap content-center justify-center p-5 bg-gray-200'>
// <div class='grid grid-cols-2 gap-3'>";
// $content .= "<div class='flex items-center justify-center min-h-screen from-[#F9F5F3] via-[#F9F5F3] to-[#F9F5F3] bg-gradient-to-br px-2 flex-wrap'>";
$content .= "
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
		bd($variantsForProduct, 'variantsForProduct');
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
		"<button class='text-sm uppercase' @click.stop='handleBuyNow({$buyNowValuesJSON})'>buy now</button>" .
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
	"<div class='modal modal-bottom sm:modal-middle' :class='{$store}.is_modal_open && `modal-open`' {$htmxMarkupForBuyNow}>" .
	// MODAL CONTENT - part of itwill be 'swapped' using htmx
	"<div class='modal-box'>" .
	// main modal content to swap out
	"<div id='htmx_alpine_tailwind_demos_get_buy_now_product_wrapper'>" .
	"<h3 class='font-bold text-lg' x-text='{$store}.current_buy_now_product_values.product_title'></h3>" .
	// BUTTONS + INCREASE/DECREASE QUANTITY BUTTONS + PRICES
	"<div class='form-control'>" .
	// "<span>$<span x-text='{$store}.current_buy_now_product_total_price'></span></span>" .
	"<span class='text-md'>" .
	// unit price
	"$<span class='mr-1' x-text='{$store}.current_buy_now_product_values.product_price'></span>" .
	// total price
	"($<span x-text='getCurrentTotalPrice()'></span>)" .
	"</span>" . // @note: using this so we get the updated value if manual quantity inputted
	"<div class='input-group'>" .
	"<button class='btn btn-outline btn' @click='handleBuyNowQuantity(-1)'>&minus;</button>" .
	"<input name='htmx_alpine_tailwind_demos_get_buy_now_quantity' class='w-14 border border-x-0 border-black bg-transparent text-center input input-bordered htmx_alpine_tailwind_demos_buy_now' type='number' value='1' min='1' x-model='{$store}.current_buy_now_product_quantity'/>" .
	"<button class='btn btn-outline' @click='handleBuyNowQuantity(1)'>&plus;</button>" .
	"<button class='btn btn-primary uppercase ml-1' @click='handUpdateCart'>Add to basket</button>" .
	"</div>" .
	"</div>" .

	// ------
	// ELEMENT FOR HTMX SWAP
	// @note: will show success/fail of add to basket
	"<div id='htmx_alpine_tailwind_demos_get_buy_now_product_notice'>" .
	"<p>Please add item to basket.</p>" .
	"</div>" .
	// ----------
	"</div>" . // end #htmx_alpine_tailwind_demos_get_buy_now_product_wrapper
	// HIDDEN INPUT FOR CURRENT BUY NOW PRODUCT ID for HTMX USE
	// @note: we bind its value to Alpine.js store value 'current_buy_now_product_values.product_id' [@NOTE an object whose property 'product_id' we bind]
	// @note: we will modify 'current_buy_now_product_values' in case server returns a product with variants. Value here will be changed when variant is selected
	"<input name='htmx_alpine_tailwind_demos_get_buy_now_product_id' class='htmx_alpine_tailwind_demos_buy_now' type='hidden' x-model='{$store}.current_buy_now_product_values.product_id'>" .
	// MODAL ACTION
	"<div class='modal-action'>" .
	// on click this 'close button', we set current buy now product to '0'
	// THIS WILL close the modal and reset current buy now values in the Alpine.js store 'HtmxAlpineTailwindDemosStore'
	"<button class='btn XXXbtn-ghost btn-secondary' @click='handleBuyNow({$defaultByNowValuesJSON})'>close</button>" .
	"</div>" .
	// ----
	"</div>" .
	// -----
	"</div>";

// ====
// @SEE ABOVE @UPDATE: WE NOW SET DIRECTLY IN AN x-initi
// variants script if available
// $content .= $variantsScript;