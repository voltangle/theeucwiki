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
		$out->addModuleStyles( 'ext.shubara.styles' );
	}

    /**
     * Render the navcards tag
     *
     * Can cause some undefined behavior if used with anything else than a navcard tag
     * inside, because it does additional processing to the input
     * Right now, the only thing we do is removing all the newlines, so p tags don't
     * get placed wrapping the navcard tags.
     *
     * @param string $input What is supplied between the HTML tags. This gets evaluated
     * so it get spit out as normal wikitext
     * @param array $args HTML tag attribute params. Ignored
     * @param Parser $parser MediaWiki Parser object
     * @param PPFrame $frame MediaWiki PPFrame object
     */
    public function renderTagNavCards( $input, array $args, Parser $parser, PPFrame $frame ) {
        $output = '<div class="ext-shubara-navcards">';
        $output .= $parser->recursiveTagParse(str_replace("\n", '', $input), $frame);
        $output .= '</div>';
        return $output;
    }

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
    public function renderTagNavCard($input, array $args, Parser $parser, PPFrame $frame) {
        $navCardID = $this->generateRandomString(20); // styles specific to this navcard
        $output = '';

        if (!(isset($args['title-img']) xor isset($args['title-txt']))) {
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
        $titleWidth = $args['title-img-w'] ?? 200;
        $titleHeight = $args['title-img-h'];
        // TODO: this code stinks
        if (!ctype_digit((string)$titleWidth)) {
            return "Error! Title width $titleWidth is invalid, should be a number";
        }
        if ($titleHeight !== null && !(ctype_digit($titleHeight) && (int) $titleHeight > 0)) {
            return "Error! Title height $titleHeight is invalid, should be a number";
        }
        $bgTintPercent = $args['bg-tint%'];

        // Generate and apply CSS

        $css = '';
        if ($bgImage != null) {
            // FIXME: this returns the file with the "domain", it has to be only an
            // explicit path
            $bgFile = $this->getDirectFileURL($bgImage, 600);
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
            $this->addHeadItem($parser, $css, 'css');
        }

        // Generate and apply JS
        $pageTitle = Title::newFromText($page);
        if (!is_object($pageTitle)) {
            return 'Supplied page does not exist or is invalid';
        }
        $pageURL = $pageTitle->getFullURL();
        // we use vanilla JS here because this is in the head of the document, we aint having jquery here
        // TODO: make this work with jQuery
        $js = "document.getElementById(\"ext-shubara-$navCardID\").addEventListener(\"click\", function(){window.location=\"$pageURL\"})";
        $this->addHeadItem($parser, $js, 'javascript');

        // Generate the HTML
        $output .= "<button id=\"ext-shubara-$navCardID\" class=\"ext-shubara-navcard\">";
        switch ($titleType) {
            case 'img':
                $titleFile = self::getDirectFileURL($title, $titleWidth, $titleHeight);
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

    function generateRandomString($length = 10): string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
    
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
    
        return $randomString;
    }

    // made with help from the CSS extension
    function addHeadItem(Parser $parser, string $data, string $type): void {
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

    /*
     * Returns a direct URL to a file with a specified name. Optionally can resize it
     * to a thumbnail of specified size (see File::createThumb in MediaWiki docs) if the
     * file is an image.
     *
     * @param string $file Filename to look for
     * @param ?int $width Image thumbnail width.
     * @param ?int $height Image thumbnail height.
     *
     * @return ?string File path or null in case of error.
     */
    function getDirectFileURL(
            string $file,
            ?int $width = null,
            ?int $height = null
    ): ?string {
        $fileTitle = Title::newFromText($file, NS_FILE);
        if (!($fileTitle && $fileTitle->exists())) {
            return null;
        }

        $file = MediaWikiServices::getInstance()->getRepoGroup()->findFile($fileTitle);
        if ($file) {
            if (!$width) {
                return $file->getFullUrl();
            } else {
                return $file->createThumb($width, $height ?? -1);
            }
        }
        return null;
    }
}
?>
