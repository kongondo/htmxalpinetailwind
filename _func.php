<?php
namespace ProcessWire;

// bd($var, __METHOD__ . ': $var at line #' . __LINE__);
/**
 * /site/templates/_func.php
 *
 * Example of shared functions used by template files
 *
 * This file is currently included by _init.php
 *
 * FUN FACT: This file is identical to the one in the NON-multi-language
 * version of this site profile (site-default). In fact, it's rare that
 * one has to think about languages when developing a multi-language
 * site in ProcessWire.
 *
 */

/**
 * Given a group of pages, render a simple <ul> navigation
 *
 * This is here to demonstrate an example of a simple shared function.
 * Usage is completely optional.
 *
 * @param PageArray $items
 * @return string
 *
 */
function renderNav(PageArray $items) {

	// $out is where we store the markup we are creating in this function
	$out = '';

	// cycle through all the items
	foreach ($items as $item) {

		// render markup for each navigation item as an <li>
		if ($item->id == wire()->page->id) {
			// if current item is the same as the page being viewed, add a "current" class to it
			$out .= "<li class='current'>";
		} else {
			// otherwise just a regular list item
			$out .= "<li>";
		}

		// markup for the link
		$out .= "<a href='$item->url'>$item->title</a> ";

		// if the item has summary text, include that too
		if ($item->summary)
			$out .= "<div class='summary'>$item->summary</div>";

		// close the list item
		$out .= "</li>";
	}

	// if output was generated above, wrap it in a <ul>
	if ($out)
		$out = "<ul class='nav'>$out</ul>\n";

	// return the markup we generated above
	return $out;
}

/**
 * Given a group of pages, render a <ul> navigation tree
 *
 * This is here to demonstrate an example of a more intermediate level
 * shared function and usage is completely optional. This is very similar to
 * the renderNav() function above except that it can output more than one
 * level of navigation (recursively) and can include other fields in the output.
 *
 * @param array|PageArray $items
 * @param int $maxDepth How many levels of navigation below current should it go?
 * @param string $fieldNames Any extra field names to display (separate multiple fields with a space)
 * @param string $class CSS class name for containing <ul>
 * @return string
 *
 */
function renderNavTree($items, $maxDepth = 0, $fieldNames = '', $class = 'nav') {

	// if we were given a single Page rather than a group of them, we'll pretend they
	// gave us a group of them (a group/array of 1)
	if ($items instanceof Page)
		$items = array($items);

	// $out is where we store the markup we are creating in this function
	$out = '';

	// cycle through all the items
	foreach ($items as $item) {

		// markup for the list item...
		// if current item is the same as the page being viewed, add a "current" class to it
		$out .= $item->id == wire()->page->id ? "<li class='current'>" : "<li>";

		// markup for the link
		$out .= "<a href='$item->url'>$item->title</a>";

		// if there are extra field names specified, render markup for each one in a <div>
		// having a class name the same as the field name
		if ($fieldNames) {
			foreach (explode(' ', $fieldNames) as $fieldName) {
				$value = $item->get($fieldName);
				if ($value)
					$out .= " <div class='$fieldName'>$value</div>";
			}
		}

		// if the item has children and we're allowed to output tree navigation (maxDepth)
		// then call this same function again for the item's children
		if ($item->hasChildren() && $maxDepth) {
			if ($class == 'nav')
				$class = 'nav nav-tree';
			$out .= renderNavTree($item->children, $maxDepth - 1, $fieldNames, $class);
		}

		// close the list item
		$out .= "</li>";
	}

	// if output was generated above, wrap it in a <ul>
	if ($out)
		$out = "<ul class='$class'>$out</ul>\n";

	// return the markup we generated above
	return $out;
}

function handleAjaxRequests($input) {
	bd($input, '$input');
	$out = "<p>Sorry, we were not able to handle your request.</p>";
	# DETERMINE HANDLER TO USE #
	if (!empty($input->get('htmx_alpine_tailwind_demos_get_buy_now_product_id'))) {
		$out = processBuyNowAction((int) $input->get('htmx_alpine_tailwind_demos_get_buy_now_product_id'));
	}

	// ------
	return $out;
}

function processBuyNowAction($productID) {
	bd($productID, __METHOD__ . ': $productID at line #' . __LINE__);
	$productID = (int) $productID;
	$out =
		// add to basket success confirm
		"<div class='alert alert-success shadow-lg mt-3'>
		<div>
			<svg xmlns='http://www.w3.org/2000/svg' class='stroke-current flex-shrink-0 h-6 w-6' fill='none' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>
			<span class='text-sm'>Product has been added to your basket successfully.</span>
		</div>
	</div>";
	// ------
	return $out;
}


# >>>>>>>>>>>>>>>>>>>>>>>>>>>>

function buildBreadCrumb($page) {
	$breadcrumbItems = array_filter(explode("/", $page->path));

	$breadcrumb = "
	<div class='text-sm breadcrumbs'>
		<ul>
	";
	// if site is multilingual, remove the 'language name' from the breadcrumbs
	if (wire('languages')) {
		array_shift($breadcrumbItems);
	}

	$urlParts = [];
	foreach ($breadcrumbItems as $breadcrumbItem) {
		$urlParts[] = "{$breadcrumbItem}";
		$url = implode("/", $urlParts);
		$breadcrumb .= " <li><a href='/{$url}'>{$breadcrumbItem}</a></li> ";
	}
	$breadcrumb .= "  </ul>
	</div>";

	// -----
	return $breadcrumb;

}

