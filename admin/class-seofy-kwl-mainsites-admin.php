<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://seofy.dk
 * @since      1.0.0
 *
 * @package    Seofy_Kwl_Mainsites
 * @subpackage Seofy_Kwl_Mainsites/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Seofy_Kwl_Mainsites
 * @subpackage Seofy_Kwl_Mainsites/admin
 * @author     Jonard Aragon <jonardaragon@gmail.com>
 */
class Seofy_Kwl_Mainsites_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_filter( 'the_content', array($this, 'seofy_autolink_mainsite') );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Seofy_Kwl_Mainsites_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Seofy_Kwl_Mainsites_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/seofy-kwl-mainsites-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Seofy_Kwl_Mainsites_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Seofy_Kwl_Mainsites_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/seofy-kwl-mainsites-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function seofy_autolink_mainsite( $text, $is_content = false ) {
		wp_reset_postdata();
		global $post;
	
		// Get the post ID
		$post_id = $post->ID;
	
		// Check if the post type is either 'post' or 'page'
		if (is_singular()) {

						// Assume $current_post_id contains the ID of the current post you want to exclude
			$current_post_id = get_the_ID(); // Get the current post ID if within The Loop

			// WP Query to get posts with the 'rank_math_focus_keyword' meta key, excluding the current post
			$args = array(
				'post_type'      => 'any', // Change to your specific post type if needed
				'posts_per_page' => -1,    // Retrieves all posts
				'post__not_in'   => array($current_post_id), // Exclude the current post
				'meta_key'       => 'rank_math_focus_keyword', // Looks for posts with this meta key
				'meta_value'     => '',    // This could be specific if you're looking for a certain keyword
				'meta_compare'   => '!=',  // Ensures the meta value is not empty
			);

			$query = new WP_Query($args);
			$keywords_permalink = array();

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();

					// Get the focus keywords and permalink
					$focus_keywords = get_post_meta(get_the_ID(), 'rank_math_focus_keyword', true);
					$permalink = get_permalink(get_the_ID());

					// Check if there are multiple keywords
					if(strpos($focus_keywords, ',') !== false) {
						$keywords = explode(',', $focus_keywords);
						foreach ($keywords as $keyword) {
							$keyword = trim($keyword); // Trim to remove any space before and after the keyword
							// Map keyword to permalink
							$keywords_permalink[$keyword] = $permalink;
						}
					} else {
						// Single keyword
						$keywords_permalink[$focus_keywords] = $permalink;
					}
				}
				wp_reset_postdata();
			}

	
			// Check if no keywords were found
			if (empty($keywords_permalink)) {
				return $text; // Return the original text if no keywords found
			}

			// Link the keywords in the text
			$linkedContent = $this->linkKeywords($text, $keywords_permalink);
			return $linkedContent;
		} else {
			return $text; // Return the original text for other post types
		}
	}


	/*public function linkKeywords($content, $keywords) {
		$linkedKeywords = []; // Keep track of linked keywords
		$existingLinks = [];
		$placeholders = [];
		$placeholderCounter = 0;
	
		// Find existing links to avoid duplicating links
		preg_match_all('/<a href="[^"]*">([^<]*)<\/a>/', $content, $existingLinkMatches);
		foreach ($existingLinkMatches[1] as $existingLinkMatch) {
			$existingLinks[] = strtolower($existingLinkMatch); // Add lowercased version to ensure case-insensitive comparison
		}
	
		// Split the content into an array separating text from shortcodes
		$shortcodePattern = '/(\[[^\]]+\])/';
		$parts = preg_split($shortcodePattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	
		foreach ($parts as &$part) {
			if (!preg_match($shortcodePattern, $part)) {
				// Temporarily remove headings, img, a, and iframe tags and replace them with unique placeholders
				$excludeTagsPattern = '/<(h[1-6]|img|a|iframe)\b[^>]*>.*?<\/\1>/is';
				$part = preg_replace_callback($excludeTagsPattern, function($matches) use (&$placeholders, &$placeholderCounter) {
					$placeholder = "{{placeholder-" . $placeholderCounter . "}}";
					$placeholders[$placeholder] = $matches[0];
					$placeholderCounter++;
					return $placeholder;
				}, $part);
	
				// Process for keyword linking
				foreach ($keywords as $keyword => $url) {
					$escapedKeyword = preg_quote($keyword, '/'); // Escape regex special chars
					$pattern = "/\b($escapedKeyword)\b/i";
					preg_match_all($pattern, $part, $matches, PREG_OFFSET_CAPTURE);

					$offset = 0;
					foreach ($matches[0] as $match) {
						$matchedKeyword = $match[0];
						if (!in_array(strtolower($matchedKeyword), array_map('strtolower', $linkedKeywords)) && !in_array(strtolower($matchedKeyword), $existingLinks)) {
							$start = $match[1] + $offset;
							$linkedKeywords[] = $matchedKeyword;
							$link = '<a href="' . $url . '">' . $matchedKeyword . '</a>';
							$part = substr_replace($part, $link, $start, strlen($matchedKeyword));
							$offset += strlen($link) - strlen($matchedKeyword);
						}
					}
				}
	
				// Reinsert the excluded tags back into their original positions
				foreach ($placeholders as $placeholder => $tagContent) {
					$part = str_replace($placeholder, $tagContent, $part);
				}
			}
		}
	
		// Reassemble the parts back into the full content
		$content = implode('', $parts);
	
		return $content;
	}*/

	public function linkKeywords($content, $keywords) {
		if (empty($content) || empty($keywords)) {
			return $content;
		}

		// 1) Collect existing anchor texts (case-insensitive) to avoid re-linking
		$existingLinks = [];
		if (preg_match_all('/<a\b[^>]*>(.*?)<\/a>/isu', $content, $m)) {
			foreach ($m[1] as $txt) {
				$existingLinks[] = mb_strtolower(trim(strip_tags($txt)));
			}
		}

		// 2) Split content by shortcodes so we don't touch them
		$shortcodePattern = '/(\[[^\]]+\])/u';
		$parts = preg_split($shortcodePattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		// Track which keywords we already linked (once per post)
		$linkedKeywords = [];

		// Pre-normalize keywords (trim, dedupe empties)
		$normalized = [];
		foreach ($keywords as $kw => $url) {
			$kw = trim($kw);
			if ($kw === '') continue;
			$normalized[$kw] = $url;
		}

		// Helper: build a safe regex for a keyword (whole word/phrase, unicode)
		$makePattern = function($kw) {
			$escaped = preg_quote($kw, '/');
			// \b is imperfect for unicode phrases; this is a reasonable compromise:
			return '/(?<![^\W_])(' . $escaped . ')(?![^\W_])/iu';
		};
		
		$globalPH = 0;
		// 3) Process only parts that are NOT shortcodes
		foreach ($parts as &$part) {
			if (preg_match($shortcodePattern, $part)) {
				continue; // leave shortcodes untouched
			}

			// 3a) Temporarily replace regions we never want to touch with placeholders
			$placeholders = [];
			$phIndex = 0;

			// Exclude block anchors/headings/iframes and handle self-closing <img/>
			$excludePatterns = [
				'/<a\b[^>]*>.*?<\/a>/isu',
				'/<h[1-6]\b[^>]*>.*?<\/h[1-6]>/isu',
				'/<iframe\b[^>]*>.*?<\/iframe>/isu',
				'/<img\b[^>]*\/?>/isu', // self-closing or legacy
				'/<script\b[^>]*>.*?<\/script>/isu',
				'/<style\b[^>]*>.*?<\/style>/isu',
				'/<(code|pre)\b[^>]*>.*?<\/\1>/isu',
			];

			foreach ($excludePatterns as $xp) {
				$part = preg_replace_callback($xp, function($m) use (&$placeholders, &$phIndex) {
					$ph = '{{ph-' . ($phIndex++) . '}}';
					$placeholders[$ph] = $m[0];
					return $ph;
				}, $part);
			}

			// 3b) For each keyword (until it’s linked once globally), replace FIRST occurrence via callback
			foreach ($normalized as $kw => $url) {
				if (isset($linkedKeywords[mb_strtolower($kw)])) {
					continue;
				}

				// If an existing link already uses this exact text, skip
				if (in_array(mb_strtolower($kw), $existingLinks, true)) {
					$linkedKeywords[mb_strtolower($kw)] = true;
					continue;
				}

				$pattern = $makePattern($kw);

				$replaced = false;
				$part = preg_replace_callback($pattern, function($m) use ($url, $kw, &$replaced, &$linkedKeywords) {
					if ($replaced) return $m[0]; // only first per part
					// Link it
					$replaced = true;
					$linkedKeywords[mb_strtolower($kw)] = true;
					return '<a href="' . esc_url($url) . '">' . $m[1] . '</a>';
				}, $part, 1); // limit=1 for speed

				// Optional: also stop if we reached some global cap of total links
			}

			// 3c) Restore placeholders
			if (!empty($placeholders)) {
				$part = $this->restore_placeholders($part, $placeholders);
			}
		}

		return implode('', $parts);
	}

	private function restore_placeholders($content, $placeholders) {
		$maxLoops = 10; // protection against infinite loops
		while ($maxLoops > 0 && preg_match('/{{ph-\d+}}/', $content)) {
			foreach ($placeholders as $ph => $real) {
				$content = str_replace($ph, $real, $content);
			}
			$maxLoops--;
		}
		return $content;
	}



}
