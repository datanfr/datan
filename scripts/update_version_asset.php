<?php
$file = '../application/helpers/utility_helper.php';
$content = file_get_contents($file);
$matches;
preg_match("/function get_version\(\){\s*return '(.+)';\s*}/",$content, $matches);
$newget_version = str_replace($matches[1], ($matches[1] + 1), $matches[0]);
$content = preg_replace("/function get_version\(\){\s*return '(.+)';\s*}/",$newget_version, $content);
$return = file_put_contents($file, $content);
