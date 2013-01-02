<?php
/**
*  requires the htmldom parser library https://github.com/zmijevik/HTML-DOM-Parser.git
*/

class WikiScraper {

    private $wikiRootUrl = "http://en.wikipedia.org";
    private $searchUrl = "http://en.wikipedia.org/w/index.php?search=";
    private $resultFoundDiv = ".mw-content-ltr";
    private $otherResultsFoundDiv = ".mw-search-results"; 
    private $notFoundDiv = ".mw-search-nonefound";
    private $tempFilename = "temp.html";

    public function __construct($verbose = false) {
     
        $this->hdp = new simple_html_dom();
        $this->verbose = $verbose;
    }

    public function loadFindAndScrapeFromQuery($searchString) { 

        $this->searchUrl = "http://en.wikipedia.org/w/index.php?search=";
        $this->searchUrl .= rawurlencode($searchString);
        $this->generateAndLoadContents();
        $this->findAndScrape();
    }

    public function getScrapedContent() {
        if(!empty($this->scrapedContent))
        return $this->scrapedContent;
    }

    private function getTempFile() {
        return dirname(__FILE__) . '/' . $this->tempFilename;
    }

    private function findElement($element) {
        return (bool)(count($this->hdp->find( $element )));
    }

    private function otherResultsFound() {
        return $this->findElement( $this->otherResultsFoundDiv );
    }

    private function resultNotFound() {
        return $this->findElement( $this->notFoundDiv );
    }

    private function scrapeContent() {
        return $this->hdp->find( $this->resultFoundDiv, 0 )->innertext;
    }

    private function generateAndLoadContents($urlArg = null,$andScrape=false) {

        if(!empty($urlArg)) {
            exec('curl -L '. $urlArg . ' > ' . $this->getTempFile());
        }
        else {
            exec('curl -L '. $this->searchUrl . ' > ' . $this->getTempFile());
        }

        $file_contents = file_get_contents( $this->getTempFile() );

        if(empty($file_contents)) {

            if($this->verbose) {
                echo "\n its empty\n";
                var_dump($this->searchUrl);
            }
        }
        else {
 
            $this->hdp->load( $file_contents );
        }

        if($andScrape) $this->findAndScrape();
    }

    private function findAndScrape() {

        if($this->resultNotFound()) {

            if($this->verbose)
                echo "nothing found\n";

            $this->scrapedContent = null;
        }
        else if ($this->otherResultsFound()) {

            if($this->verbose)
                echo "first link\n";

            $this->scrapeFirstLink();
        }
        else {

            if($this->verbose)
                echo "all else\n";

            $this->scrapedContent = $this->scrapeContent();
        }
    }

    private function scrapeFirstLink() {

        $link = $this->wikiRootUrl . $this->hdp->find($this->otherResultsFoundDiv, 0)->find('a',0)->href;
        $this->generateAndLoadContents($link, true);
    }
}
