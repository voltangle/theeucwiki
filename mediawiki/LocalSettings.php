<?php
# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

// https://www.mediawiki.org/wiki/Manual:Short_URL
$wgScriptPath = "/w";

// The URL path to static resources (images, scripts, etc.)
$wgResourceBasePath = $wgScriptPath;

$wgLogos = [
	'icon' => "$wgScriptPath/icon.png",
	'1x' => "$wgScriptPath/logo-1x.png",
	'1.5x' => "$wgScriptPath/logo-1.5x.png",
	'2x' => "$wgScriptPath/logo-2x.png",
	'svg' => "$wgScriptPath/logo.svg",
];
// $wgFavicon = "$wgScriptPath/favicon.ico";
$wgFavicon = "$wgScriptPath/favicon.png";

## Database settings
$wgDBtype = "mysql";
$wgDBserver = "db";
$wgDBname = "mediawiki";
$wgDBuser = "mwuser";
$wgDBpassword = "";

$wgAllowSiteCSSOnRestrictedPages = true;
$wgMaxImageArea = false;

# MySQL specific settings
$wgDBprefix = "";
$wgDBssl = false;

# MySQL table options to use during installation or update
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

# Shared database table
# This has no effect unless $wgSharedDB is also set.
$wgSharedTables[] = "actor";
$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";
$wgUseInstantCommons = true;

# Periodically send a pingback to https://www.mediawiki.org/ with basic data
# about this MediaWiki instance. The Wikimedia Foundation shares this data
# with MediaWiki developers to help guide future development efforts.
$wgPingback = true;
$wgLocaltimezone = "UTC";
$wgCacheDirectory = "/tmp";

$wgSecretKey = getenv('MW_SECRETKEY');
$wgUpgradeKey = getenv('MW_UPGRADEKEY');

## For attaching licensing metadata to pages, and displaying an
## appropriate copyright notice / icon. GNU Free Documentation
## License and Creative Commons licenses are supported so far.
$wgRightsPage = ""; # Set to the title of a wiki page that describes your license/copyright
$wgRightsUrl = "";
$wgRightsText = "";
$wgRightsIcon = "";

$wgDiff3 = "/usr/bin/diff3";

########################### Core Settings ##########################
$wgLanguageCode = 'en';
$wgSitename = 'The EUC Wiki';
$wgMetaNamespace = "The_EUC_Wiki";
$wgServer = getenv('MW_SITE_SERVER');
$wgEnableUploads = true;

$wgJobRunRate = 0;

## https://www.mediawiki.org/wiki/Manual:Short_URL
$wgArticlePath = '/wiki/$1';
## Also see mediawiki.conf

##### Improve performance
# https://www.mediawiki.org/wiki/Manual:$wgMainCacheType
$wgMainCacheType = CACHE_MEMCACHED;
$wgParserCacheType = CACHE_MEMCACHED; # optional
$wgMessageCacheType = CACHE_MEMCACHED; # optional
$wgMemCachedServers = explode(',', getenv('MW_MEMCACHED_SERVERS'));
$wgSessionsInObjectCache = true; # optional
$wgSessionCacheType = CACHE_MEMCACHED; # optional

# Use Varnish accelerator
// FIX: this stinks
$tmpProxy = getenv( 'MW_PROXY_SERVERS' );
if ( $tmpProxy ) {
    # https://www.mediawiki.org/wiki/Manual:Varnish_caching
    $wgUseSquid = true;
    $wgSquidServers = explode( ',', $tmpProxy );
    $wgUsePrivateIPs = true;
    $wgHooks['IsTrustedProxy'][] = function( $ip, &$trusted ) {
        // Proxy can be set as a name of proxy container
        if ( !$trusted ) {
            global $wgSquidServers;
            foreach ( $wgSquidServers as $proxy ) {
                if ( !ip2long( $proxy ) ) { // It is name of proxy
                    if ( gethostbyname( $proxy ) === $ip ) {
                        $trusted = true;
                        return;
                    }
                }
            }
        }
    };
}

define("NS_NEWS", 3000);
define("NS_NEWS_TALK", 3001);

// This should be migrated over to Shubara and have some nice UI and wrapping and shit
$wgExtraNamespaces[NS_NEWS] = "News";
$wgExtraNamespaces[NS_NEWS_TALK] = "News_talk";

//Use $wgSquidServersNoPurge if you don't want MediaWiki to purge modified pages
//$wgSquidServersNoPurge = array('127.0.0.1');

