<?php

/*
 * Returns the number of words on the page
 */

class NumOfWordsCrawlerModule implements ICrawlerModule {
    
    
    public function getName ()
    {
        return "wordsCount";
    }    
    
    public function handle ($url, $dom)
    {
        $body = $dom->getElementsByTagName('body');
        
        if (empty($body)) 
            throw new Exception("Failed to get BODY element");

        if ($body->length == 0) 
            throw new Exception("Failed to get BODY element");
            
        $body = strip_tags($body->item(0)->textContent);
        
        return str_word_count($body);
    }
   
}


