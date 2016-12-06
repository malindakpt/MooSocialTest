<?php 
class ChatErrorsController extends ChatAppController{
    public function admin_index()
    {
    }
    public function admin_download()
    {
        $file = __DIR__.DS."..".DS."webroot".DS."js".DS."server".DS."log".DS."error-all.log";
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);

        }
        exit;
    }
}