<?php
namespace ProcessWire;

// products.php template file

/** @var Page $page */

// Primary content is the page's body copy
// $content = $page->get('body');

$breadcrumb = buildBreadCrumb($page);

$selectorArray = [
	'template' => 'product',
	'status<' => Page::statusTrash
];

// for findRaw
// $fields = ['id', 'title', ];
$products = $pages->find($selectorArray);


// bd($products, 'products');

// @TODO YOU NEED TO ADD YOUR OWN CHECKS HERE IF IMAGES EXIST!
// $content .= "<div class='not-prose XXXgrid XXXgap-4 XXXmd:grid-cols-4 XXXlg:grid-cols-5'>";
// @NOTE: 'XXXclass' are classes temporarily retained; might be deleted in future
// $content .= "<div class='flex min-h-screen w-full flex-wrap content-center justify-center p-5 bg-gray-200'>
// <div class='grid grid-cols-2 gap-3'>";
// $content .= "<div class='flex items-center justify-center min-h-screen from-[#F9F5F3] via-[#F9F5F3] to-[#F9F5F3] bg-gradient-to-br px-2 flex-wrap'>";
$content .= "
<section class='not-prose'>
<div class='mx-auto grid grid-cols-1 gap-6 p-6 XXXsm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5'>
";
foreach ($products as $product) {
	$image = $product->images->first();
	$thumb = $image->size(260, 260);
	$productTitle = $sanitizer->truncate($product->headline, 75);
	// @TODO NOT SURE ABOUT 'object-attrs' below!

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
		"<p class='text-lg font-bold text-primary'>\${$product->price}</p>" .

		"<div class='flex items-center space-x-1.5 rounded-lg px-4 py-1.5 duration-100 btn btn-primary'>" .
		"<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5'
			stroke='currentColor' class='h-4 w-4'>
			<path stroke-linecap='round' stroke-linejoin='round'
			d='M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z' />
			</svg>" .
		// buy now
		"<button class='text-sm uppercase' @click.stop='handleBuyNow({$product->id})'>buy now</button>" .
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