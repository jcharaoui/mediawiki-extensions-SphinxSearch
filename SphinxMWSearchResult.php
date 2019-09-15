<?php
/**
 * Class file for SphinxMWSearchResult
 *
 * https://www.mediawiki.org/wiki/Extension:SphinxSearch
 *
 * Released under GNU General Public License (see http://www.fsf.org/licenses/gpl.html)
 *
 * @file
 * @ingroup Extensions
 * @author Svemir Brkic <svemir@deveblog.com>
 */

class SphinxMWSearchResult extends RevisionSearchResult {

	public $sphinx_client = null;

	function __construct( $row, $sphinx_client ) {
		$this->sphinx_client = $sphinx_client;
		$this->initFromTitle( Title::makeTitle( $row->page_namespace, $row->page_title ) );
	}

	/**
	 * Emulates SearchEngine getTextSnippet so that we can use our own userHighlightPrefs
	 *
	 * @param $terms array of terms to highlight
	 * @return string highlighted text snippet
	 */
	function getTextSnippet( $terms ) {
		global $wgUser, $wgAdvancedSearchHighlighting;
		global $wgSphinxSearchMWHighlighter, $wgSphinxSearch_index;

		$this->initText();
		$contextlines = 2;
		$contextchars = 75;
		if ( $wgSphinxSearchMWHighlighter ) {
			$h = new SearchHighlighter();
			if ( $wgAdvancedSearchHighlighting ) {
				return $h->highlightText( $this->mText, $terms, $contextlines, $contextchars );
			} else {
				return $h->highlightSimple( $this->mText, $terms, $contextlines, $contextchars );
			}
		}

		$excerpts_opt = array(
			"before_match" => "(searchmatch)",
			"after_match" => "(/searchmatch)",
			"chunk_separator" => " ... ",
			"limit" => $contextlines * $contextchars,
			"around" => $contextchars,
		);

		$excerpts = $this->sphinx_client->BuildExcerpts(
			array( $this->mText ),
			$wgSphinxSearch_index,
			join( ' ', $terms ),
			$excerpts_opt
		);

		if ( is_array( $excerpts ) ) {
			$ret = '';
			foreach ( $excerpts as $entry ) {
				// remove some wiki markup
				$entry = preg_replace(
					'/([\[\]\{\}\*\#\|\!]+|==+|<br ?\/?>)/',
					' ',
					$entry
				);
				$entry = str_replace(
					array("<", ">"),
					array("&lt;", "&gt;"),
					$entry
				);
				$entry = str_replace(
					array( "(searchmatch)", "(/searchmatch)" ),
					array( "<span class='searchmatch'>", "</span>" ),
					$entry
				);
				$ret .= "<div style='margin: 0.2em 1em 0.2em 1em;'>$entry</div>\n";
			}
		} else {
			$ret = wfMessage( 'internalerror_info', $this->sphinx_client->GetLastError() );
		}
		return $ret;
	}

}
