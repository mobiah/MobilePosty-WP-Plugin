<?php
global $momo_visibleCats;
global $momo_visiblePages;
global $momo_visiblePosts;
global $momo_catParents;
global $momo_pageParents;
global $momo_postCats;

// the following variables all need to be declared as {} 
// if they are empty to force javascript to recognize them 
// as empty OBJECTS, not empty ARRAYS - (arrays are not associative)
?>
var momo_visibleCats = <?=( empty($momo_visibleCats) ? '{}' : json_encode($momo_visibleCats))?>;
var momo_visiblePages = <?=( empty($momo_visiblePages) ? '{}' : json_encode($momo_visiblePages))?>;
var momo_visiblePosts = <?=( empty($momo_visiblePosts) ? '{}' : json_encode($momo_visiblePosts))?>;
var momo_catParents = <?=( empty($momo_catParents) ? '{}' : json_encode($momo_catParents))?>;
var momo_pageParents = <?=( empty($momo_pageParents) ? '{}' : json_encode($momo_pageParents))?>;
var momo_postCats = <?=( empty($momo_postCats) ? '{}' : json_encode($momo_postCats))?>;
