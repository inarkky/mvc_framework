<?php

namespace application\helpers;


class File 
{

    public $the_file;
    public $the_temp_file;
    public $validate_mime = true; 
    public $upload_dir;
    public $replace;
    public $do_filename_check;
    public $max_length_filename = 100;
    public $extensions;
    public $valid_mime_types = [
        '.bmp'  => 'image/bmp', 
        '.gif'  => 'image/gif', 
        '.jpg'  => 'image/jpeg', 
        '.jpeg' => 'image/jpeg', 
        '.pdf'  => 'application/pdf', 
        '.png'  => 'image/png', 
        '.zip'  => 'application/zip'
    ]; 
    public $ext_string;
    public $http_error;
    public $rename_file;
    public $file_copy; 
    public $message = [];
    public $create_directory = true;

    protected $fileperm = 0644;
    protected $dirperm = 0755; 
    

    public function file_upload() 
    {
        $this->rename_file = false;
        $this->ext_string = '';
    }

    public function show_error_string($br = '<br />') 
    {
        $msg_string = '';
        foreach ($this->message as $value) {
            $msg_string .= $value.$br;
        }
        return $msg_string;
    }

    public function set_file_name($new_name = '') 
    { 
        if ($this->rename_file) {
            if ($this->the_file == '') return;
            $name = ($new_name == '') ? strtotime('now') : $new_name;
            sleep(3);
            $name = $name.$this->get_extension($this->the_file);
        } else {
            $name = str_replace(' ', '_', $this->the_file);
        }
        return $name;
    }

    public function upload($to_name = '') 
    {
        if ($this->http_error > 0) {
            $this->message[] = $this->error_text($this->http_error);
            return false;
        } else {
            $new_name = $this->set_file_name($to_name);
            if ($this->check_file_name($new_name)) {
                if ($this->validateExtension($this->the_temp_file)) {
                    if (is_uploaded_file($this->the_temp_file)) {
                        $this->file_copy = $new_name;
                        if ($this->move_upload($this->the_temp_file, $this->file_copy)) {
                            $this->message[] = $this->error_text(0);
                            if ($this->rename_file) $this->message[] = $this->error_text(16);
                            return true;
                        }
                    } else {
                        $this->message[] = $this->error_text(7); 
                        return false;
                    }
                } else {
                    $this->show_extensions();
                    $this->message[] = $this->error_text(11);
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public function check_file_name($the_name) 
    {
        if ($the_name != '') {
            if (strlen($the_name) > $this->max_length_filename) {
                $this->message[] = $this->error_text(13);
                return false;
            } else {
                if ($this->do_filename_check == 'y') {
                    if (preg_match('/^([a-z0-9_\-]*\.?)\.[a-z0-9]{1,5}$/i', $the_name)) { 
                        return true;
                    } else {
                        $this->message[] = $this->error_text(12);
                        return false;
                    }
                } else {
                    return true;
                }
            }
        } else {
            $this->message[] = $this->error_text(10);
            return false;
        }
    }

    public function get_extension($from_file) 
    {
        $ext = strtolower(strrchr($from_file,'.'));
        return $ext;
    }

    public function validateMimeType($mime_type) 
    {
        $ext = $this->get_extension($this->the_file);
        if ($mime_type == $this->valid_mime_types[$ext]) {
            return true;
        } else {
            $this->message[] = $this->error_text(18);
            return false;
        }
    }

    public function validateExtension() 
    {
        $extension = $this->get_extension($this->the_file);
        $ext_array = $this->extensions;
        if (in_array($extension, $ext_array)) {
            if ($this->validate_mime) {
                if ($mime_type = $this->get_mime_type($this->the_temp_file)) {
                    if ($this->validateMimeType($mime_type)) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    $this->message[] = $this->error_text(18);
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    
    public function show_extensions() 
    {
        $this->ext_string = implode(' ', $this->extensions);
    }
    
    public function move_upload($tmp_file, $new_file) 
    {
        if ($this->existing_file($new_file)) {
            $newfile = $this->upload_dir.$new_file;
            if ($this->check_dir($this->upload_dir)) {
                if (move_uploaded_file($tmp_file, $newfile)) {
                    umask(0);
                    chmod($newfile , $this->fileperm);
                    return true;
                } else {
                    $this->message[] = $this->error_text(7); 
                    return false;
                }
            } else {
                $this->message[] = $this->error_text(14);
                return false;
            }
        } else {
            $this->message[] = $this->error_text(15);
            return false;
        }
    }
    
    public function check_dir($directory) 
    {
        if (!is_dir($directory)) {
            if ($this->create_directory) {
                umask(0);
                mkdir($directory, $this->dirperm);
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function existing_file($file_name) 
    {
        if ($this->replace == 'y') {
            return true;
        } else {
            if (file_exists($this->upload_dir.$file_name)) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function get_uploaded_file_info($name) 
    {
        $str = 'File name: '.basename($name).PHP_EOL;
        $str .= 'File size: '.filesize($name).' bytes'.PHP_EOL;
        if ($mimetype = get_mime_type($name)) {
            $str .= 'Mime type: '.$mimetype.PHP_EOL;
        }
        if ($img_dim = getimagesize($name)) {
            $str .= 'Image dimensions: x = '.$img_dim[0].'px, y = '.$img_dim[1].'px'.PHP_EOL;
        }
        return $str;
    }
    
    public function get_mime_type($file) 
    {
        $mtype = false;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $file);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mtype = mime_content_type($file);
        } 
        return $mtype;
    }
        
    public function del_temp_file($file)
    {
        $delete = @unlink($file); 
        clearstatcache();
        if (@file_exists($file)) { 
            $filesys = eregi_replace('/','\\',$file); 
            $delete = @system('del $filesys');
            clearstatcache();
            if (@file_exists($file)) { 
                $delete = @chmod ($file, 0644); 
                $delete = @unlink($file); 
                $delete = @system('del $filesys');
            }
        }
    }

    protected function error_text($err_num) 
    {
        $error[0] = 'File: <b>'.$this->the_file.'</b> successfully uploaded!';
        $error[1] = 'The uploaded file exceeds the max. upload filesize directive in the server configuration.';
        $error[2] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form.';
        $error[3] = 'The uploaded file was only partially uploaded';
        $error[4] = 'No file was uploaded';
        $error[6] = 'Missing a temporary folder. ';
        $error[7] = 'Failed to write file to disk. ';
        $error[8] = 'A PHP extension stopped the file upload. ';
        // end  http errors
        $error[10] = 'Please select a file for upload.';
        $error[11] = 'Only files with the following extensions are allowed: <b>'.$this->ext_string.'</b>';
        $error[12] = 'Sorry, the filename contains invalid characters. Use only alphanumerical chars and separate parts of the name (if needed) with an underscore. <br>A valid filename ends with one dot followed by the extension.';
        $error[13] = 'The filename exceeds the maximum length of '.$this->max_length_filename.' characters.';
        $error[14] = 'Sorry, the upload directory does not exist!';
        $error[15] = 'Uploading <b>'.$this->the_file.'...Error!</b> Sorry, a file with this name already exitst.';
        $error[16] = 'The uploaded file is renamed to <b>'.$this->file_copy.'</b>.';
        $error[17] = 'The file %s does not exist.';
        $error[18] = 'The file type (MIME type) is not valid.'; // new ver. 2.33
        $error[19] = 'The MIME type check is enabled, but is not supported.'; // new ver. 2.34
        
        return $error[$err_num];
    }
}

