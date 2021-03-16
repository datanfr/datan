<?php
$file = '../application/helpers/utility_helper.php';
$content = file_get_contents($file);
$matches;
preg_match("/function getVersion\(\){\s*return '(.+)';\s*}/",$content, $matches);
$newGetVersion = str_replace($matches[1], ($matches[1] + 1), $matches[0]);
$content = preg_replace("/function getVersion\(\){\s*return '(.+)';\s*}/",$newGetVersion, $content);
$return = file_put_contents($file, $content);
