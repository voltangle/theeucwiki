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
// NOTE: Ideally, this guy should be not a tag extension, but a parser hook, because it
// takes wikitext input in multiple args, and a parser hook syntax will work better imo
class Imagechip {
    /**
     * @param string $input What is supplied between the HTML tags. This gets evaluated
     * so it get spit out as normal wikitext
     * @param array $args HTML tag attribute params. Valid params:
     * - background-color: a hex color for the card. var(--color-surface-1) by default.
     * Also, background color directly influences the border color. You can't set it
     * manually. If background-color is default, border is --color-surface-2. If set,
     * it will be slightly brighter than the background color.
     * - content-light: Content that is used for light mode.
     * - content-dark: Content that is used for dark mode.
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
        $backgroundColor = @$args['background-color'];
        if ($backgroundColor != null) {
            array_push($styles, "background-color: $backgroundColor;");
            $borderColor = Utils::adjustBrightness($backgroundColor, 0.2);
            array_push($styles, "border: 4px solid $borderColor;");
        }
    
        if (isset($args['flex']) && is_numeric(@$args['flex'])) {
            $flex = @$args['flex'];
            array_push($styles, "flex: $flex;");
        }

        $rawStyles = implode('', $styles);
        Utils::embedStyle("#ext-shubara-$id { $rawStyles }", $parser, $content);

        $contentLight = @$args['content-light'];
        $contentDark = @$args['content-dark'];
        if ($contentLight != null) {
            $parsedContent = $parser->recursiveTagParse($contentLight, $frame);
            $nId = Utils::generateRandomString();
            $content .= Html::rawElement('div', ['id' => "ext-shubara-$nId", 'class' => " ext-shubara-imagechip-child"], $parsedContent);
            if ($contentDark != null) {
                $style = ".skin-citizen-dark #ext-shubara-$nId { display: none; }";
                $style .= "@media (prefers-color-scheme: dark) {
                    .skin-citizen-auto #ext-shubara-$nId {display: none;}}";
                Utils::embedStyle($style, $parser, $content);
            }
        }
        if ($contentDark != null) {
            $parsedContent = $parser->recursiveTagParse($contentDark, $frame);
            $nId = Utils::generateRandomString();
            $content .= Html::rawElement('div', ['id' => "ext-shubara-$nId", 'class' => " ext-shubara-imagechip-child"], $parsedContent);
            if ($contentLight != null) {
                $style = ".skin-citizen-light #ext-shubara-$nId { display: none; }";
                $style .= "@media (prefers-color-scheme: light) {
                    .skin-citizen-auto #ext-shubara-$nId {display: none;}}";
                Utils::embedStyle($style, $parser, $content);
            }
        }
    
        $content .= $parser->recursiveTagParse($input, $frame);

        return Html::rawElement('div', $htmlAttributes, $content);
    }
}
