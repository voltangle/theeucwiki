<?php
namespace MediaWiki\Extension\Shubara\Tags;

use MediaWiki\Extension\Shubara\Utils;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Html\Html;

/**
 * Render the navcards tag
 */
class Navcards {
    /**
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
     * - layout: the CSS layout to use. Valid values: flex, grid. Default: flex
     * - grid-cols: value inserted into grid-template-columns as the repeat amount. If
     *   changed from the default value, grid-autohscroll is automatically unset.
     *      Default: 2
     * - grid-autohscroll: whether if the grid converts to a scrollable container when
     *   becoming too small. Default: yes
     * - uniform-rows: whether if grid-auto-rows: 1fr is set
     * @param Parser $parser MediaWiki Parser object
     * @param PPFrame $frame MediaWiki PPFrame object
     */
// TODO: implement grid-cols
    public static function run( $input, array $args, Parser $parser, PPFrame $frame ) {
        $id = Utils::generateRandomString();
        $classes = ['ext-shubara-navcards'];
        $styles = [];
        $output = '';

        if (isset($args['flex']) && is_numeric(@$args['flex'])) {
            $flex = @$args['flex'];
            array_push($styles, "flex: $flex;");
        }
        if (@$args['uniform-rows'] == 'yes') {
            array_push($styles, 'grid-auto-rows: 1fr;');
        }

        $gridAutoHScroll = $args['grid-autohscroll'] ?? 'yes';
        if (isset($args['grid-cols']) && is_numeric(@$args['grid-cols'])) {
            $gridCols = @$args['grid-cols'];
            $gridAutoHScroll = 'no';
            array_push($styles, "grid-template-columns: repeat($gridCols, 1fr);");
        }

        $layout = $args['layout'] ?? 'flex';
        if ($layout == 'flex' or $layout == 'grid') {
            array_push($classes, "ext-shubara-navcards-$layout");
        }
        if ($gridAutoHScroll == 'yes' && $layout == 'grid') {
            array_push($classes, 'ext-shubara-navcards-grid-autohscroll');
        }

        $rawStyles = '';
        if (@$args['no-min-width'] == 'yes') {
            $rawStyles .= "#ext-shubara-$id > * { min-width: unset !important; }";
        }
        $htmlAttributes = [
            'class' => implode(' ', $classes),
        ];
        if (count($styles) != 0) {
            $rawStyles .= "#ext-shubara-$id {";
            $rawStyles .= implode("\n", $styles);
            $rawStyles .= '}';
        }
        if (count($styles) != 0 or $args['no-min-width'] == 'yes') {
            $htmlAttributes['id'] = "ext-shubara-$id";
        }

        Utils::embedStyle($rawStyles, $parser, $output);

        switch ($args['out-mode'] ?? 'noreturn') {
            case 'raw': $output .= $parser->recursiveTagParse($input, $frame); break;
            case 'noreturn':
                $output .= $parser->recursiveTagParse(str_replace("\n", '', $input), $frame);
                break;
        }

        return Html::rawElement('div', $htmlAttributes, $output);
    }
}
