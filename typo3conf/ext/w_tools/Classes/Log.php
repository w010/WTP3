<?php


class tx_wtools_log  {

    protected $file = '';



    public function __construct($file) {
        $this->file = $file;
    }


    public function log($notice)    {
        $filePointer = fopen($this->file, "a");

        $logMsg = date('Y-m-d H:i:s') . "\t\t" . $notice . "\n";

        // co to ma powodować?
        rewind($filePointer);
        fwrite($filePointer, $logMsg);
        fclose($filePointer);
    }
    

}

?>