####################### Email #########################
$wgEnableEmail = true;
$wgEnableUserEmail = true;

$wgEnotifUserTalk = false;
$wgEnotifWatchlist = false;
$wgEnotifRevealEditorAddress = true;

$wgPasswordSender = 'info@monowheel.wiki';
$wgNoReplyAddress = 'noreply@monowheel.wiki';

if (!str_contains($wgServer, 'localhost')) { // if running in prod
    $wgEmailAuthentication = true;
    $wgEmailConfirmToEdit = true;
    $wgSMTP = [
        'host' => 'in-v3.mailjet.com',
        'localhost' => 'monowheel.wiki',
        'port' => 587,
        'auth' => true,
        'username' => getenv('MAILJET_APIKEY'),
        'password' => getenv('MAILJET_SECRETKEY'),
    ];
}

####################### Uploads #########################
# Set this value if needed
# $wgUploadSizeWarning
$wgMaxUploadSize = 1024 * 1024 * 200; # 200 mebibytes

####################### Extensions #########################
wfLoadExtension('AbuseFilter');
wfLoadExtension('AdvancedSearch');
wfLoadExtension('Babel');
wfLoadExtension('CategoryTree');
wfLoadExtension('CheckUser');
wfLoadExtension('Cite');
wfLoadExtension('CiteThisPage');
wfLoadExtension('CirrusSearch');
wfLoadExtension('cldr');
wfLoadExtension('CleanChanges');
wfLoadExtension('CodeEditor'); 
wfLoadExtension('ConfirmEdit');
if (getenv('USE_TURNSTILE') == 'yes') {
    wfLoadExtension('ConfirmEdit/Turnstile');
}
wfLoadExtension('CreateRedirect');
wfLoadExtension('CSS');
wfLoadExtension('Description2');
wfLoadExtension('Disambiguator');
wfLoadExtension('DiscussionTools');
wfLoadExtension('DisplayTitle');
wfLoadExtension('Drafts');
wfLoadExtension('DynamicPageList3');
wfLoadExtension('Echo');
wfLoadExtension('Elastica');
wfLoadExtension('ElectronPdfService');
wfLoadExtension('Gadgets');
# wfLoadExtension('GoogleLogin');
wfLoadExtension('ImageMap');
wfLoadExtension('InputBox');
wfLoadExtension('Interwiki');
wfLoadExtension('Linter');
wfLoadExtension('LoginNotify');
wfLoadExtension('Math');
wfLoadExtension('Moderation');
wfLoadExtension('MultimediaViewer');
wfLoadExtension('Nuke');
wfLoadExtension('OATHAuth');
wfLoadExtension('PageImages');
wfLoadExtension('ParserFunctions');
wfLoadExtension('PdfHandler');
wfLoadExtension('Poem');
wfLoadExtension('Popups');
wfLoadExtension('RedirectManager');
wfLoadExtension('RelatedArticles');
wfLoadExtension('ReplaceText');
wfLoadExtension('RevisionSlider');
wfLoadExtension('SandboxLink');
wfLoadExtension('SecureLinkFixer');
wfLoadExtension('Scribunto');
wfLoadExtension('ShortDescription');
wfLoadExtension('Shubara');
wfLoadExtension('SpamBlacklist');
wfLoadExtension('StopForumSpam');
wfLoadExtension('SyntaxHighlight_GeSHi');
wfLoadExtension('TabberNeue');
wfLoadExtension('TemplateStyles');
wfLoadExtension('TextExtracts');
wfLoadExtension('Thanks');
wfLoadExtension('TitleBlacklist');
wfLoadExtension('Translate');
wfLoadExtension('UniversalLanguageSelector');
wfLoadExtension('UploadWizard');
wfLoadExtension('UserMerge');
wfLoadExtension('VisualEditor');
wfLoadExtension('WikiEditor');

### Description2 ###
$wgEnableMetaDescriptionFunctions = true;
$wgDescriptionRemoveElements[] = '.ext-shubara-infobox';

### Math ###
$wgMathInternalRestbaseURL = getenv('MW_REST_RESTBASE_URL');

### DisplayTitle ###
$wgAllowDisplayTitle = true;
$wgRestrictDisplayTitle = false;

### StopForumSpam ###
$wgSFSIPListLocation = getenv('MW_HOME') . '/stopforumspam.txt';

### Disambiguator ###
$wgDisambiguatorNotifications = true;

