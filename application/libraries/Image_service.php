<?php

class Image_service
{
    public function convert_to_webp($source_path, $destination_path, $quality = 80)
    {
        try {
            $image = new \Imagick($source_path);
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality($quality);
            $image->writeImage($destination_path);
            $image->clear();
            $image->destroy();
            return true;
        } catch (Exception $e) {
            log_message('error', 'Erreur Imagick : ' . $e->getMessage());
            return false;
        }
    }

    public function resize_image($source_path, $destination_path, $target_width){
        $imagick = new \Imagick($source_path);
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        $aspect_ratio = $height / $width;
        $target_height = intval($target_width * $aspect_ratio);

        $imagick->resizeImage($target_width, $target_height, \Imagick::FILTER_LANCZOS, 1);
        $imagick->writeImage($destination_path);
        $imagick->destroy();
    }

}