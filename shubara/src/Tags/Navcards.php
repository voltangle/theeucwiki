<?php
namespace MediaWiki\Extension\Shubara\Tags;

use MediaWiki\Extension\Shubara\Utils;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

/**
 * Render the navcards tag
 *
 * Can cause some undefined behavior if used with anything else than a navcard or
 * ulnav tag inside, because it does additional processing to the input
 *
 * @param string $input What is supplied between the HTML tags. This gets evaluated
 * so it get spit out as normal wikitext
 * @param array $args HTML tag attribute params. Valid params:
 * - out-mode: how it works. 'noreturn' by default. Valid values: noreturn, raw
 * - flex: sets the flex CSS value
 * - no-min-width: unsets the min-width CSS style on the children
 * @param Parser $parser MediaWiki Parser object
 * @param PPFrame $frame MediaWiki PPFrame object
 */
class Navcards {
    public static function run( $input, array $args, Parser $parser, PPFrame $frame ) {
        $output = '<div class="ext-shubara-navcards"';
        if (isset($args['flex']) && is_numeric(@$args['flex'])) {
            $flex = @$args['flex'];
            $output .= " style=\"flex: $flex;\"";
        }
        $output .= '>';
    
        if (isset($args['no-min-width'])) {
            // i've set !important here just to be safe
            $css = '.ext-shubara-navcard { min-width: unset !important; }';
    
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
        }
    
        switch ($args['out-mode'] ?? 'noreturn') {
            case 'raw': $output .= $parser->recursiveTagParse($input, $frame); break;
            case 'noreturn':
                $output .= $parser->recursiveTagParse(str_replace("\n", '', $input), $frame);
                break;
        }
    
        $output .= '</div>';
        return $output;
    }
}