### TitleBlacklist ###
$wgTitleBlacklistSources = array(
    array(
         'type' => 'localpage',
         'src'  => 'MediaWiki:Titleblacklist',
    ),
    array(
         'type' => 'url',
         'src'  => 'https://meta.wikimedia.org/w/index.php?title=Title_blacklist&action=raw',
    ),
);

### ConfirmEdit ###
$wgTurnstileSiteKey = getenv('TURNSTILE_SITEKEY');
$wgTurnstileSecretKey = getenv('TURNSTILE_SECRETKEY');

### SyntaxHighlight_GeSHi ###
$wgPygmentizePath = '/usr/bin/pygmentize';

### UploadWizard ###
# TODO: configure
# $wgUploadWizardConfig['licensing']['impor']
$wgUploadWizardConfig['tutorial']['skip'] = true;

### CheckUser ###
$wgGroupPermissions['sysop']['checkuser'] = true;
$wgGroupPermissions['sysop']['checkuser-log'] = true;

### Drafts ###
$wgDraftsAutoSaveWait = 60;

### CleanChanges
$wgCCTrailerFilter = true;
$wgCCUserFilter = false;
$wgDefaultUserOptions['usenewrc'] = 1;

### WikiEditor ###
# Enables use of WikiEditor by default but still allows users to disable it in preferences
$wgDefaultUserOptions['usebetatoolbar'] = 1;
# Enables link and table wizards by default but still allows users to disable them in preferences
$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1;
# Displays the Preview and Changes tabs
$wgDefaultUserOptions['wikieditor-preview'] = 1;
# Displays the Publish and Cancel buttons on the top right side
$wgDefaultUserOptions['wikieditor-publish'] = 1;

### VisualEditor ###
$tmpRestDomain = getenv( 'MW_REST_DOMAIN' );
$tmpRestParsoidUrl = getenv( 'MW_REST_PARSOID_URL' );

// Enable by default for everybody
$wgDefaultUserOptions['visualeditor-enable'] = 1;
$wgDefaultUserOptions['visualeditor-editor'] = 'visualeditor';

// Don't allow users to disable it
$wgHiddenPrefs[] = 'visualeditor-enable';

// OPTIONAL: Enable VisualEditor's experimental code features
#$wgDefaultUserOptions['visualeditor-enable-experimental'] = 1;

$wgVirtualRestConfig['modules']['parsoid'] = [
        // URL to the Parsoid instance
        'url' => $tmpRestParsoidUrl,
        // Parsoid "domain", see below (optional)
        'domain' => $tmpRestDomain,
        // Parsoid "prefix", see below (optional)
        'prefix' => $tmpRestDomain,
];

$tmpRestRestbaseUrl = getenv( 'MW_REST_RESTBASE_URL' );
if ( $tmpRestRestbaseUrl ) {
    $wgVirtualRestConfig['modules']['restbase'] = [
    'url' => $tmpRestRestbaseUrl,
    'domain' => $tmpRestDomain,
    'parsoidCompat' => false
    ];

    $tmpRestProxyPath = getenv( 'MW_REST_RESTBASE_PROXY_PATH' );
    if ( $tmpProxy && $tmpRestProxyPath ) {
        $wgVisualEditorFullRestbaseURL = $wgServer . $tmpRestProxyPath;
    } else {
        $wgVisualEditorFullRestbaseURL = $wgServer . ':' . getenv( 'MW_REST_RESTBASE_PORT' ) . "/$tmpRestDomain/";
    }
    $wgVisualEditorRestbaseURL = $wgVisualEditorFullRestbaseURL . 'v1/page/html/';
}

########################### Search ############################
# https://www.mediawiki.org/wiki/Extension:CirrusSearch
$wgSearchType = 'CirrusSearch';
$wgDebugLogGroups['CirrusSearch'] = "$IP/cache/CirrusSearch.log";
$wgCirrusSearchConnectionAttempts = 3;
$wgCirrusSearchUseCompletionSuggester = 'build';
$wgCirrusSearchServers =  explode(',', getenv('MW_CIRRUS_SEARCH_SERVERS'));

### RelatedArticles ###
$wgRelatedArticlesFooterAllowedSkins = ['vector-2022', 'vector', 'citizen'];
$wgRelatedArticlesUseCirrusSearch = true;
$wgRelatedArticlesDescriptionSource = 'pagedescription';

### Scribunto ###
$wgScribuntoDefaultEngine = 'luastandalone';

### Translate ###
$wgTranslateDocumentationLanguageCode = 'qqq';
$wgExtraLanguageNames['qqq'] = 'Message documentation'; # No linguistic content. Used for documenting messages

