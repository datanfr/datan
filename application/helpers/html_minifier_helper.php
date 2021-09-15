<?php

// composer require pfaciana/tiny-html-minifier

use Minifier\TinyMinify;

function getHtmlMinified($html){
  return TinyMinify::html($html);
}

?>
