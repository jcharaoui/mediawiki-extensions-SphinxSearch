<?php

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
	echo <<<EOT
To install SphinxSearch extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/SphinxSearch/SphinxSearch.php" );

EOT;
	exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'version'        => '0.7.0',
	'name'           => 'SphinxSearch',
	'author'         => 'Svemir Brkic, Paul Grinberg',
	'email'          => 'svemir at thirdblessing dot net, gri6507 at yahoo dot com',
	'url'            => 'http://www.mediawiki.org/wiki/Extension:SphinxSearch',
	'descriptionmsg' => 'sphinxsearch-desc'
);

$dir = dirname(__FILE__) . '/';

$wgAutoloadClasses['SphinxSearch'] = $dir . 'SphinxSearch_body.php';
$wgExtensionMessagesFiles['SphinxSearch'] = $dir . 'SphinxSearch.i18n.php';
$wgExtensionAliasesFiles['SphinxSearch'] = $dir . 'SphinxSearch.alias.php';

##########################################################
# To completely disable the default search and replace it with SphinxSearch,
# set this BEFORE including SphinxSearch.php in LocalSettings.php
# $wgSearchType = 'SphinxSearch';
##########################################################

if ($wgSearchType == 'SphinxSearch') {
	$wgDisableInternalSearch = true;
	$wgDisableSearchUpdate = true;
	$wgSpecialPages['Search'] = 'SphinxSearch';
} else {
	$wgSpecialPages['SphinxSearch'] = 'SphinxSearch';
}

# this assumes you have copied sphinxapi.php from your Sphinx
# installation folder to your SphinxSearch extension folder
if (!class_exists('SphinxClient')) {
	require_once ( $dir . "sphinxapi.php" );
}

# Host and port on which searchd deamon is tunning
$wgSphinxSearch_host = 'localhost';
$wgSphinxSearch_port = 9312;

# Main sphinx.conf index to search
$wgSphinxSearch_index = "wiki_main";

# By default, we search all available indexes
# You can also specify them explicitly, e.g
#$wgSphinxSearch_index_list = "wiki_main,wiki_incremental";
$wgSphinxSearch_index_list = "*";

# If you have multiple index files, you can specify their weights like this
# See http://www.sphinxsearch.com/docs/current.html#api-func-setindexweights
#$wgSphinxSearch_index_weights = array(
#	"wiki_main" => 100,
#	"wiki_incremental" => 10
#);

# Default Sphinx search mode
$wgSphinxSearch_mode = SPH_MATCH_EXTENDED;

# Default sort mode
$wgSphinxSearch_sortmode = SPH_SORT_RELEVANCE;
$wgSphinxSearch_sortby = '';

# By default, search will return articles that match any of the words in the search
# To change that to require all words to match by default, set the following to true 
$wgSphinxMatchAll = false;

# Number of matches to display at once
$wgSphinxSearch_matches = 10;
# How many matches searchd will keep in RAM while searching
$wgSphinxSearch_maxmatches = 1000;
# When to stop searching all together (if different from zero)
$wgSphinxSearch_cutoff = 0;

# Weights of individual indexed columns. This gives page titles extra weight
$wgSphinxSearch_weights = array('old_text'=>1, 'page_title'=>100);

# If you want to enable hierarchical category search, specify the top category of your hierarchy like this
#$wgSphinxTopSearchableCategory = 'Subject_areas';

# If you want sub-categories to be fetched as parent categories are checked,
# also set $wgUseAjax to true in your LocalSettings file, so that the following can be used:
#$wgAjaxExportList[] = 'SphinxSearch::ajaxGetCategoryChildren';

# EXPERIMENTAL: allow excluding selected categories when filtering
#$wgUseExcludes = true;

# Web-accessible path to the extension's folder
$wgSphinxSearchExtPath = '/extensions/SphinxSearch';
# Web-accessible path to the folder with SphinxSearch.js file (if different from $wgSphinxSearchExtPath)
#$wgSphinxSearchJSPath = '';

##########################################################
# Use Aspell to suggest possible misspellings. This could be provided via either
# PHP pspell module (http://www.php.net/manual/en/ref.pspell.php) or command line
# insterface to ASpell

# Should the suggestion mode be enabled?
# Should be set BEFORE SphinxSearch.php is included in LocalSettings
if (!isset($wgSphinxSuggestMode)) {
	$wgSphinxSuggestMode = false;
}

# Path to personal dictionary (for example personal.en.pws.) Needed only if using a personal dictionary
# Should be set BEFORE SphinxSearch.php is included in LocalSettings
if (!isset($wgSphinxSearchPersonalDictionary)) {
	$wgSphinxSearchPersonalDictionary = "";
}

# Here is why some vars need to be set before SphinxSearch is included.
# We setup a special page to edit the personal dictionary.
if ($wgSphinxSuggestMode && $wgSphinxSearchPersonalDictionary) {
	$wgAutoloadClasses['SphinxSearchPersonalDict'] = $dir . 'SphinxSearch_PersonalDict.php';
	$wgSpecialPages['SphinxSearchPersonalDict'] = 'SphinxSearchPersonalDict';
}

# Path to Aspell. Used only if your PHP does not have the pspell extension.
$wgSphinxSearchAspellPath = "/usr/bin/aspell";

# Path to aspell location and language data files. Do not set if not sure.
#$wgSphinxSearchPspellDictionaryDir = "/usr/lib/aspell";

