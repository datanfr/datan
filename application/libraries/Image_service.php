<?php

class Image_service
{
    public function convert_to_webp_with_imagick($source_path, $destination_path, $quality = 80)
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

}