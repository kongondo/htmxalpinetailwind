<?php namespace ProcessWire;

// sitemap.php template file
// Generate navigation that descends up to 4 levels into the tree.
// See the _func.php for the renderNav() function definition. 

/** @var Page $homepage Variable defined in _init.php */

$content = renderNavTree($homepage, 4); 

