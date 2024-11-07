<?php
namespace MediaWiki\Extension\Shubara;

use MediaWiki\Context\RequestContext;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\RawPageViewBeforeOutputHook;
use MediaWiki\Parser as Parser;
use MediaWiki\Parser\PPFrame as PPFrame;

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
        // $out = $parser->getOutput();
        // $out->addInlineStyle( $cssString );
        $styleTag = generateRandomString(26);
        $output .= "<div class=\"mw-ext-shubara-navcard-$styletag\">";
        $output .= $input;
        $output .= '<p>';
        $output .= implode(" ", $args);
        $output .= '</p></div>';
        return htmlspecialchars( $output );
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
}

?>
