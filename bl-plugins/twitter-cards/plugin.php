<?php

class pluginTwitterCards extends Plugin {

	public function init()
	{
		// Fields and default values for the database of this plugin
		$this->dbFields = array(
			'defaultImage'=>''
		);
	}

	public function form()
	{
		global $Language;

		$html  = '<div>';
		$html .= '<label>'.$Language->get('Default image').'</label>';
		$html .= '<select name="defaultImage">';

		$images = Filesystem::listFiles(PATH_UPLOADS);
		foreach ($images as $image) {
			$base = basename($image);
			$html .= '<option value="'.$base.'" '.(($this->getValue('defaultImage')==$base)?'selected':'').'>'.$base.'</option>';
		}

		$html .= '</select>';
		$html .= '</div>';

		return $html;
	}

	public function siteHead()
	{
		global $Url;
		global $Site;
		global $WHERE_AM_I;
		global $pages;
		global $page;

		$data = array(
			'card'		=>'summary',
			'site'		=>'',
			'title'		=>$Site->title(),
			'description'	=>$Site->description(),
			'image'		=>''
		);

		switch($WHERE_AM_I) {
			// The user filter by page
			case 'page':
				$data['title']		= $page->title();
				$data['description']	= $page->description();
				$data['image'] 		= $page->coverImage($absolute=true);

				$content = $page->content();
				break;

			// The user is in the homepage
			default:
				$content = '';
				// The image it's from the first page
				if(isset($pages[0]) ) {
					$data['image'] 	= $pages[0]->coverImage($absolute=true);
					$content 	= $pages[0]->content();
				}
				break;
		}

		$html  = PHP_EOL.'<!-- Twitter Cards -->'.PHP_EOL;
		$html .= '<meta property="twitter:card" content="'.$data['card'].'">'.PHP_EOL;
		$html .= '<meta property="twitter:site" content="'.$data['site'].'">'.PHP_EOL;
		$html .= '<meta property="twitter:title" content="'.$data['title'].'">'.PHP_EOL;
		$html .= '<meta property="twitter:description" content="'.$data['description'].'">'.PHP_EOL;

		// If the page doesn't have a coverImage try to get an image from the HTML content
		if( empty($data['image']) ) {
			// Get the image from the content
			$src = $this->getImage($content);
			if ($src!==false) {
				$og['image'] = $src;
			} else {
				$og['image'] = DOMAIN_UPLOADS.$this->getValue('defaultImage');
			}
		}

		$html .= '<meta property="twitter:image" content="'.$data['image'].'">'.PHP_EOL;
		return $html;
	}

	// Returns the first image from the HTML content
	private function getImage($content)
	{
		$dom = new DOMDocument();
		$dom->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$content);
		$finder = new DomXPath($dom);

		$images = $finder->query("//img");

		if($images->length>0) {
			// First image from the list
			$image = $images->item(0);
			// Get value from attribute src
			$imgSrc = $image->getAttribute('src');
			// Returns the image src
			return $imgSrc;
		}

		return false;
	}
}
