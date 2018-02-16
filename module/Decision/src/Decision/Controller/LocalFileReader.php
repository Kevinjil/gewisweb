<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Decision\Controller;

/**
 * Description of LocalFileReader
 *
 * @author s134399
 */
class LocalFileReader implements FileReader {

    /**
     * The location in the local filesystem that is considered the 'root' for this browser
     * @var string
     */
    static private $root;

    public function __construct($root) {
        $this->root = $root;
        //var_dump(getcwd());
    }

    public function downloadFile($path) {
        $fullPath = $this->root . $path;
        if (!is_file($fullPath)){
            //var_dump($fullPath . ' is no file');
            return null;
        }
        //var_dump($fullPath . ' is a file');
        $response = new \Zend\Http\Response\Stream();
        $response->setStream(fopen('file://' . $fullPath, 'r'));
        $response->setStatusCode(200);
        $headers = new \Zend\Http\Headers();
        $headers->addHeaderLine('Content-Type', 'application/octet-stream')
                ->addHeaderLine('Content-Disposition', 'attachment; filename="' . end(explode('/',$fullPath)) . '"')
                ->addHeaderLine('Content-Length', filesize($fullPath));
        $response->setHeaders($headers);
        return $response;
    }

    public function listDir($path) {
        //remove the trailing slash from the dir
        $fullPath = $this->root . $path;
        if (!is_dir($fullPath)){
            var_dump($fullPath . ' is no dir');
            return null;
        }
        //We must insert an additional /, except when when $path is the root
        $delimiter = $path !== '' ? '/': '';

        var_dump($fullPath . ' is a dir');
        $dircontents = scandir($fullPath);
        var_dump($dircontents);
        $files = [];
        foreach ($dircontents as $dircontent){
            if ($dircontent[0]==='.'){
                continue;
            }
            if (is_dir($fullPath . '/' . $dircontent)){
                $kind = 'dir';
            }
            elseif (is_file($fullPath . '/' . $dircontent)){
                $kind = 'file';
            }
            else{
                //Ignore all strange filesystem thingies like symlinks and such
                continue;
            }

            $files[] = new FileNode($kind, htmlspecialchars($path . $delimiter . $dircontent), htmlspecialchars($dircontent));
        }
        return $files;
    }

    public function isDir($path) {
        return is_dir($this->root . $path);
    }

}
