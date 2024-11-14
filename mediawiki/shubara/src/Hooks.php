<?php
namespace MediaWiki\Extension\Shubara;

use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\RawPageViewBeforeOutputHook;
use MediaWiki\Hook\BeforePageDisplayHook;
use Parser;
use PPFrame;
use OutputPage;
use MediaWiki\Title\Title;
use MediaWiki\Html\Html;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use Skin; // MediaWiki skin
use InvalidArgumentException;
use BadFunctionCallException;
use NS_FILE;

class Hooks implements ParserFirstCallInitHook, BeforePageDisplayHook {

    /*
     Hook class implementations
    */

    public function onParserFirstCallInit( $parser ) {
        $parser->setHook( 'navcard', Hooks::renderTagNavCard(...) );
        $parser->setHook( 'navcards', Hooks::renderTagNavCards(...) );
		return true;
	}

    public function onBeforePageDisplay($out, $skin): void {
		$out->addModules( 'ext.shubara' );
	}

    // render <navcards>
    public function renderTagNavCards( $input, array $args, Parser $parser, PPFrame $frame ) {
        return htmlspecialchars( $input );
    }

    /**
     *
     * Render the navcard tag
     *
     * @param string $input What is supplied between the HTML tags
     * @param array $args HTML tag attribute params. All of them are optional, but with
     * caveats.
     * Valid params:
     * - page: a page name, with an optional namespace prefix.
     * - title-img: Image that is shown above the background in the center. If both
     * title-img and title-txt are present, none are rendered and instead an error is shown.
     * - title-txt: Text that is shown above the background in the center. If both
     * title-img and title-txt are present, none are rendered and instead an error is shown.
     * - bg-img: Image that is shown as the background.
     * - bg-tint%: How much the image is tinted (e.g. overlayed with black)
     *  @param Parser $parser MW Parser
     *  @param PPFrame $frame MW Frame
     */
    public function renderTagNavCard($input, array $args, Parser $parser, PPFrame $frame) {
        $navCardID = $this->generateRandomString(20); // styles specific to this navcard
        $output = '';

        if (isset($args['title-img']) xor isset($args['title-txt'])) {
            return 'Error! No title-img or title-txt present, or both are there at the same time';
        }
        $page = $args['page'];
        $titleType = '';
        $title = null;
        if (isset($args['title-img'])) {
            $title = $args['title-img'];
            $titleType = 'img';
        } else {
            $title = $args['title-txt'];
            $titleType = 'txt';
        }
        $bgImage = $args['bg-img'];
        $bgTintPercent = $args['bg-tint%'];

        // Generate and apply CSS

        $css = '';
        if ($titleType == 'img') {
            $titleFile = $this->getDirectFileURL($title);
            if (!$titleFile) { return "Error! Title image $title does not exist."; }
            $css .= "#ext-shubara-$navCardID { background-image: url(\"$titleFile\"); }";
        }
        if ($parser->getOptions()->getIsPreview()) {
            // embed as <style> because previews can't show what gets
            // inserted inside <head>
            global $wgShowDebug; // https://www.mediawiki.org/wiki/Manual:$wgShowDebug
            // disabled for production to save a few bytes on page size
            if ($css !== '') {
                if ($wgShowDebug) {
                    $output .= "<!-- Begin Extension:Shubara (Preview mode) -->";
                }
                $output .= "<style>$css</style>";
                if ($wgShowDebug) {
                    $output .= "<!-- End Extension:Shubara (Preview mode) -->";
                }
            }
        } else {
            $this->addHeadItem($parser, $css, 'css');
        }

        // Generate and apply JS
        $pageTitle = Title::newFromText(trim($page));
        // FIXME: Make it just a "red link" or smth
        if (!$pageTitle) { throw new InvalidArgumentException('Supplied page does not exist');}
        $pageURL = $pageTitle->getFullURL();
        // we use vanilla JS here because this is in the head of the document, we aint having jquery here
        // TODO: make this work with jQuery
        $js = "document.getElementById(\"ext-shubara-$navCardID\").addEventListener(\"click\", function(){window.location=\"$pageURL\"})";
        $this->addHeadItem($parser, $js, 'javascript');

        $output .= "<button id=\"ext-shubara-$navCardID\" class=\"ext-shubara-navcard\">";
        $output .= htmlspecialchars($input);
        $output .= '</button>';
        return $output;
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
    
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
    
        return $randomString;
    }

    // made with help from the CSS extension
    function addHeadItem(Parser $parser, string $data, string $type) {
        $title = Title::newFromText($data);
        $headItem = '<!-- Begin Extension:Shubara -->';
        // Encode data URI and append link tag
		$dataPrefix = "data:text/$type;charset=UTF-8;base64,";
		$url = $dataPrefix . base64_encode( $data );

        switch ($type) {
            case "javascript":
                // $headItem .= Html::linkedScript( $url );
                // Do it like this instead of Html::linkedScript so I can add the defer
                $headItem .= Html::element('script', ['src' => $url, 'defer' => 'defer']);
                break;
            case "css": $headItem .= Html::linkedStyle( $url ); break;
            default: throw new InvalidArgumentException("Unexpected type, expected javascript or css, got $type");
        }
        $headItem .= '<!-- End Extension:Shubara -->';
		$parser->getOutput()->addHeadItem( $headItem );
    }

    function runWithExtension(string $ext, callback $callback) {
        if (ExtensionRegistry::getInstance()->isLoaded($ext)) {
            callback();
        } else {
            throw new BadFunctionCallException("Extension $ext is not loaded");
        }
    }

    function getDirectFileURL(string $file) {
        $fileTitle = Title::newFromText($file, NS_FILE);
        if (!($fileTitle && $fileTitle->exists())) {
            return false;
        }

        $file = MediaWikiServices::getInstance()->getRepoGroup()->findFile($fileTitle);
        if ($file) {
            return $file->getFullUrl();
        }
        return false;
    }
}
?>
