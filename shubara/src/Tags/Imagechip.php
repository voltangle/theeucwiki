<?php
namespace MediaWiki\Extension\Shubara\Tags;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Html\Html;
use MediaWiki\Extension\Shubara\Utils;

define("HEX_COLOR_REGEX", '/^#(?:[0-9a-fA-F]{3}){1,2}$/g');

/**
* Render the imagechip tag
*/
// NOTE: Ideally, this guy should be not a tag extension, but a parser hook, because it
// takes wikitext input in multiple args, and a parser hook syntax will work better imo
// BUG: background-color does not work
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
        $href = htmlspecialchars(@$args["href"]);
        $htmlAttributes = [
            'class' => 'ext-shubara-imagechip ext-shubara-button',
            'id' => "ext-shubara-$id",
        ];
        $styles = [];
        $content = '';
        
        $mode = $args['mode'] ?? 'col';
        $backgroundColor = @$args['background-color'];
        $backgroundColorValid = preg_match($HEX_COLOR_REGEX, $backgroundColor);
        if ($backgroundColor != null && $backgroundColorValid == 1) {
            array_push($styles, "background-color: $backgroundColor;");
            $borderColor = Utils::adjustBrightness($backgroundColor, 0.2);
            array_push($styles, "border: 4px solid $borderColor;");
        }
    
        if (isset($args['flex']) && is_numeric(@$args['flex'])) {
            $flex = @$args['flex'];
            array_push($styles, "flex: $flex;");
        }

        if (count($styles) != 0) {
            $rawStyles = implode('', $styles);
            Utils::embedStyle("#ext-shubara-$id { $rawStyles }", $parser, $content);
        }

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

        // TODO: make this work with jQuery
        $js = "document.getElementById(\"ext-shubara-$id\").addEventListener(\"click\", function(){window.location=\"$href\"})";
        Utils::addHeadItem($parser, $js, 'javascript');
    
        $content .= $parser->recursiveTagParse($input, $frame);

        $tag = Html::rawElement('div', $htmlAttributes, $content);
    
        return $tag;
    }
}
