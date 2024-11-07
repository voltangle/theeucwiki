<?php
namespace MediaWiki\Extension\Shubara;

use RequestContext;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\RawPageViewBeforeOutputHook;
use Parser;
use PPFrame;
use MediaWiki\Extension\CSS\Hooks as CSSExtHooks;
use MediaWiki\Title\Title;
use MediaWiki\Html\Html;

class Hooks implements ParserFirstCallInitHook {
    public function onParserFirstCallInit( $parser ) {
        $parser->setHook( 'navcard', Hooks::renderTagNavCard(...) );
        $parser->setHook( 'navcards', Hooks::renderTagNavCards(...) );
		return true;
	}

    // render <navcards>
    public function renderTagNavCards( $input, array $args, Parser $parser, PPFrame $frame ) {
        return htmlspecialchars( $input );
    }

    // Render <navcard>
    public function renderTagNavCard( $input, array $args, Parser $parser, PPFrame $frame ) {
        $styleTag = $this->generateRandomString(26);
        $this->addStyles($parser, ".mw-ext-shubara-navcard-$styleTag { background-color: blue; }");
        $output .= "<div class=\"mw-ext-shubara-navcard-$styleTag\">";
        $output .= htmlspecialchars($input);
        $output .= '<p>';
        $output .= implode(" ", $args);
        $output .= '</p></div>';
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
    function addStyles(Parser $parser, string $css) {
        $title = Title::newFromText($css);
        $headItem = '<!-- Begin Extension:Shubara -->';
        # Encode data URI and append link tag
		$dataPrefix = 'data:text/css;charset=UTF-8;base64,';
		$url = $dataPrefix . base64_encode( $css );

		$headItem .= Html::linkedStyle( $url );
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
}
?>
