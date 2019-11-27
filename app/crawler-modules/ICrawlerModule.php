<?php

interface ICrawlerModule {
    /* 
     * returns a unique name of the crawler module
     */
    public function getName();
    /*
     *  handles data passed and returns a result
     *  accepts: $url - URL to be crawled
     *           $dom - parsed HTML document
     */
    public function handle($rootURL, $url, $dom);    
}
