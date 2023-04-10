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
	// bd($input, '$input');
	$out = "<p>Sorry, we were not able to handle your request.</p>";
	# *** DETERMINE HANDLER TO USE *** #
	// htmx_alpine_tailwind_demos
	if (!empty($input->get('htmx_alpine_tailwind_demos'))) {
		// HANDLE SWITCH DEMO
		// bd($input->get('htmx_alpine_tailwind_demos'), __METHOD__ . ': $input->get(\'htmx_alpine_tailwind_demos\') at line #' . __LINE__);
		// @note: no swap when switching demos; so no output
		processSwitchDemo($input->get('htmx_alpine_tailwind_demos'));
	} elseif (!empty($input->get('htmx_alpine_tailwind_demos_get_buy_now_product_id'))) {
		// HANDLE BUY NOW REQUEST (update cart)
		$out = processBuyNowAction((int) $input->get('htmx_alpine_tailwind_demos_get_buy_now_product_id'));
	}

	// ------
	return $out;
}

function processBuyNowAction($productID) {
	// bd($productID, __METHOD__ . ': $productID at line #' . __LINE__);
	$productID = (int) $productID;

	// @NOTE: JUST FOR THE DEMOS, WE CHECK THE SESSION FOR THE CURRENTLY SELECTED DEMO
	// THIS WILL ALLOW US TO TAILOR OUR RESPONSE, e.g. server-side rendered (near) full modal markup vs
	// MINIMAL MARKUP FROM SERVER ONLY FOR 'success' notices

	$currentDemo = getDemoFromSession();
	bd($currentDemo, __METHOD__ . ': $currentDemo at line #' . __LINE__);
	// @TODO @NOTE: FOR NOW, WE JUST CHECK DEMO NAMES HERE; IDEALLY, WE SHOULD BE FETCHING THE RESPONSE FROM THE DEMO RENDERER FILES THEMESELVES?
	// demo_alpine_renders_modal OR demo_htmx_renders_modal

	// get options for current demo
	$demoOptions = getDemoByKey($currentDemo);
	bd($demoOptions, __METHOD__ . ': $demoOptions at line #' . __LINE__);
	// get full path to current demo's renderer file
	$demoFilePath = getDemoFilePath($demoOptions);
	bd($demoFilePath, __METHOD__ . ': $demoFilePath at line #' . __LINE__);
	// include the file to get the variable with contents for the modal
	// $isIncludedFile = wire('files')->render($demoFilePath);
	require_once($demoFilePath);
	// bd($isIncludedFile, __METHOD__ . ': $isIncludedFile at line #' . __LINE__);
	// bd($modalOutput, __METHOD__ . ': $modalOutput at line #' . __LINE__);

	$out = $modalOutput;



	// ------
	return $out;
}

function processSwitchDemo($selectedDemo) {
	// bd($selectedDemo, __METHOD__ . ': $selectedDemo at line #' . __LINE__);
	$isValidDemo = isValidDemo($selectedDemo);
	if ($isValidDemo) {
		// set demo to session
		setDemoToSession($selectedDemo);
		// get the demo options to see if we need a redirect
		$demosList = getDemosList();
		$demoOptions = $demosList[$selectedDemo];
		// bd($demosList, __METHOD__ . ': $demosList at line #' . __LINE__);
		// bd($demoOptions, __METHOD__ . ': $demoOptions at line #' . __LINE__);
		// $redirect = "";

		if (!empty($demoOptions['redirect'])) {
			$pages = wire('pages');
			// bd($demoOptions['redirect'], __METHOD__ . ': $demoOptions[\'redirect\'] - REDIRECTING TO THIS LOCATION - at line #' . __LINE__);
			$redirect = $demoOptions['redirect'];
			// bd($redirect, __METHOD__ . ': $redirect at line #' . __LINE__);
			if (is_int($redirect)) {
				$pageID = $redirect;
				// bd($pageID, __METHOD__ . ': $pageID at line #' . __LINE__);
				$redirectHeader = $pages->get($pageID)->url;
				// bd($redirectHeader, __METHOD__ . ': $redirectHeader at line #' . __LINE__);

			} else {
				// @TODO - OK? MULTILINGUAL?
				// redirect is the 'name' or 'title' of the page
				$name = wire('sanitizer')->pageName($redirect);
				$redirectHeader = $pages->get("name={$name}")->url;
			}

			// bd($redirectHeader, __METHOD__ . ': $redirectHeader at line #' . __LINE__);

			header("HX-Redirect: {$redirectHeader}");
		}
	}
}

