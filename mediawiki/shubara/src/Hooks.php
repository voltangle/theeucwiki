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

    // Render <navcard>
    public function renderTagNavCard($input, array $args, Parser $parser, PPFrame $frame) {
        $navCardID = $this->generateRandomString(20); // styles specific to this navcard
        $output = '';

        // Argument retrieval
        // FIXME: refactor without exceptions
        if (!isset($args['page'])) {
            throw new InvalidArgumentException('No page argument supplied');
        }
        if (!isset($args['title-image'])) {
            throw new InvalidArgumentException('No title-image argument supplied');
        }
        $page = $args['page'];
        $titleImage = $args['title-image'];

        // Generate and apply CSS
        $titleFile = $this->getDirectFileURL($titleImage);
        if (!$titleFile) { throw new InvalidArgumentException('Title image does not exist');}
        $css = "#ext-shubara-$navCardID { background-image: url(\"$titleFile\"); }";
        if ($parser->getOptions()->getIsPreview()) {
            // embed as <style> because previews can't show what gets
            // inserted inside <head>
            global $wgShowDebug; // https://www.mediawiki.org/wiki/Manual:$wgShowDebug
            // disabled for production to save a few bytes on page size
            if ($wgShowDebug) {
                $output .= "<!-- Begin Extension:Shubara (Preview mode) -->";
            }
            $output .= "<style>$css</style>";
            if ($wgShowDebug) {
                $output .= "<!-- End Extension:Shubara (Preview mode) -->";
            }
        } else {
            $this->addHeadItem($parser, $css, 'css');
        }

        // Generate and apply JS
        $title = Title::newFromText(trim($page));
        // FIXME: Make it just a "red link" or smth
        if (!$title) { throw new InvalidArgumentException('Supplied page does not exist');}
        $pageURL = $title->getFullURL();
        // FIXME: this is some NASTY code wtf
        
        /* $js = "(function() {
  var nTimer = setInterval(function() {
    if (window.jQuery) {
      \$(\"#ext-shubara-$navCardID\").click(function(){window.location=\"$pageURL\"});
      clearInterval(nTimer);
    }
  }, 100);
})();"; */
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
