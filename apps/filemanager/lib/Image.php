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
	public static function resize ($file, $width = 140, $height = 105, $style = 'cover', $format = 'jpg') {
		if (strpos ($file, '/') === 0) {
			// trim slash in case we get an absolute path
			$file = ltrim ($file, '/');
		}
		
		if (strpos ($file, '#') !== false) {
			// remove hashmark from previous Image:: call
			$file = explode ('#', $file);
			$file = $file[0];
		}

		if (! file_exists ($file)) {
			return $file . '#file-missing';
		}

		$info = pathinfo ($file);
		$ext = strtolower ($info['extension']);

		if ($format === 'ext') {
			$format = $ext;
		}

		$cache_file = 'cache/thumbs/' . md5 ($file) . '-'. $style ."-" . $width . 'x' . $height . '.' . $format;
		if (@file_exists ($cache_file) && @filemtime ($cache_file) > @filemtime ($file)) {
			return $cache_file;
		}

		if (! extension_loaded ('gd')) {
			return $file . '#gd-missing';
		}
		
		try {
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
		} catch (Exception $e) {
			return $file . '#exception-caught';
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

		if ($format === 'png') {
			@imagealphablending ($new, false);
			@imagesavealpha ($new, true);
			@imagecopyresampled ($new, $orig, 0, 0, $woffset, $hoffset, $width, $height, $w, $h);
			@imagepng ($new, $cache_file);
		} elseif ($format === 'gif') {
			$black = imagecolorallocate ($new, 0, 0, 0);
			@imagecolortransparent ($new, $black);
			@imagecopyresampled ($new, $orig, 0, 0, $woffset, $hoffset, $width, $height, $w, $h);
			@imagegif ($new, $cache_file);
		} else {
			@imagecopyresampled ($new, $orig, 0, 0, $woffset, $hoffset, $width, $height, $w, $h);
			@imagejpeg ($new, $cache_file);
		}
		@imagedestroy ($orig);
		@imagedestroy ($new);
		return $cache_file;
	}

	/**
	 * Fixes the orientation of a JPEG image based on its exif data.
	 * Note: Requires the exif PHP extension, or it will simply
	 * return the original image untouched with the following
	 * hashmark added: #exif-missing.
	 */
	public static function reorient ($file) {
		if (strpos ($file, '/') === 0) {
			// trim slash in case we get an absolute path
			$file = ltrim ($file, '/');
		}
		
		if (strpos ($file, '#') !== false) {
			// remove hashmark from previous Image:: call
			$file = explode ('#', $file);
			$file = $file[0];
		}

		$info = pathinfo ($file);
		$ext = strtolower ($info['extension']);
		
		if ($ext !== 'jpg' && $ext !== 'jpeg') {
			// only jpegs have exif data
			return $file;
		}
		
		if (! extension_loaded ('exif')) {
			return $file . '#exif-missing';
		}
		
		try {
			$exif = exif_read_data ($file);
		} catch (Exception $e) {
			return $file . '#exif-error';
		}
		$orientation = isset ($exif['Orientation']) ? $exif['Orientation'] : false;
		if (! $orientation || $orientation === 1) {
			// no reorientation needed :)
			return $file;
		}

		list ($width, $height) = getimagesize ($file);
		$new = @imagecreatetruecolor ($width, $height);
		
		try {
			if (@imagetypes () & IMG_JPG) {
				$orig = @imagecreatefromjpeg ($file);
				@imagecopyresampled ($new, $orig, 0, 0, 0, 0, $width, $height, $width, $height);
			} else {
				return $file . '#libjpg-missing';
			}
		} catch (Exception $e) {
			return $file . '#exception-caught';
		}

		switch ($orientation) {
			case 3:
				$new = @imagerotate ($new, 180, 0);
				break;
			case 6:
				$new = @imagerotate ($new, -90, 0);
				break;
			case 8:
				$new = @imagerotate ($new, 90, 0);
				break;
		}
		
		@imagejpeg ($new, $file);
		@imagedestroy ($orig);
		@imagedestroy ($new);
		return $file;
	}
	
	/**
	 * Generate a unique key for embedded image spots.
	 */
	public static function generate_key ($key) {
		if (! empty ($key)) {
			return $key;
		}
		
		if (! @is_dir ('cache/photos')) {
			mkdir ('cache/photos');
		}

		while (true) {
			$key = md5 (uniqid (rand (), true));
			$file = 'cache/photos/' . $key;
			// TODO: verify uniqueness
			if (! file_exists ($file)) {
				touch ($file);
				chmod ($file, 0666);
				break;
			}
		}

		return $key;
	}
	
	/**
	 * Get or set the image associated with a key.
	 */
	public static function for_key ($key, $image = null) {
		if (! @is_dir ('cache/photos')) {
			mkdir ('cache/photos');
		}

		if (! preg_match ('/^[a-zA-Z0-9-]+$/', $key)) {
			// invalid key
			return false;
		}

		$file = 'cache/photos/' . $key;
		
		if ($image !== null) {
			file_put_contents ($file, $image);
			chmod ($file, 0666);
		}
		
		if (! file_exists ($file)) {
			return false;
		}
		
		$res = file_get_contents ($file);
		if (empty ($res)) {
			return false;
		}
		return $res;
	}
}
