<?php namespace ProcessWire;

// basic-page.php template file 

/** @var Page $page */

// Primary content is the page's body copy
$content = $page->get('body'); 

// If the page has children, then render navigation to them under the body.
// See the _func.php for the renderNav example function.
if($page->hasChildren) {
	$content .= renderNav($page->children);
}

// if the rootParent (section) page has more than 1 child, then render 
// section navigation in the sidebar (see _func.php for renderNavTree).
if($page->rootParent->hasChildren > 1) {
	$sidebar = renderNavTree($page->rootParent, 3); 
	// make any sidebar text appear after navigation
	$sidebar .= $page->get('sidebar'); 
}

