<?php

use Minifier\TinyMinify;

function getHtmlMinified($html){
  return TinyMinify::html($html);
}

?>
