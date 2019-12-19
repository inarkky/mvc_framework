<?php

namespace application\helpers;


use application\helpers\File;

class Image extends File 
{
    public $x_size;
    public $y_size;
    public $x_max_size = 300;
    public $y_max_size = 200;
    public $x_max_thumb_size = 110;
    public $y_max_thumb_size = 88;
    public $thumb_folder;
    public $foto_folder;
    public $larger_dim;
    public $larger_curr_value;
    public $larger_dim_value;
    public $larger_dim_thumb_value;
    
    private $use_image_magick = false; 
    
    public function process_image($landscape_only = false, $create_thumb = false, $delete_tmp_file = false, $compression = 85) 
    {
        $filename = $this->upload_dir.$this->file_copy;
        
        $this->check_dir($this->thumb_folder); 
        $this->check_dir($this->foto_folder); 
        
        $thumb = $this->thumb_folder.$this->file_copy;
        $foto = $this->foto_folder.$this->file_copy;
        
        if ($landscape_only) {
            $this->get_img_size($filename);
            if ($this->y_size > $this->x_size) {
                $this->img_rotate($filename, $compression);
            }
        }

        $this->check_dimensions($filename); 
        
        if ($this->larger_curr_value > $this->larger_dim_value) {
            $this->thumbs($filename, $foto, $this->larger_dim_value, $compression);
        } else {
            copy($filename, $foto);
        }
        
        if ($create_thumb) {
            if ($this->larger_curr_value > $this->larger_dim_thumb_value) {
                $this->thumbs($filename, $thumb, $this->larger_dim_thumb_value, $compression); 
            } else {
                copy($filename, $thumb);
            }
        }
        
        if ($delete_tmp_file) $this->del_temp_file($filename); 
    }

    public function get_img_size($file) 
    {
        $img_size = getimagesize($file);
        $this->x_size = $img_size[0];
        $this->y_size = $img_size[1];
    }

    public function check_dimensions($filename) 
    {
        $this->get_img_size($filename);
        $x_check = $this->x_size - $this->x_max_size;
        $y_check = $this->y_size - $this->y_max_size;

        if ($x_check < $y_check) {
            $this->larger_dim = "y";
            $this->larger_curr_value = $this->y_size;
            $this->larger_dim_value = $this->y_max_size;
            $this->larger_dim_thumb_value = $this->y_max_thumb_size;
        } else {
            $this->larger_dim = "x";
            $this->larger_curr_value = $this->x_size;
            $this->larger_dim_value = $this->x_max_size;
            $this->larger_dim_thumb_value = $this->x_max_thumb_size;
        }
    }

    public function img_rotate($wr_file, $comp) 
    {
        $new_x = $this->y_size;
        $new_y = $this->x_size;

        if ($this->use_image_magick) {
            exec(sprintf("mogrify -rotate 90 -quality %d %s", $comp, $wr_file));
        } else {
            $src_img = imagecreatefromjpeg($wr_file);
            $rot_img = imagerotate($src_img, 90, 0);
            $new_img = imagecreatetruecolor($new_x, $new_y);
            imageantialias($new_img, TRUE);
            imagecopyresampled($new_img, $rot_img, 0, 0, 0, 0, $new_x, $new_y, $new_x, $new_y);
            imagejpeg($new_img, $this->upload_dir.$this->file_copy, $comp);
        }
    }

    public function thumbs($file_name_src, $file_name_dest, $target_size, $quality = 80) 
    {
        $size = getimagesize($file_name_src);

        if ($this->larger_dim == "x") {
            $w = number_format($target_size, 0, ',', '');
            $h = number_format(($size[1]/$size[0])*$target_size,0,',','');
        } else {
            $h = number_format($target_size, 0, ',', '');
            $w = number_format(($size[0]/$size[1])*$target_size,0,',','');
        }
        
        if ($this->use_image_magick) {
            exec(sprintf("convert %s -resize %dx%d -quality %d %s", $file_name_src, $w, $h, $quality, $file_name_dest));
        } else {
            $dest = imagecreatetruecolor($w, $h);
            imageantialias($dest, TRUE);
            $src = imagecreatefromjpeg($file_name_src);
            imagecopyresampled($dest, $src, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);
            imagejpeg($dest, $file_name_dest, $quality);
        }
    }

}

