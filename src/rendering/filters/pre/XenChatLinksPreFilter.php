<?php

/**
 * Xen Chat links pre-filter.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatLinksPreFilter {
	const URL_REGEXP = "/((https|http|ftp)\:\/\/)?([\-_a-z0-9A-Z]+\.)+[a-zA-Z]{2,6}(\/[^\n\r \?]*)?(\?[^\"'<>\n\r ]+)?/i";
	const URL_YOUTUBE_REGEXP = "/((https|http)\:\/\/)?([\-_a-z0-9A-Z]+\.)*youtube\.com\/watch\?v\=([^\&\"'<>\n\r ]+)[^\"'<>\n\r ]*/i";
    const URL_YOUTUBE_REGEXP_2 = "/((https|http)\:\/\/)?([\-_a-z0-9A-Z]+\.)*youtu\.be\/([^\&\"'<>\n\r ]+)[^\"'<>\n\r ]*/i";
	const URL_IMAGE_REGEXP = "/((https|http|ftp)\:\/\/)?([\-_a-z0-9A-Z]+\.)+[a-zA-Z]{2,6}(\/[^ \?]*)?\.(jpg|jpeg|gif|png)(\?[^\"'<>\n\r ]+)?/i";
	const URL_PROTOCOLS_REGEXP = "/^(https|http|ftp)\:\/\//i";
	
	/**
	* @var XenChatImagesService
	*/
	private $imagesService;
	
	/**
	* @var integer
	*/
	private $replacementOffset = 0;
	
	/**
	* @var array
	*/
	private $createdAttachments = array();

	public function __construct() {
        $this->imagesService = XenChatContainer::get('services/XenChatImagesService');
		XenChatContainer::load('rendering/filters/XenChatShortcodeConstructor');
	}

	/**
	* Created attachments.
	*
	* @return array
	*/
	public function getCreatedAttachments() {
		return $this->createdAttachments;
	}
	
	/**
	* Detects URLs in the text and converts them into shortcodes indicating either regular links or images.
	*
	* @param string $text HTML-encoded string
	* @param boolean $detectAndDownloadImages Whether to check and download images
	* @param boolean $detectYouTubeVideos
	*
	* @return string
	*/
	public function filter($text, $detectAndDownloadImages, $detectYouTubeVideos = false) {
		$this->replacementOffset = 0;
		$this->createdAttachments = array();
		
		if (preg_match_all(self::URL_REGEXP, $text, $matches)) {
			if (count($matches) == 0) {
				return $text;
			}
			
			foreach ($matches[0] as $detectedURL) {
				$shortCode = null;
				$regularLink = false;
				$ytMatches = array();
				
				if ($detectAndDownloadImages && preg_match(self::URL_IMAGE_REGEXP, $detectedURL)) {
					$imageUrl = $detectedURL;
					if (!preg_match(self::URL_PROTOCOLS_REGEXP, $detectedURL)) {
						$imageUrl = "http://".$detectedURL;
					}
				
					try {
						$result = $this->imagesService->downloadImage($imageUrl);
						$this->createdAttachments[] = $result['id'];
						$shortCode = XenChatShortcodeConstructor::getImageShortcode($result['id'], $result['image'], $result['image-th'], $detectedURL);
					} catch (Exception $ex) {
						$regularLink = true;
                        $actions = XenChatContainer::get('services/user/XenChatActions');
                        $authentication = XenChatContainer::get('services/user/XenChatAuthentication');
						$actions->publishAction(
                            'showErrorMessage',
                            array('message' => $ex->getMessage()),
                            $authentication->getUser()
                        );
					}
				} elseif ($detectYouTubeVideos && preg_match(self::URL_YOUTUBE_REGEXP, $detectedURL, $ytMatches)) {
                    $movieId = array_pop($ytMatches);
                    $shortCode = XenChatShortcodeConstructor::getYouTubeShortcode($movieId, $detectedURL);
                } elseif ($detectYouTubeVideos && preg_match(self::URL_YOUTUBE_REGEXP_2, $detectedURL, $ytMatches)) {
					$movieId = array_pop($ytMatches);
					$shortCode = XenChatShortcodeConstructor::getYouTubeShortcode($movieId, $detectedURL);
				} else {
					$regularLink = true;
				}
				
				if ($regularLink) {
					$shortCode = sprintf('[link src="%s"]', $detectedURL);
				}
				
				if ($shortCode !== null) {
					$text = $this->strReplaceFirst($detectedURL, $shortCode, $text);
				}
			}
		}
		
		return $text;
	}

    /**
     * Replaces first occurrence of the needle.
     *
     * @param string $needle
     * @param string $replace
     * @param string $haystack
     *
     * @return string
     */
	private function strReplaceFirst($needle, $replace, $haystack) {
		$pos = strpos($haystack, $needle, $this->replacementOffset);
		
		if ($pos !== false) {
			$this->replacementOffset = $pos + strlen($replace);
			return substr_replace($haystack, $replace, $pos, strlen($needle));
		}
		
		return $haystack;
	}
}