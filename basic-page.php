<?php
namespace ProcessWire;

// basic-page.php template file

/** @var Page $page */

// Primary content is the page's body copy
$content = $page->get('body');

// example dummy E-COMMERCE pages - @NOTE -> WILL FETCH FROM https://fakestoreapi.com/, & CREATE THE PAGES AND CATEGORIES!
// createDummyProductAndCategoryPages();