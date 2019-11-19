<?php

/*
 * Returns the title length
 */

class TitlesCrawlerModule implements ICrawlerModule {
    
    
    public function getName ()
    {
        return "titleLength";
    }    
    
    public function handle ($url, $dom)
    {
        $titleElements = $dom->getElementsByTagName('title');
        
        if (empty($titleElements) || $titleElements->length == 0) 
            return 0;
            
        $title = $titleElements->item(0)->textContent;
        
        return strlen($title);
    }
   
}
