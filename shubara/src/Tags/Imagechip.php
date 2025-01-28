<?php
namespace MediaWiki\Extension\Shubara\Tags;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Html\Html;
use MediaWiki\Extension\Shubara\Utils;

define("HEX_COLOR_REGEX", '/^#(?:[0-9a-fA-F]{3}){1,2}$/');

/**
* Render the imagechip tag
*/
// TODO: finish this
class Imagechip {
    /**
     * @param string $input What is supplied between the HTML tags. This gets evaluated
     * so it get spit out as normal wikitext
     * @param array $args HTML tag attribute params. Valid params:
     * - background-color: a hex color for the card. #DEADBE by default
     * - txt-color: a hex color for the text. white (#FFFFFF) by default
     * - href: where the button redirects
     * - target: link type, internal or external
     * - flex: sets the flex CSS value
     * @param Parser $parser MediaWiki Parser object
     * @param PPFrame $frame MediaWiki PPFrame object
     */
    public static function run($input, array $args, Parser $parser, PPFrame $frame) {
        $id = Utils::generateRandomString();
        $htmlAttributes = [
            'class' => 'ext-shubara-imagechip',
            'id' => "ext-shubara-$id",
        ];
        $styles = [];
        $content = '';
        
        $mode = $args['mode'] ?? 'col';
        // BUG: this is a GLARING SECURITY ISSUE you can just slap any CSS in there and
        // it will work. I will have to add some sanitization later on
        $backgroundColor = $args['background-color'] ?? '#DEADBE';
        array_push($styles, "background-color: $backgroundColor;");
        $borderColor = Utils::adjustBrightness($backgroundColor, 0.2);
        array_push($styles, "border: 4px solid $borderColor;");
        $txtColor = $args['txt-color'];
        $txtColorValid = preg_match(HEX_COLOR_REGEX, $txtColor);
        if ($txtColor != null && $txtColorValid) {
            array_push($styles, "color: $txtColor");
        }
    
        if (isset($args['flex']) && is_numeric(@$args['flex'])) {
            $flex = @$args['flex'];
            array_push($styles, "flex: $flex;");
        }

        $rawStyles = implode('', $styles);
        Utils::embedStyle("#ext-shubara-$id { $rawStyles }", $parser, $content);
    
        $content .= $parser->recursiveTagParse($input, $frame);

        return Html::rawElement('div', $htmlAttributes, $content);
    }
}
