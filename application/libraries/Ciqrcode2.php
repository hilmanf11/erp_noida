<?php

/**
 * PHP QR Code porting for Codeigniter
 *
 * @package        	CodeIgniter
 * @subpackage    	Libraries
 * @category    	Libraries
 * @porting author	dwi.setiyadi@gmail.com
 * @original author	http://phpqrcode.sourceforge.net/
 * 
 * @version		1.0
 */

class Ciqrcode2
{
	function __construct($config = array())
	{
		// call original library
		include "qrcode2/qrcode.php";
	}

	public function generate($params = array())
	{
		$generator = new QRCode($data, $options);
		$generator->output_image();
		$image = $generator->render_image();
		imagepng($image);
		imagedestroy($image);
	}
}

/* end of file */