function setDemoToSession(string $selectedDemo): void {
	wire('session')->set('htmxalpinetailwindproductsselectedDemo', $selectedDemo);
	// bd($selectedDemo, __METHOD__ . ': $selectedDemo at line #' . __LINE__);
}

function getDemoFromSession(): string {
	$currentDemo = wire('session')->get('htmxalpinetailwindproductsselectedDemo');
	// bd($currentDemo, __METHOD__ . ': $currentDemo at line #' . __LINE__);
	return $currentDemo;
}

function getDemoByKey($demoKey) {
	$demosList = getDemosList();
	$demoOptions = null;
	if (!empty($demosList[$demoKey])) {
		$demoOptions = $demosList[$demoKey];
	}
	// -----
	return $demoOptions;
}

function getDemoFilePath(array $demoOptions): string {

	$demoFilename = $demoOptions['file'];
	$demoFilePath = wire('config')->templates->path . "prepend/{$demoFilename}.php";
	// -----
	return $demoFilePath;
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

// @TODO HARDCODED FOR NOW; in future might use a 'demos' folder
function getDemosList(): array {
	$demosList = [
		'demo_alpine_renders_modal' => [
			'label' => 'Alpine.js Renders Modal',
			'file' => 'products-alpine-renders-modal',
			'description' => 'Product details in the buy now modal are rendered from client-side using Alpine.js. The data is pre-populated when the products page is rendered. This data contains details of product variants where applicable.',
			// we need to redirect in order to have the related 'render file' to be rendered
			'redirect' => 'products' // products page
		],
		'demo_htmx_renders_modal' => [
			'label' => 'htmx Renders Modal',
			'file' => 'products-htmx-renders-modal',
			'description' => 'Product details in the buy now modal are fetched from the server when the modal is opened. The fetching is done via htmx which sends the ID of the product to the server using a get request. On the server, the request is pre-processed. Processing also determines if the product has variants. The returned markup contains Aline.js markup as well. When the modal is closed, Alpine.js clears the previous markup.',
			// we need to redirect in order to have the related 'render file' to be rendered
			'redirect' => 'products' // products page
		],
		'demo_htmx_redirect_from_server' => [
			'label' => 'htmx Redirect from Server',
			'file' => null,
			// none needed; we are just
			// @see: processSwitchDemo() for how we set the redirect via PHP header
			'description' => 'Not a display demo. Shows how to redirect from the backend using htmx.',
			'redirect' => 1 // ProcessWire home page
		],
	];

	// ------
	return $demosList;
}

function renderDemosSelectMarkup() {
	$demosList = getDemosList();
	$currentDemo = getDemoFromSession();
	bd($currentDemo, __METHOD__ . ': $currentDemo at line #' . __LINE__);
	// @note: no swapping here; this is just for switiching demos.
// @note: some demos redirect!
// @note: home page handles the ajax requests
	$out = "<select name='htmx_alpine_tailwind_demos'  hx-get='/' hx-swap='none' >";
	// empty option
	// @TODO SET A DEFAULT ONE?
	$out .= "<option value='0' xxdisabled>Select a demo</option>";
	// @note: $key will be the session value we set
	foreach ($demosList as $key => $option) {
		$selected = $key === $currentDemo ? ' selected' : '';
		$out .= "<option value='{$key}'{$selected}>{$option['label']}</option>";
	}

	// -----
	$out .= "</select>";
	// -------
	return $out;
}

function isValidDemo(string $selectedDemo): bool {
	// is the htmx-requested demo a viald one?
	// @note: protects against client-side manipulation
	$demosList = getDemosList();
	$allowedValues = array_keys($demosList);
	$isValidDemo = !empty(wire('sanitizer')->option($selectedDemo, $allowedValues));
	return $isValidDemo;
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