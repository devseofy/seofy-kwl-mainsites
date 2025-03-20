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
	/** Version 1 */
	/*
	private function linkKeywords($content, $keywords) {
		$linkedKeywords = []; // Keep track of linked keywords
		$placeholders = [];
		$placeholderCounter = 0;
	
		// Step 1: Temporarily remove headings, img, a, and iframe tags and replace them with unique placeholders
		$excludeTagsPattern = '/<(h[1-6]|img|a|iframe)\b[^>]*>.*?<\/\1>/is';
		$content = preg_replace_callback($excludeTagsPattern, function($matches) use (&$placeholders, &$placeholderCounter) {
			$placeholder = "{{placeholder-" . $placeholderCounter . "}}";
			$placeholders[$placeholder] = $matches[0];
			$placeholderCounter++;
			return $placeholder;
		}, $content);
	
		foreach ($keywords as $keyword => $url) {
			// Find occurrences of the keyword in the content with word boundaries for exact matches
			$pattern = "/\b($keyword)\b/i"; // Added word boundaries and capturing group for case preservation
			preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
	
			$offset = 0; // Keep track of the offset for replacements
			foreach ($matches[0] as $match) {
				$matchedKeyword = $match[0]; // The actual keyword text matched, preserving case
				if (!in_array(strtolower($matchedKeyword), array_map('strtolower', $linkedKeywords))) { // Check if not already linked, case-insensitive
					$start = $match[1] + $offset;
					$linkedKeywords[] = $matchedKeyword; // Mark keyword as linked, preserving its case
					$link = '<a href="' . $url . '">' . $matchedKeyword . '</a>'; // Use matched keyword text
					$content = substr_replace($content, $link, $start, strlen($matchedKeyword));
					$offset += strlen($link) - strlen($matchedKeyword); // Update offset for next replacement
				}
			}
		}
	
		// Step 3: Reinsert the headings, img, a, and iframe tags back into their original positions
		foreach ($placeholders as $placeholder => $heading) {
			$content = str_replace($placeholder, $heading, $content);
		}
	
		return $content;
	}
	*/
	/** Version 2 */
	/*private function linkKeywords($content, $keywords) {
		$linkedKeywords = []; // Keep track of linked keywords
		$placeholders = [];
		$shortcodePlaceholders = []; // Additional array for storing shortcode placeholders
		$placeholderCounter = 0;
		$shortcodePlaceholderCounter = 0;
	
		// Step 0: Temporarily remove shortcodes and replace them with unique placeholders
		$shortcodePattern = '/\[(.*?)\]/s'; // Pattern to match shortcodes, including nested ones
		$content = preg_replace_callback($shortcodePattern, function($matches) use (&$shortcodePlaceholders, &$shortcodePlaceholderCounter) {
			$placeholder = "{{shortcode-placeholder-" . $shortcodePlaceholderCounter . "}}";
			$shortcodePlaceholders[$placeholder] = $matches[0];
			$shortcodePlaceholderCounter++;
			return $placeholder;
		}, $content);
	
		// Step 1: Temporarily remove headings, img, a, and iframe tags and replace them with unique placeholders
		$excludeTagsPattern = '/<(h[1-6]|img|a|iframe)\b[^>]*>.*?<\/\1>/is';
		$content = preg_replace_callback($excludeTagsPattern, function($matches) use (&$placeholders, &$placeholderCounter) {
			$placeholder = "{{placeholder-" . $placeholderCounter . "}}";
			$placeholders[$placeholder] = $matches[0];
			$placeholderCounter++;
			return $placeholder;
		}, $content);
	
		foreach ($keywords as $keyword => $url) {
			// Find occurrences of the keyword in the content with word boundaries for exact matches
			$pattern = "/\b($keyword)\b/i";
			preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
	
			$offset = 0;
			foreach ($matches[0] as $match) {
				$matchedKeyword = $match[0];
				if (!in_array(strtolower($matchedKeyword), array_map('strtolower', $linkedKeywords))) {
					$start = $match[1] + $offset;
					$linkedKeywords[] = $matchedKeyword;
					$link = '<a href="' . $url . '">' . $matchedKeyword . '</a>';
					$content = substr_replace($content, $link, $start, strlen($matchedKeyword));
					$offset += strlen($link) - strlen($matchedKeyword);
				}
			}
		}
	
		// Step 3: Reinsert the headings, img, a, and iframe tags back into their original positions
		foreach ($placeholders as $placeholder => $heading) {
			$content = str_replace($placeholder, $heading, $content);
		}
	
		// Step 4: Reinsert the shortcodes back into their original positions
		foreach ($shortcodePlaceholders as $placeholder => $shortcodeContent) {
			$content = str_replace($placeholder, $shortcodeContent, $content);
		}
	
		return $content;
	}

	private function linkKeywords($content, $keywords) {
		$linkedKeywords = []; // Keep track of linked keywords
		$placeholders = [];
		$placeholderCounter = 0;
	
		// Step 1: Temporarily remove headings, img, a, and iframe tags and replace them with unique placeholders
		$excludeTagsPattern = '/<(h[1-6]|img|a|iframe)\b[^>]*>.*?<\/\1>/is';
		$content = preg_replace_callback($excludeTagsPattern, function($matches) use (&$placeholders, &$placeholderCounter) {
			$placeholder = "{{placeholder-" . $placeholderCounter . "}}";
			$placeholders[$placeholder] = $matches[0];
			$placeholderCounter++;
			return $placeholder;
		}, $content);
	
		// Directly process content, avoiding shortcodes
		$processedSections = [];
		$splitContent = preg_split('/(\[.*?\])/', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	
		foreach ($splitContent as $section) {
			// Check if the section is a shortcode
			if (preg_match('/^\[.*?\]$/', $section)) {
				// If it's a shortcode, just append without changes
				$processedSections[] = $section;
			} else {
				// Process non-shortcode sections for keywords
				foreach ($keywords as $keyword => $url) {
					$pattern = "/\b($keyword)\b/i";
					if (!in_array(strtolower($keyword), array_map('strtolower', $linkedKeywords))) {
						$replacement = '<a href="' . $url . '">$1</a>';
						$section = preg_replace($pattern, $replacement, $section);
						$linkedKeywords[] = $keyword; // Mark keyword as linked
					}
				}
				$processedSections[] = $section;
			}
		}
	
		// Reassemble the content
		$content = implode('', $processedSections);
	
		// Step 3: Reinsert the headings, img, a, and iframe tags back into their original positions
		foreach ($placeholders as $placeholder => $heading) {
			$content = str_replace($placeholder, $heading, $content);
		}
	
		return $content;
	}*/
	/*
	function linkKeywords($content, $keywords) {
		$linkedKeywords = []; // Keep track of linked keywords
		$placeholders = [];
		$placeholderCounter = 0;
	
		// Step 0: Split the content into an array separating text from shortcodes
		$shortcodePattern = '/(\[[^\]]+\])/'; // Adjusted pattern to match shortcodes
		$parts = preg_split($shortcodePattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	
		// Process only non-shortcode parts for linking
		foreach ($parts as &$part) {
			if (!preg_match($shortcodePattern, $part)) { // If part is not a shortcode
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
					$pattern = "/\b($keyword)\b/i";
					preg_match_all($pattern, $part, $matches, PREG_OFFSET_CAPTURE);
	
					$offset = 0;
					foreach ($matches[0] as $match) {
						$matchedKeyword = $match[0];
						if (!in_array(strtolower($matchedKeyword), array_map('strtolower', $linkedKeywords))) {
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
					$pattern = "/\b($keyword)\b/i";
					preg_match_all($pattern, $part, $matches, PREG_OFFSET_CAPTURE);
	
					$offset = 0;
					foreach ($matches[0] as $match) {
						$matchedKeyword = $match[0];
						// Check against already linked keywords and existing links
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
	}

}
