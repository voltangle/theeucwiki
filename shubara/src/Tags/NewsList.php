<?php
namespace MediaWiki\Extension\Shubara\Tags;

use MediaWiki\Extension\Shubara\Utils;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Html\Html;
use MediaWiki\Page\PageStore;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\Context\RequestContext;

// this only exists because DPL3 cannot filter pages by language
// oh and it doesn't have 'translated page' built in variable
// i will have to patch the guy so it works properly for this task
class NewsList {
    public static function run( $input, array $args, Parser $parser, PPFrame $frame ) {
        $output = '';

        $mws = MediaWikiServices::getInstance();
        $pageStore = $mws->getPageStore();
        $linkRenderer = $mws->getLinkRenderer();
        $pageProps = $mws->getPageProps();
        $language = RequestContext::getMain()->getLanguageCode()->toBcp47Code();

        $query = $pageStore->newSelectQueryBuilder()
            ->fields(['page_id', 'page_title'])
            ->where(['page_namespace' => 3000]); // NS_NEWS
        $pageRecords = $query->fetchResultSet();

        $pages = [];
        foreach ($pageRecords as $pageRecord) {
            if (str_contains($pageRecord->page_title, '/')) { continue; }
            array_push($pages, $pageRecord->page_title);
        }

        foreach ($pages as $page) {
            $title = Title::makeTitleSafe(3000, $page);
            $linkTitle;
            $pageTitle;

            if ($title == null) { continue; }

            // First, we try to grab a translated page

            $l10nTitle = null;
            // just so it doesn't go to the /en page, it's practically useless
            if ($language != 'en') {
                $l10nTitle = Title::newFromText($title->getPrefixedText() . '/' . $language);
            }
            if ($l10nTitle && $l10nTitle->exists()) {
                $linkTitle = $l10nTitle;

                $pageTitle = array_values(
                    $pageProps->getProperties($l10nTitle, 'displaytitle'))[0]
                    ?? $title->getPrefixedText();
            } else {
                $linkTitle = $title;
                $pageTitle = $pageProps->getProperties($title, 'displaytitle')
                    [$title->getArticleID()]
                    ?? $title->getPrefixedText();
            }

            $output .= Html::rawElement('li', [],
                $linkRenderer->makeLink($linkTitle, $pageTitle));
        }
        $output = Html::rawElement('ul', [], $output);
        return $output;
    }
}
