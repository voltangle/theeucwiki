<?php
namespace MediaWiki\Extension\Shubara\Tags;

use MediaWiki\Extension\Shubara\Utils;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\Title;

/**
 *
 * Render the navcard tag
 *
 * @param string $input What is supplied between the HTML tags. Ignored
 * @param array $args HTML tag attribute params. All of them are optional, but with
 * caveats.
 * Valid params:
 * - page: a page name, with an optional namespace prefix.
 * - title-img: Image that is shown above the background in the center. If both
 * title-img and title-txt are present, none are rendered and instead an error is shown.
 * - title-txt: Text that is shown above the background in the center. If both
 * title-img and title-txt are present, none are rendered and instead an error is shown.
 * - title-img-w: Width of the title image. Under the hood handled as a thumbnail.
 * - title-img-h: Height of the title image. Under the hood handled as a thumbnail.
 * - bg-img: Image that is shown as the background.
 * - bg-tint%: How much the image is tinted (e.g. overlayed with black)
 *  @param Parser $parser MW Parser
 *  @param PPFrame $frame MW Frame
 */
class Navcard {
    public static function run($input, array $args, Parser $parser, PPFrame $frame) {
        $navCardID = Utils::generateRandomString(10); // styles specific to this navcard
        $output = '';
    
        if (!(isset($args['title-img']) xor isset($args['title-txt']))) {
            return 'Error! No title-img or title-txt present, or both are there at the same time';
        }
        $page = @$args['page'];
        $titleType = '';
        $title = null;
        if (isset($args['title-img'])) {
            $title = @$args['title-img'];
            $titleType = 'img';
        } else {
            $title = $args['title-txt'] ?? null;
            $titleType = 'txt';
        }
        $bgImage = $args['bg-img'] ?? null;
        $titleWidth = $args['title-img-w'] ?? 200;
        $titleHeight = $args['title-img-h'] ?? null;
        // TODO: this code stinks
        if (!ctype_digit((string)$titleWidth)) {
            return "Error! Title width $titleWidth is invalid, should be a number";
        }
        if ($titleHeight !== null && !(ctype_digit($titleHeight) && (int) $titleHeight > 0)) {
            return "Error! Title height $titleHeight is invalid, should be a number";
        }
        $bgTintPercent = $args['bg-tint%'] ?? null;
    
        // Generate and apply CSS
    
        $css = '';
        if ($bgImage != null) {
            // FIXME: this returns the file with the "domain", it has to be only an
            // explicit path
            $bgFile = Utils::getDirectFileURL($bgImage, 600);
            if (!$bgFile) { return "Error! Image $bgImage does not exist."; }
            $css .= "#ext-shubara-$navCardID { background-image: url(\"$bgFile\"); }";
        }
        if ($parser->getOptions()->getIsPreview()) {
            // embed as <style> because previews can't show what gets
            // inserted inside <head>
            global $wgShowDebug; // https://www.mediawiki.org/wiki/Manual:$wgShowDebug
            // disabled for production to save a few bytes on page size
            if ($css != '') {
                if ($wgShowDebug) {
                    $output .= "<!-- Begin Extension:Shubara (Preview mode) -->";
                }
                $output .= "<style>$css</style>";
                if ($wgShowDebug) {
                    $output .= "<!-- End Extension:Shubara (Preview mode) -->";
                }
            }
        } else {
            Utils::addHeadItem($parser, $css, 'css');
        }
    
        $pageTitle = Title::newFromText($page);
        if (!is_object($pageTitle)) {
            return 'Supplied page does not exist or is invalid';
        }
        $pageURL = $pageTitle->getFullURL();
        // Generate and apply JS
        // we use vanilla JS here because this is in the head of the document, we aint having jquery here
        // TODO: make this work with jQuery
        $js = "document.getElementById(\"ext-shubara-$navCardID\").addEventListener(\"click\", function(){window.location=\"$pageURL\"})";
        Utils::addHeadItem($parser, $js, 'javascript');
    
        // Generate the HTML
        $output .= "<button id=\"ext-shubara-$navCardID\" class=\"ext-shubara-navcard\">";
        switch ($titleType) {
            case 'img':
                $titleFile = Utils::getDirectFileURL($title, $titleWidth, $titleHeight);
                if (!$titleFile) { return "Error! Title image $title does not exist."; }
                $output .= "<img src=\"$titleFile\" />";
                break;
            case 'txt':
                $output .= "<span>$title</span>";
                break;
        }
        $output .= '</button>';
        return $output;
    }
}