$wgHooks['TranslatePostInitGroups'][] = function (&$list, &$deps, &$autoload) {
	$id = 'wiki-mainpage';
	$mg = new WikiMessageGroup($id, 'mainpage-messages');
	$mg->setLabel('MainPage');
	$mg->setDescription('Messages used in the main page of this wiki.');
	$list[$id] = $mg;
	return true;
};

$wgHooks['TranslatePostInitGroups'][] = function (&$list, &$deps, &$autoload) {
	$id = 'wiki-sidebar';
	$mg = new WikiMessageGroup($id, 'sidebar-messages');
	$mg->setLabel('Sidebar');
	$mg->setDescription('Messages used in the sidebar of this wiki.');
	$list[$id] = $mg;
	return true;
};

$wgHooks['TranslatePostInitGroups'][] = function (&$list, &$deps, &$autoload) {
	$id = 'wiki-sitenotice';
	$mg = new WikiMessageGroup($id, 'sitenotice-messages');
	$mg->setLabel('Sitenotice');
	$mg->setDescription('Message used in the sitenotice of this wiki.');
	$list[$id] = $mg;
	return true;
};

######################### UI ######################### 

wfLoadSkin('Vector');
wfLoadSkin('MinervaNeue');
wfLoadSkin('Timeless');
wfLoadSkin('MonoBook');
wfLoadSkin('CologneBlue');
wfLoadSkin('Citizen');

$wgDefaultSkin = 'citizen';

$wgHooks['SkinAddFooterLinks'][] = function($skin, $key, &$footerLinks) {
    if ($key === 'places') {
        $footerLinks['github'] = Html::rawElement('a',
            [
                'href' => 'https://github.com/voltangle/euc.repair',
                'rel' => 'noreferrer noopener'
            ],
        'GitHub');
        $footerLinks['ko-fi'] = Html::rawElement('a',
            [
                'href' => 'https://ko-fi.com/eucrepair',
                'rel' => 'noreferrer noopener'
            ],
        'Ko-fi');
        $footerLinks['patreon'] = Html::rawElement('a',
            [
                'href' => 'https://www.patreon.com/c/eucrepair',
                'rel' => 'noreferrer noopener'
            ],
        'Patreon');
    }
};

######################### Permissions ######################### 

$wgGroupPermissions['*']['edit'] = false; // Disable anonymous editing
$wgGroupPermissions['*']['createaccount'] = true;
$wgGroupPermissions['sysop']['interwiki'] = true;
$wgGroupPermissions['sysop']['sboverride'] = true; // sb - SpamBlacklist
$wgGroupPermissions['bureaucrat']['sboverride'] = true; // sb - SpamBlacklist
$wgGroupPermissions['bureaucrat']['usermerge'] = true;
$wgGroupPermissions['sysop']['deletelogentry'] = true;
$wgGroupPermissions['sysop']['deleterevision'] = true;
$wgGroupPermissions['automoderated']['skip-move-moderation'] = false;
$wgGroupPermissions['sysop']['skip-move-moderation'] = true;
$wgAddGroups['moderator'][] = 'automoderated';
$wgRemoveGroups['moderator'][] = 'automoderated';
$wgGroupPermissions['user']['translate'] = true;
$wgGroupPermissions['user']['translate-messagereview'] = true;
$wgGroupPermissions['user']['translate-groupreview'] = true;
$wgGroupPermissions['user']['translate-import'] = true;
$wgGroupPermissions['translator']['translate'] = true;
$wgGroupPermissions['translator']['skipcaptcha'] = true; // T36182: needed with ConfirmEdit
$wgGroupPermissions['sysop']['pagetranslation'] = true;
$wgGroupPermissions['sysop']['translate-manage'] = true;

$wgNamespaceProtection[NS_NEWS] = ['edit-news'];
$wgGroupPermissions['bureaucrat']['edit-news'] = true;

$wgBlockDisablesLogin = true;

$wgNamespacesWithSubpages[NS_MAIN] = true;

######################### Authentication ######################### 

// Google
$wgGLAppId = getenv('GOOGLE_LOGIN_APPID');
$wgGLSecret = getenv('GOOGLE_LOGIN_SECRET');

$wgAuthenticationTokenVersion = "1";

######################### Debug ######################### 

$wgDebugLogGroups['StopForumSpam'] = '/var/log/mediawiki/stopforumspam.log';

if (str_contains($wgServer, 'localhost')) { // if running locally
    $wgShowDebug = true;
    $wgShowExceptionDetails = true;
}