function getVariantsForAProduct(array $allProductsVariants, string $property, $match): array {
	$variantsForProduct = [];
	$variantsForProduct = array_filter($allProductsVariants, fn($item) => !empty($item[$property]) && $item[$property] === $match);

	// -------
	return $variantsForProduct;
}

function getFormattedPrice(float $price) {

	// $whole = floor($product->price);
	// >> e.g. if prices is 1.2 <<
	# e.g., value is 1
	$whole = (int) $price; // 1
	# e.g. value will be 0.20 after number format,
	# we then explode to get rid of of the '0.' part by selecting the second element  (e.g. the cents) in the value
	$fractionArray = explode(".", number_format(($price - $whole), 2));
	$fraction = $fractionArray[1];

	$formattedPrice = "<span class='currency'>$</span>{$whole}<span class='decimals text-xs align-baseline font-normal relative -top-1'>{$fraction}</span>";
	// ---
	return $formattedPrice;
}

function createDummyProductAndCategoryPages() {
	$data = getFakeStoreData();
	// in case only one item fetched, we need it as an array still
	if (!is_array($data)) {
		$data = [$data];
	}
	// ---------
	// bd($data, 'data');
	$pages = wire('pages');
	$sanitizer = wire('sanitizer');
	######### create product and optionally category page
	if (!empty($data)) {
		$productsParent = $pages->get('template=products');
		$categoriesParent = $pages->get('template=categories');
		foreach ($data as $item) {
			$product = createProductPage($item, $productsParent);
			// -------
			$categoryTitle = ucwords($sanitizer->text($item->category));
			// -----
			// add category to product
			if (!empty($categoryTitle)) {
				// $product = createCategoryPage($product, $categoriesParent, $categoryTitle);
				createCategoryPage($product, $categoriesParent, $categoryTitle);
			}

		}
	}
}

function getFakeStoreData() {

	### ----- TEMP GET FAKE CONTENT from Fake Store API FOR TESTING ####

	// Get an instance of WireHttp
	$http = new WireHttp();
	$fakeStoreData = [];
	// Get a single product
	// $url = 'https://fakestoreapi.com/products/1';
	// Get a limited number of products
	// $url = 'https://fakestoreapi.com/products?limit=5';
	// Get all products
	$url = 'https://fakestoreapi.com/products';
	$response = $http->get($url);
	// example response - JSON
// '{"id":1,"title":"Fjallraven - Foldsack No. 1 Backpack, Fits 15 Laptops","price":109.95,"description":"Your perfect pack for everyday use and walks in the forest. Stash your laptop (up to 15 inches) in the padded sleeve, your everyday","category":"men's clothing","image":"https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_.jpg","rating":{"rate":3.9,"count":120}}'
// json_decode($reponse)  [stdClass]
// id: 1
// title: 'Fjallraven - Foldsack No. 1 Backpack, Fits 15 Laptops'
// price: 109.95
// description: 'Your perfect pack for everyday use and walks in the forest. Stash your laptop (up to 15 inches) in the padded sleeve, your everyday'
// category: 'men's clothing'
// image: 'https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_.jpg'
// bdb($response, 'response');
	if ($response !== false) {
		//   echo "Successful response: " . $sanitizer->entities($response);
		// bd("Successful response");
		/** @var stdClass $fakeStoreData */
		$fakeStoreData = json_decode($response);
	} else {
		//   echo "HTTP request failed: " . $http->getError();
		// bd($http->getError(), "HTTP request failed: ");
	}
	// ----
	return $fakeStoreData;
}

function createProductPage(\stdClass $item, Page $parent): Page {
	$sanitizer = wire('sanitizer');
	// ---------
	$product = new Page();
	$product->template = 'product';
	$product->parent = $parent;
	$product->title = $sanitizer->text($item->title);
	$product->name = $sanitizer->pageName($product->title);
	$product->headline = $sanitizer->entities($item->description);
	$product->price = (float) $item->price;
	// -----
	$product->save();
	// --------
	// add product image
	$product->images->add($item->image);
	$product->save();
	// ----
	return $product;
}

function createCategoryPage(Page $product, Page $categoriesParent, string $categoryTitle): Page {
	$pages = wire('pages');
	$sanitizer = wire('sanitizer');
	// ---------

	$category = $pages->get("template=category,title={$categoryTitle}");
	// bd($category, 'category exists?');
	if (empty($category->id)) {
		$category = new Page();
		$category->template = 'category';
		$category->parent = $categoriesParent;
		$category->title = $sanitizer->text($categoryTitle);
		$category->name = $sanitizer->pageName($category->title);
		$category->save();
		// bd($category, 'category NEW');
	}
	// add category to page
	if (!empty($category->id)) {
		// categories
		$productCategories = $product->categories;
		$productCategories->add($category);
		// -----
		$product->save();
	}


	// -----
	return $product;
}