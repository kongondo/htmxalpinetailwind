<?php
namespace ProcessWire;

// products.php template file

/** @var Page $page */

// Primary content is the page's body copy
// $content = $page->get('body');

$selectorArray = [
	'template' => 'product',
	'status<' => Page::statusTrash
];

// for findRaw
// $fields = ['id', 'title', ];
$products = $pages->find($selectorArray);


// bd($products, 'products');

// @TODO YOU NEED TO ADD YOUR OWN CHECKS HERE IF IMAGES EXIST!
$content .= "<div class='not-prose grid gap-4 md:grid-cols-4 lg:grid-cols-5'>";
// @NOTE: 'XXXclass' are classes temporarily retained; might be deleted in future
foreach ($products as $product) {
	$image = $product->images->first();
	$thumb = $image->size(260, 260);
	$productTitle = $sanitizer->truncate($product->headline, 75);
	$content .=
		"<div class='card card-compact w-full bg-base-100 XXXshadow-md p-2 XXXm-2 rounded-lg XXXh-96'>
	<figure><img src='{$thumb->url}' alt='{$image->description}' class='w-full'></figure>
	<div class='card-body px-0 shadow-md'>
		<h2 class='card-title'>{$product->title}</h2>
		<p>{$productTitle}</p>
		<div class='card-actions justify-end'>
			<button class='btn-sm btn-primary'>Buy Now</button>
		</div>
	</div>
</div>
";

}
$content .= "</div>";