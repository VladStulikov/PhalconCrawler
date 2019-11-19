<?php 

/*
 * Returns an array with the following keys
 * 
 * intLinksCount: number of links for the same website
 * extLinksCount: number of links to the other websites
 * intLinks:  array of internal links
 */

class LinksCrawlerModule implements ICrawlerModule {
    
    
    public function getName ()
    {
        return "links";
    }    
    
    public function handle ($url, $dom)
    {
        $result = array();
        
        $linkNodesList = $dom->getElementsByTagName('a');
        
        $intLinksList = array();
        $extLinksList = array();
        
        foreach($linkNodesList as $linkNode) {
            $link = $linkNode->getAttribute('href');
            
            if ($link == "/")
                continue;
            
            if (substr($link,0,1) == "/") {
                $fullURL = $url.$link;
                if (filter_var($fullURL, FILTER_VALIDATE_URL) !== FALSE)
                    $intLinksList[] = $fullURL;
            } else 
                if (filter_var($link, FILTER_VALIDATE_URL) !== FALSE)
                    $extLinksList[] = $link;
        }
        
        $intLinksList = array_unique($intLinksList);
        $extLinksList = array_unique($extLinksList);
        
        $result["intLinksCount"] = count($intLinksList);
        $result["extLinksCount"] = count($extLinksList);
        $result["intLinks"] = $intLinksList;
        
        return $result;
    }
   
}




