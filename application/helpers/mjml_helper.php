<?php

use Qferrer\Mjml\Renderer\ApiRenderer;

function getMjmlHtml($mjml){
  $renderer = new ApiRenderer($_SERVER['API_MJML_ID'], $_SERVER['API_MJML_SECRETE_KEY']);
  return $renderer->render($mjml);
}

?>
