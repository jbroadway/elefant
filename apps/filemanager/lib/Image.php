<?php

/**
 * Image-manipulation methods.
 */
class Image {
	/**
	 * If it can, it creates a cached thumbnail of the specified
	 * image file, saved to `cache/thumbs` with the name
	 * `md5($file)` plus `-WIDTHxHEIGHT.jpg`. If it can't create
	 * the thumbnail, the original will be returned with one of
	 * the following messages added to it as a hashmark:
	 *
	 *     #gd-missing
	 *     #libjpg-missing
	 *     #libpng-missing
	 *     #libgif-missing
	 *     #unsupported-format
	 *
	 * If the cached version already exists, and its modification time
	 * is newer than the original, then the cached version is returned
	 * immediately.
	 *
	 * This makes first requests to a gallery page expensive, but
	 * subsequent requests much faster.
	 */
	public static function resize ($file, $width = 140, $height = 105, $style = "cover") {
		if (strpos ($file, '/') === 0) {
			$file = ltrim ($file, '/');
		}

		$cache_file = 'cache/thumbs/' . md5 ($file) . '-'. $style ."-" . $width . 'x' . $height . '.jpg';
		if (@file_exists ($cache_file) && @filemtime ($cache_file) > @filemtime ($file)) {
			return $cache_file;
		}

		$info = pathinfo ($file);
		$ext = strtolower ($info['extension']);

		if (! extension_loaded ('gd')) {
			return $file . '#gd-missing';
		}
		if ($ext === 'jpg' || $ext === 'jpeg') {
			if (@imagetypes () & IMG_JPG) {
				$orig = @imagecreatefromjpeg ($file);
			} else {
				return $file . '#libjpg-missing';
			}
		} elseif ($ext === 'png') {
			if (@imagetypes () & IMG_PNG) {
				$orig = @imagecreatefrompng ($file);
			} else {
				return $file . '#libpng-missing';
			}
		} elseif ($ext === 'gif') {
			if (@imagetypes () & IMG_GIF) {
				$orig = @imagecreatefromgif ($file);
			} else {
				return $file . '#libgif-missing';
			}
		} else {
			return $file . '#unsupported-format';
		}

		if (! @is_dir ('cache/thumbs')) {
			mkdir ('cache/thumbs');
		}

		list ($w, $h) = getimagesize ($file);

		if($style === "cover"){
			$ratio = max ($width / $w, $height / $h);
			$woffset = ($w - $width / $ratio) / 2;
			$hoffset = ($h - $height / $ratio) / 2;
			$h = $height / $ratio;
			$w = $width / $ratio;	
		} elseif ($style === "stretch"){		
			$woffset = 0;
			$hoffset = 0;
		} elseif ($style === "contain"){		
			$woffset = 0;
			$hoffset = 0;			
			$scale = min($width/$w, $height/$h);	
			$width  = ceil($scale*$w);
			$height = ceil($scale*$h);
		}
	
		$new = @imagecreatetruecolor ($width, $height);
		@imagecopyresampled ($new, $orig, 0, 0, $woffset, $hoffset, $width, $height, $w, $h);
	
		@imagejpeg ($new, $cache_file);
		@imagedestroy ($orig);
		@imagedestroy ($new);
		return $cache_file;
	}
}

?>