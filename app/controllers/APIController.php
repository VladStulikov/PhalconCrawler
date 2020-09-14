<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

class APIController extends Controller
{        
    private function crawlPage($rootURL, $url) 
    {
        $dom = new DOMDocument();
        
        $result = array();
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("User-agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:70.0) Gecko/20100101 Firefox/70.0"));
    
        $time_start = microtime(true); 
        $html = curl_exec($curl);
        $time_end = microtime(true);
        $result["loadTime"] = $time_end - $time_start;
        
        $curlError = curl_error($curl);
        
        if ($curlError)
            throw new Exception($curlError);
            
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);             
        
        if ($status >= 400)
            throw new Exception("HTTP code: ".$status);

        if (empty($html))    
            throw new Exception("Empty response for the page");
        
        if (!@$dom->loadHTML($html,LIBXML_PARSEHUGE))
            throw new Exception("Failed to parse the page");

        $crawlerModulesList = $this->config->path("crawler.modules");    
            
        foreach($crawlerModulesList as $clazz) {
            $reflect = new ReflectionClass($clazz);
            if($reflect->implementsInterface('ICrawlerModule')) {
                $crawlerModule = new $clazz();
                $moduleName = $crawlerModule->getName();
                
                if (key_exists($moduleName, $result))
                    throw new Exception ("Duplicating Crawler Modules Name: ".$moduleName);
                    
                    $result[$moduleName] = $crawlerModule->handle($rootURL, $url, $dom);
            }
        }
        
        $result["httpCode"] = $status;
        
        return $result;
    }
    
    public function crawlAction()
    {        
        try {
            $data = $this->request->getJsonRawBody();
            $urlToCrawl = $data->{"urlToCrawl"};
        } catch (Exception $e) {
            $this->response->setJsonContent(
                [
                    'url' => $urlToCrawl,    
                    'success' => false,
                    'status' => "Parameters expected are: urlToCrawl(string), nothing passed in",
                ]
            );
            return $this->response;
        }
     
        $pageStatuses = array();
        
        try {
            $rootPageInfo = $this->crawlPage($urlToCrawl, $urlToCrawl);
            
            $numOfPagesCrawled = 1;
            $numOfImages = $rootPageInfo["imgCount"];
            $numOfIntLinks = $rootPageInfo["links"]["intLinksCount"];
            $numOfExtLinks = $rootPageInfo["links"]["extLinksCount"];
            $totalPageLoad = $rootPageInfo["loadTime"];
            $totalWordCount = $rootPageInfo["wordsCount"];
            $totalTitleLength = $rootPageInfo["titleLength"];            
            
            $internalLinks = $rootPageInfo["links"]["intLinks"];
            $numOfInternalLinks = count($internalLinks);
            
            $pageStatuses[] = array (
                "url" => $urlToCrawl,
                "success" => true,
                "message" => "HTTP code: ".$rootPageInfo["httpCode"],
            );
            
            $limit = min($numOfInternalLinks, $this->config->path("crawler.maxPagesToCrawl"));
            
            $i = 1;

            while ($i < $limit) {
                try {
                    $numOfPagesCrawled++;
                    
                    $pageInfo = $this->crawlPage($urlToCrawl, $internalLinks[$i]);
                    
                    $numOfImages += $pageInfo["imgCount"];
                    $numOfIntLinks += $pageInfo["links"]["intLinksCount"];
                    $numOfExtLinks += $pageInfo["links"]["extLinksCount"];
                    $totalPageLoad += $pageInfo["loadTime"];
                    $totalWordCount += $pageInfo["wordsCount"];
                    $totalTitleLength += $pageInfo["titleLength"]; 
                    
                    $pageStatuses[] = array (
                        "url" => $internalLinks[$i],   
                        "success" => true,
                        "message" => "HTTP code: ".$pageInfo["httpCode"],
                    );
                    
                } catch(Exception $e) {
                    $pageStatuses[] = array (
                        "url" => $internalLinks[$i],   
                        "success" => false,
                        "message" => $e->getMessage(),    
                    );
                }
                $i++;
            }

            $this->response->setJsonContent(
                [
                    'url' => $urlToCrawl,
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
                    'url' => $urlToCrawl,
                    'success' => false,
                    'message' => $e->getMessage(),
                ]
            );               
        }
     
        return $this->response;
    }

}

