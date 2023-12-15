<?php // screenshot-page.php

// get the incoming image data sent by the AJAX request
$screenshotImage = $_POST["image"];
$mpId = $_POST["mpId"];

echo $mpId;

// remove image/jpeg from left side of image data and get the remaining part
$screenshotImage = explode(";", $screenshotImage)[1];

// remove base64 from left side of image data and get the remaining part
$screenshotImage = explode(",", $screenshotImage)[1];

// replace all spaces with plus sign (helpful for larger images)
$screenshotImage = str_replace(" ", "+", $screenshotImage);

// convert back from base64 format
$screenshotImage = base64_decode($screenshotImage);

// save the image as filename.jpeg
file_put_contents("../assets/imgs/deputes_ogp/original/ogp_deputes_" . $mpId . ".png", $screenshotImage);

// provide a response to the front-end
echo "Done";
