<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

class APIController extends Controller
{        
    private function crawlPage($url) 
    {
        $dom = new DOMDocument();
        
        $result = array();
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
        $time_start = microtime(true); 
        $html = curl_exec($curl);
        $time_end = microtime(true);
        $result["loadTime"] = $time_end - $time_start;
        
        $curlError = curl_error($curl);
        
        if ($curlError)
            throw new Exception($curlError. " for ".$url);
            
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);             
        
        if ($status != 200)
            throw new Exception("HTTP code: ".$status." for ".$url);

        if (empty($html))    
            throw new Exception("Empty response for ".$url);
        
        if (!@$dom->loadHTML($html))
            throw new Exception("Failed to parse ".$url);

        $crawlerModulesList = $this->config->path("crawler.modules");    
            
        foreach($crawlerModulesList as $clazz) {
            $reflect = new ReflectionClass($clazz);
            if($reflect->implementsInterface('ICrawlerModule')) {
                $crawlerModule = new $clazz();
                $moduleName = $crawlerModule->getName();
                
                if (key_exists($moduleName, $result))
                    throw new Exception ("Duplicating Crawler Modules Name: ".$moduleName);
                    
                $result[$moduleName] = $crawlerModule->handle($url, $dom);
            }
        }
        
        return $result;
    }
    
    public function crawlAction()
    {
        $pageStatuses = array();
        
        try {
            $rootPageInfo = $this->crawlPage($this->config->path("crawler.uriToCrawl"));
            
            $numOfPagesCrawled = 0;
            $numOfImages = $rootPageInfo["imgCount"];
            $numOfIntLinks = $rootPageInfo["links"]["intLinksCount"];
            $numOfExtLinks = $rootPageInfo["links"]["extLinksCount"];
            $totalPageLoad = $rootPageInfo["loadTime"];
            $totalWordCount = $rootPageInfo["wordsCount"];
            $totalTitleLength = $rootPageInfo["titleLength"];            
            
            $internalLinks = $rootPageInfo["links"]["intLinks"];
            $numOfInternalLinks = count($internalLinks);
            
            $limit = min($numOfInternalLinks, $this->config->path("crawler.maxPagesToCrawl"));
            
            $i = 0;

            while ($i < $limit) {
                try {
                    $pageInfo = $this->crawlPage($internalLinks[$i]);
                    $numOfPagesCrawled++;
                    $numOfImages += $pageInfo["imgCount"];
                    $numOfIntLinks += $pageInfo["links"]["intLinksCount"];
                    $numOfExtLinks += $pageInfo["links"]["extLinksCount"];
                    $totalPageLoad += $pageInfo["loadTime"];
                    $totalWordCount += $pageInfo["wordsCount"];
                    $totalTitleLength += $pageInfo["titleLength"]; 
                    
                    $pageStatuses[] = array (
                        "success" => true,
                        "status" => "Successfull",
                    );
                    
                } catch(Exception $e) {
                    $pageStatuses[] = array (
                        "success" => false,
                        "status" => $e->getMessage(),    
                    );
                }
                $i++;
            }

            $this->response->setJsonContent(
                [
                    'success' => true,
                    'status' => "Successfull",
                    'numOfPagesCrawled' => $numOfPagesCrawled,
                    'numOfImages' => $numOfImages,
                    'numOfIntLinks' => $numOfIntLinks,
                    'numOfExtLinks' => $numOfExtLinks,
                    'avgPageLoad' => round($totalPageLoad / $numOfPagesCrawled,2),
                    'avgWordCount' =>  round($totalWordCount / $numOfPagesCrawled,2),
                    'avgTitleLength' =>  round($totalTitleLength / $numOfPagesCrawled,2),
                    'pageStatuses' => $pageStatuses    
                ]
            );               
            
        } catch (Exception $e) {
            $this->response->setJsonContent(
                [
                    'success' => false,
                    'status' => $e->getMessage(),
                ]
            );               
        }
     
        return $this->response;
    }

}

