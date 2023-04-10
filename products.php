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

$breadcrumb = buildBreadCrumb($page);

$demoSession = $session->get('htmxalpinetailwindproductsselectedDemo');
$demoRender = 'demo_alpine_renders_modal';
$demoRenderFileName = 'alpine_renders_modals/products-alpine-renders-modal';
// @TODO CHANGE THIS TO GET FROM functions!
// if ($demoSession === 'demo_htmx_renders_modal') {
// 	$demoRenderFileName = 'htmx_renders_modals/products-htmx-renders-modal';
// }
// bd($demoSession, __METHOD__ . ': $demoSession at line #' . __LINE__);
// ------
// LOAD CONTENT FOR PRODUCTS @update @TODO WE NOW GET THE VARIABLES INSTEAD!
// $content = $files->render("{$config->templates->path}demos/{$demoRenderFileName}.php");
// @note: this will populate $content and other variables as needed
$demoRenderFilePath = getDemoFilePathFromSession();
// bd($demoRenderFilePath, __METHOD__ . ': $demoRenderFilePath at line #' . __LINE__);
// bd(wire('files')->exists($demoRenderFilePath), __METHOD__ . ': wire(\'files\')->exists($demoRenderFilePath) at line #' . __LINE__);
require_once($demoRenderFilePath);