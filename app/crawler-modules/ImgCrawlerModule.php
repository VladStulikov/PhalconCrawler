<?php

/*
 * Returns the number of images on the page
 */

class ImgCrawlerModule implements ICrawlerModule {
    
    
    public function getName ()
    {
        return "imgCount";
    }    
    
    public function handle ($url, $dom)
    {
        $linkNodesList = $dom->getElementsByTagName('img');
        
        $imageSrcList = array();
        
        foreach($linkNodesList as $linkNode) {
             $imageSrcList[] = $linkNode->getAttribute('src');  
        }
        $imageSrcList = array_unique($imageSrcList);
        
        return count($imageSrcList);
    }
   
}