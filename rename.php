<?php
require_once __DIR__ . '/config.php';

$dir = $PATH_GAMBAR_WISUDAWAN;

// Open a directory, and read its contents
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {

        $nomor = 1;

        while (($file = readdir($dh)) !== false) {

            if (is_dir($file)) {
                // directory don't rename
            } else {
                // Check for image extensions
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if (in_array($extension, ['jpg', 'jpeg', 'png', 'jfif'])) {
                    //split and rename use preg_split()
                    $pattern = "/_/";
                    $components = preg_split($pattern, $file);
                    
                    if (count($components) === 3) {
                        // ambil filename urutan 2 dari depan
                        $filenameOnly = trim($components[1]);

                        if (strlen($filenameOnly) === 10) {
                            $filefinish = $filenameOnly . '.' . $extension;
                            rename($dir . $file, $dir . $filefinish);

                            echo $nomor . ' [' . $file . '] => ' . $filefinish;
                        } else {
                            echo $nomor . ' [' . $file . '] => ' . $file . ' - filename length not 10';
                        }
                    } else {
                        if (count($components) > 3) {
                            // ambil filename urutan 2 dari belakang
                            $lastIndex = count($components) - 2;
                            $filenameOnly = trim($components[$lastIndex]);

                            if (strlen($filenameOnly) === 10) {
                                $filefinish = $filenameOnly . '.' . $extension;
                                rename($dir . $file, $dir . $filefinish);

                                echo $nomor . ' [' . $file . '] => ' . $filefinish;
                            } else {
                                echo $nomor . ' [' . $file . '] => ' . $file . ' - filename length not 10';
                            }
                        } else {
                            echo $nomor . ' [' . $file . '] => ' . $file . ' - insufficient components';
                            // echo $file;
                        }
                    }
                } else {
                    echo $nomor . ' [' . $file . '] => ' . $file . ' - not an image';
                }

                echo "<br>";
                $nomor++;
            }
            
        }
        closedir($dh);
    }
}
