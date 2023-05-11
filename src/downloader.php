<?php
/*
    PHP simple file downloader - send file to download.
    version: 1.0
    release date: 12.5.2023
*/

// Namespace
namespace Rndoom04\downloader;

// Using
use Skyzyx\Components\Mimetypes\Mimetypes;

// Library
class downloader {
    // * Properties */
    private $filePointer;


    /* Methods */
    public function __construct ($filePointer) {
        if (!is_resource($filePointer)) {
            throw new \InvalidArgumentException("You must pass a file pointer to the ctor.");
        }

        $this->filePointer = $filePointer;
    }
    
    // Sends the download to the browser
    public function sendDownload (string $filename, bool $forceDownload = true) {
        if (headers_sent()) {
            throw new \RuntimeException("Cannot send file to the browser, since the headers were already sent.");
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: {$this->getMimeType($filename)}");

        if ($forceDownload) {
            header("Content-Disposition: attachment; filename=\"{$filename}\";" );
        } else {
            header("Content-Disposition: filename=\"{$filename}\";" );
        }

        header("Content-Transfer-Encoding: binary");
        header("Content-Length: {$this->getFileSize()}");

        @ob_clean();

        rewind($this->filePointer);
        fpassthru($this->filePointer);
    }
    
    // Returns the mime type of a file name
    private function getMimeType (string $fileName) : string {
        $fileExtension  = pathinfo($fileName, PATHINFO_EXTENSION);
        $mimeTypeHelper = Mimetypes::getInstance();
        $mimeType       = $mimeTypeHelper->fromExtension($fileExtension);

        return !is_null($mimeType) ? $mimeType : "application/force-download";
    }
    
    // Returns the file size of the file
    private function getFileSize () : int {
        $stat = fstat($this->filePointer);
        return $stat['size'];
    }

    // Creates a new file download from a file path
    public static function createFromFilePath (string $filePath) {
        if (!is_file($filePath)) {
            throw new \InvalidArgumentException("File does not exist.");
        } else if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("File to download is not readable.");
        }

        return new downloader(fopen($filePath, "rb"));
    }

    // Creates a new file download helper with a given content
    public static function createFromString (string $content) {
        $file = tmpfile();
        fwrite($file, $content);

        return new downloader($file);
    }
}
?>
