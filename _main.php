<?php
namespace ProcessWire;

/**
 * _main.php
 * Main markup file (multi-language)
 * MULTI-LANGUAGE NOTE: Please see the README.txt file
 *
 * This file contains all the main markup for the site and outputs the regions
 * defined in the initialization (_init.php) file. These regions include:
 *
 *   $title: The page title/headline
 *   $content: The markup that appears in the main content/body copy column
 *   $sidebar: The markup that appears in the sidebar column
 *
 * Of course, you can add as many regions as you like, or choose not to use
 * them at all! This _init.php > [template].php > _main.php scheme is just
 * the methodology we chose to use in this particular site profile, and as you
 * dig deeper, you'll find many others ways to do the same thing.
 *
 * This file is automatically appended to all template files as a result of
 * $config->appendTemplateFile = '_main.php'; in /site/config.php.
 *
 * In any given template file, if you do not want this main markup file
 * included, go in your admin to Setup > Templates > [some-template] > and
 * click on the "Files" tab. Check the box to "Disable automatic append of
 * file _main.php". You would do this if you wanted to echo markup directly
 * from your template file or if you were using a template file for some other
 * kind of output like an RSS feed or sitemap.xml, for example.
 *
 */
if ($config->ajax) {
	// HANDLE AJAX REQUESTS
	// we pass to a handler function
	// @note: we could access $input DIRECTLY in the function as well
	$out = handleAjaxRequests($input);
	echo $out;
	exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo _x('en', 'HTML language code'); ?>"
	:data-theme="$store.HtmxAlpineTailwindDemosStore.current_theme" x-data='HtmxAlpineTailwindDemosData' x-cloak>

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>
		<?php echo $title; ?>
	</title>
	<!-- <meta name="description" content="<?php echo $page->get('summary'); ?>" /> -->
	<!-- <link href="//fonts.googleapis.com/css?family=Lusitana:400,700|Quattrocento:400,700" rel="stylesheet" type="text/css" /> -->
	<!-- *** @NOTE: CDN ONLY FOR DEMO & NOT FOR PRODUCTION *** -->
	<!-- INCLUDE DAISY UI COMPONENTS (taiwlind component library) -->
	<link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet" type="text/css" />
	<!-- INCLUDE TAILWIND CSS -->
	<script src="https://cdn.tailwindcss.com"></script>
	<style type="text/tailwindcss">
		@layer utilities {
			.content-auto {
				content-visibility: auto;
			}
			/* remove browser increase/decrease markup from number inputs */
			input[type="number"]::-webkit-inner-spin-button,
			input[type="number"]::-webkit-outer-spin-button {
			-webkit-appearance: none;
			margin: 0;
		}
		}
		/* ALPINE JS attribute
		Hide a block of HTML until after Alpine is finished initializing its contents
		*/
		[x-cloak] { display: none !important; }
	</style>
	<script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
	<!-- INCLUDE ALPINE.JS -->
	<script src="https://unpkg.com/alpinejs" defer></script>
	<!-- INCLUDE HTMX -->
	<script src="https://unpkg.com/htmx.org@1.8.6"></script>
	<script src="<?php echo $config->urls->templates ?>scripts/tailwind.config.js"></script>
	<!-- INCLUDE CUSTOM CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates ?>styles/main.css" />
	<!-- INCLUDE CUSTOM JS -->
	<script src="<?php echo $config->urls->templates ?>scripts/main.js"></script>
	<?php


	?>

</head>

<body class="container mx-auto prose md:prose-lg lg:prose-xl max-w-none p-5">
	<header>
		<div class="mt-3 mb-5">
			<p>Theme</p>
			<select name="" id="" x-model="$store.HtmxAlpineTailwindDemosStore.current_theme">
				<template x-for="(theme, index) in getDaisyUIThemes()">
					<option :value="theme" x-text="theme" :selected="theme == getCurrentTheme()"></option>
				</template>
			</select>
		</div>
		<?php
		echo $breadcrumb;
		?>
	</header>


	<main id='main' class='px-4'>
		<h1 class="text-clifford">
			<?php
			echo $title;
			?>
		</h1>



		<?php
		// CONTENT
		echo $content;
		// CSRF FOR ANY FORMS
		echo $session->CSRF->renderInput();
		?>


	</main>

	<!-- footer -->
	<footer id='footer'>

		<?php

		?>


	</footer>

</body>

</html>