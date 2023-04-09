<?php
namespace ProcessWire;


// @TODO SINGLE PRODUCT OR PAGE DEMO



$breadcrumb = buildBreadCrumb($page);

$imageMarkup = '<p>No product image</p>';
if ($page->images->count()) {
  $image = $page->images->first();
  $thumb = $image->width(500);
  $imageMarkup = "<img src='{$thumb->url}' alt='$image->description' class='px-4'>";
}

$content =
  "<div class='grid gap-4 grid-cols-4'>" .
  "<div class='col-span-full md:col-span-1'><p>{$imageMarkup}</p></div>" .
  "<div class='md:col-span-3'>{$page->body}</div>" .
  "</div>";