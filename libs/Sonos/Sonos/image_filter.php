<?php

/*********************************************************************************************************
 * Image Filter Class
 *
 * @package PHP Image Filter GD
 * @version 1.0.1
 * @author MT Jordan <mtjo62@gmail.com>
 * @copyright 2014
 * @license zlib/libpng
 *********************************************************************************************************/

class image_filter
{ 
    /*****************************************************************************************************
     * Private vars
     *****************************************************************************************************/
     
    /**
     * Current filter
     *
     * @access private
     * @var    str
     */
    private $image_filter;
    
    /**
     * Array of available filters
     *
     * @access private
     * @var    array
     */
    private $image_filter_array = array( 'brighten',
                                         'brush',
                                         'darken',
                                         'edgedetect',
                                         'emboss',
                                         'flip',
                                         'grayscale',
                                         'larger',
                                         'mirror',
                                         'negative',
                                         'pixelate',
                                         'sephia',
                                         'sharpen',
                                         'sketch',
                                         'smaller',
                                         'smooth' );
    
    /**
     * Source image
     *
     * @access private
     * @var    mixed
     */
    private $image_src = false;
    
    /**
     * cURL flag
     *
     * @access private
     * @var    bool
     */
    private $image_curl = false;
    
    /**
     * Temp destination image
     *
     * @access private
     * @var    mixed
     */
    private $image_dest = false;
    
    /**
     * Image information 
     *
     * @access private
     * @var    array
     */
    private $image_info;
    
    /**
     * Source image URL
     *
     * @access private
     * @var    str
     */
    private $image_url;
    
    /**
     * Transparent image flag
     *
     * @access private
     * @var    bool
     */
    private $image_trans = false;
    
    /**
     * Constructor
     *
     * @access public
     * @param  str $url
     * @param  str $filter
     * @return void
     */
    function __construct( $url, $filter )
    {
        $image_filter = strtolower( $filter );
        $this->image_filter = $image_filter;
        $this->image_url = $url;
        $this->image_info = $this->get_image_info();
        $this->set_filter_error();
        $this->image_src = $this->set_image_type();
        $this->image_trans = $this->get_transparency();
    
        $this->$image_filter();
    }
    
    /*****************************************************************************************************
     * GD filters
     *****************************************************************************************************/

    /**
     * Process brighten filter
     *
     * @access public
     * @return void
     */   
    private function brighten()
    {
        imagefilter( $this->image_src, IMG_FILTER_BRIGHTNESS, 40 );
         
        $this->return_image();   
    }

    /**
     * Process brush filter
     *
     * @access private
     * @return void
     */   
    private function brush()
    {
        $neg_noise = -1;
        $pos_noise = 1;
 
        for ( $x = 0; $x < $this->image_info[0]; $x++ )
        {
            for ( $y = 0; $y < $this->image_info[1]; $y++ )
            {
                $explode_X = rand( $neg_noise, $pos_noise );
                $explode_Y = rand( $neg_noise, $pos_noise );

                if ( $x + $explode_X >= $this->image_info[0] )
                {
                    continue;
                }

                if ( $x + $explode_X < 0 )
                {
                    continue;
                }

                if ( $y + $explode_Y >= $this->image_info[1] )
                {
                    continue;
                }
                if ( $y + $explode_Y < 0 )
                {
                    continue;
                }

                imagesetpixel( $this->image_src, $x, $y, imagecolorat( $this->image_src, $x + $explode_X, $y + $explode_Y ) );
                imagesetpixel( $this->image_src, $x + $explode_X, $y + $explode_Y, imagecolorat( $this->image_src, $x, $y ) );
            }
        }
    
        $this->return_image(); 
    }
    
    /**
     * Process darken filter
     *
     * @access private
     * @return void
     */   
    private function darken()
    {
        imagefilter( $this->image_src, IMG_FILTER_BRIGHTNESS, -40 );
        
        $this->return_image(); 
    }

    /**
     * Process edge detect filter
     *
     * @access private
     * @return void
     */   
    private function edgedetect()
    {
        imagefilter( $this->image_src, IMG_FILTER_EDGEDETECT );
    
        if ( $this->image_trans )
        {
            imagefill( $this->image_src, 0, 0, imagecolorallocatealpha( $this->image_src, 147, 147, 147, 127 ) );
        }
    
        $this->return_image(); 
    }
    
    /**
     * Process emboss filter
     *
     * @access private
     * @return void
     */   
    private function emboss()
    {
        if ( $this->image_trans && $this->image_info[2] == 3 )
        {    
            $this->image_dest = imagecreate( $this->image_info[0], $this->image_info[1] );
            imagecopy( $this->image_dest, $this->image_src, 0, 0, 0, 0, $this->image_info[0], $this->image_info[1] );
       
            imagefilter( $this->image_dest, IMG_FILTER_GRAYSCALE );    
            imagefilter( $this->image_dest, IMG_FILTER_EMBOSS );
        }
        else
        {
            imagefilter( $this->image_src, IMG_FILTER_GRAYSCALE );    
            imagefilter( $this->image_src, IMG_FILTER_EMBOSS );
        }       
       
        $this->return_image(); 
    }
    
    /**
     * Process flip filter
     *
     * @access private
     * @return void
     */   
    private function flip()
    {
        $this->image_dest = imagecreatetruecolor( $this->image_info[0], $this->image_info[1] );
    
        if ( $this->image_trans )
        {
            $rgb = $this->random_RGB( $this->image_src );
            imagefill( $this->image_dest, 0, 0, imagecolorallocate( $this->image_dest, $rgb['r'], $rgb['g'], $rgb['b'] ) );
        }
   
        for ( $i = 0; $i < $this->image_info[0]; $i++ )
        {
            for ( $j = 0; $j < $this->image_info[1]; $j++ )
            {
                imagecopy( $this->image_dest, $this->image_src, $i, $this->image_info[1] - $j - 1, $i, $j, 1, 1 );
            }
        }

        if ( $this->image_trans )
        {
            imagecolortransparent( $this->image_dest, imagecolorallocate( $this->image_dest, $rgb['r'], $rgb['g'], $rgb['b'] ) ); 
        }    
               
        $this->return_image();       
    }
    
    /**
     * Process grayscale filter
     *
     * @access private
     * @return void
     */   
    private function grayscale()
    {
        imagefilter( $this->image_src, IMG_FILTER_GRAYSCALE );
        
        $this->return_image(); 
    }
    
    /**
     * Process larger filter
     *
     * @access private
     * @return void
     */   
    private function larger()
    {
        $image_width = $this->image_info[0] * 2;
        $image_height = $this->image_info[1] * 2;
        
        if ( $this->image_info[2] == 2 )
        {
            $this->image_dest = imagecreatetruecolor( $image_width, $image_height );
        }
        else
        {
            $this->image_dest = imagecreate( $image_width, $image_height );
        }
        
        imagecopyresampled( $this->image_dest, $this->image_src, 0, 0, 0, 0, $image_width, $image_height, $this->image_info[0], $this->image_info[1] );
    
        if ( $this->image_trans )
        {    
            imagecolortransparent( $this->image_dest, imagecolorat( $this->image_dest, 0, 0 ) );
        }
                
        $this->return_image(); 
    }

    /**
     * Process mirror filter
     *
     * @access private
     * @return void
     */   
    private function mirror()
    {
        $this->image_dest = imagecreatetruecolor( $this->image_info[0], $this->image_info[1] + ( $this->image_info[1] / 2 ) );
        
        imagealphablending( $this->image_dest, false );
        imagesavealpha( $this->image_dest, true );
        imagecopyresampled( $this->image_dest, $this->image_src, 0, 0, 0, 0, $this->image_info[0], $this->image_info[1], $this->image_info[0], $this->image_info[1] );
    
        for ( $i = 1; $i <= $this->image_info[1] / 2; $i++ ) 
        {
            for ( $j = 0; $j < $this->image_info[0]; $j++ ) 
            {
                $rgb = imagecolorat( $this->image_src, $j, $this->image_info[1] - $i );
                $alpha = ( $rgb & 0x7F000000 ) >> 24;
                $alpha =  max( $alpha, 47 + ( $i * ( 80 / ( $this->image_info[1] / 2 ) ) ) );
                $rgb = imagecolorsforindex( $this->image_src, $rgb );
                 
                //Check for transparent pixel
                if ( $rgb['alpha'] == 127 )
                {
                    $rgb = imagecolorallocatealpha( $this->image_dest, $rgb['red'], $rgb['green'], $rgb['blue'], 127 );
                    imagesetpixel( $this->image_dest, $j, $this->image_info[1] + $i - 1, $rgb );
                }
                else
                {
                    $rgb = imagecolorallocatealpha( $this->image_dest, $rgb['red'], $rgb['green'], $rgb['blue'], $alpha );
                    imagesetpixel( $this->image_dest, $j, $this->image_info[1] + $i - 1, $rgb );
                }
            }
        }
  
        $this->return_image(); 
    }
    
    /**
     * Process negative filter
     *
     * @access public
     * @return void
     */   
    private function negative()
    {
        imagefilter( $this->image_src, IMG_FILTER_NEGATE );
        
        $this->return_image(); 
    }
    
    /**
     * Process pixelate filter
     *
     * @access private
     * @return void
     */   
    private function pixelate()
    {
        imagefilter( $this->image_src, IMG_FILTER_PIXELATE, 3, 2 );
            
        $this->return_image(); 
    }
    
    /**
     * Process sephia filter
     *
     * @access private
     * @return void
     */       
    private function sephia()
    {
        $this->image_dest = imagecreatetruecolor( $this->image_info[0], $this->image_info[1] );
        $temp_image = imagecreatetruecolor( $this->image_info[0], $this->image_info[1] );

        imagefill( $this->image_dest, 0, 0, imagecolorallocate( $this->image_dest, 234, 224, 213 ) );
        imagecopy( $this->image_dest, $this->image_src, 0, 0, 0, 0, $this->image_info[0], $this->image_info[1] );
        imagefilter( $this->image_dest, IMG_FILTER_GRAYSCALE );
        imagefill( $temp_image, 0, 0, imagecolorallocate( $temp_image, 112, 66, 20 ) );
        imagecopymerge( $this->image_dest, $temp_image, 0, 0, 0, 0, $this->image_info[0], $this->image_info[1], 30 );

        imagedestroy( $temp_image );        
        
        $this->return_image(); 
    }
    
    /**
     * Process sharpen filter
     *
     * @access private
     * @return void
     */   
    private function sharpen()
    {
        $sharpen = array( array( -1.2, -1, -1.2 ), 
                   array( -1, 20, -1 ), 
                   array( -1.2, -1, -1.2 ) ); 

        $divisor = array_sum( array_map( 'array_sum', $sharpen ) );            
            
        if ( $this->image_trans )
        {    
            $this->image_dest = imagecreate( $this->image_info[0], $this->image_info[1] );
            imagecopyresampled( $this->image_dest, $this->image_src, 0, 0, 0, 0, $this->image_info[0], $this->image_info[1], $this->image_info[0], $this->image_info[1] );
            imagecolortransparent( $this->image_dest, imagecolorat( $this->image_dest, 0, 0 ) );
            
            imageconvolution( $this->image_dest, $sharpen, $divisor, 0 );  

            imagecolortransparent( $this->image_dest, imagecolorat( $this->image_dest, 0, 0 ) );
        
        }
        else
        {
            imageconvolution( $this->image_src, $sharpen, $divisor, 0 ); 
        }
    
        $this->return_image(); 
    }
    
    /**
     * Process sketch filter
     *
     * @access private
     * @return void
     */   
    private function sketch()
    {
        if ( $this->image_trans )
        {    
            $this->image_dest = imagecreate( $this->image_info[0], $this->image_info[1] );
            imagecopyresampled( $this->image_dest, $this->image_src, 0, 0, 0, 0, $this->image_info[0], $this->image_info[1], $this->image_info[0], $this->image_info[1] );
            imagecolortransparent( $this->image_dest, imagecolorat( $this->image_dest, 0, 0 ) );
            
            imagefilter( $this->image_dest, IMG_FILTER_MEAN_REMOVAL );
            
            imagecolortransparent( $this->image_dest, imagecolorat( $this->image_dest, 0, 0 ) );
        }
        else
        {
            imagefilter( $this->image_src, IMG_FILTER_MEAN_REMOVAL );
        }
    
        $this->return_image(); 
    }
    
    /**
     * Process smaller filter
     *
     * @access private
     * @return void
     */   
    private function smaller()
    {
        $image_width = $this->image_info[0] / 2;
        $image_height = $this->image_info[1] / 2;
        
        if ( $this->image_info[2] == 2 )
        {
            $this->image_dest = imagecreatetruecolor( $image_width, $image_height );
        }
        else
        {
            $this->image_dest = imagecreate( $image_width, $image_height );
        }
        
        imagecopyresampled( $this->image_dest, $this->image_src, 0, 0, 0, 0, $image_width, $image_height, $this->image_info[0], $this->image_info[1] );
    
        if ( $this->image_trans )
        {    
            imagecolortransparent( $this->image_dest, imagecolorat( $this->image_dest, 0, 0 ) );
        }
                
        $this->return_image(); 
    }
    
    /**
     * Process smooth filter
     *
     * @access private
     * @return void
     */   
    private function smooth()
    {
        if ( $this->image_trans )
        {    
            $this->image_dest = imagecreate( $this->image_info[0], $this->image_info[1] );
            imagecopyresampled( $this->image_dest, $this->image_src, 0, 0, 0, 0, $this->image_info[0], $this->image_info[1], $this->image_info[0], $this->image_info[1] );
            imagecolortransparent( $this->image_dest, imagecolorat( $this->image_dest, 0, 0 ) );
            
            imagefilter( $this->image_dest, IMG_FILTER_GAUSSIAN_BLUR );
            
            imagecolortransparent( $this->image_dest, imagecolorat( $this->image_dest, 0, 0 ) );
        }
        else
        {
            imagefilter( $this->image_src, IMG_FILTER_GAUSSIAN_BLUR );
        }
    
        $this->return_image(); 
    }
    
    /*****************************************************************************************************
     * Utility methods
     *****************************************************************************************************/
    
    /**
     * Process image URL and create image information
     *
     * @access private
     * @return array
     */   
    private function get_image_info()
    {
        $return_val = false;
        $image_info = @getimagesize( $this->image_url );

        if ( is_array( $image_info ) )
        {
            $return_val = $image_info;
        }
        elseif ( function_exists( 'curl_version' ) )
        {
            include_once 'img_info.php';
            
            $this->image_curl = true;
            
            $image_curl = new img_info;
            $return_val = $image_curl->getimagesize( $this->image_url );
        }
                    
        return $return_val;
    }
    
    /**
     * Process image URL and return image string if using cURL
     *
     * @access private
     * @return str
     */   
    private function get_image_string()
    {
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false ); 
        curl_setopt( $curl, CURLOPT_URL, $this->image_url );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        $data = curl_exec( $curl );
        curl_close( $curl );
        
        return $data;
    }
    
    /**
     * Test GIF or PNG image for transparency
     *
     * @access private
     * @return bool
     */       
    private function get_transparency() 
    {
        if ( $this->image_info[2] == 1 )
        {
            $image = $this->image_gif();
            $index = imagecolortransparent( $image );
            
            if ( $index != -1 ) 
            {
                imagedestroy( $image ); 
                
                return true;
            }
            
            imagedestroy( $image ); 
        }
        elseif ( $this->image_info[2] == 3 )
        {
            $image = $this->image_png();
            imagesavealpha( $image, true );
            
            for ( $i = 0; $i < $this->image_info[0]; $i++ ) 
            {
                for( $j = 0; $j < $this->image_info[1]; $j++ ) 
                {
                    $rgb = imagecolorat( $image, $i, $j );
                    $alpha = imagecolorsforindex( $image, $rgb );
            
                    if ( $alpha['alpha'] == 127 )
                    {
                        imagedestroy( $image ); 
                        
                        return true;
                    }
                }
            }
        
            imagedestroy( $image );   
        }
        
        return false;
    }
   
    /**
     * Return GIF image resource
     *
     * @access private
     * @param  str $image_src
     * @return mixed
     */   
    private function image_gif()
    {
        $return_val = false;
        
        if ( $this->image_curl )
        {
            $return_val = imagecreatefromstring( $this->get_image_string() );
        }
        else
        {
            $return_val = imagecreatefromgif( $this->image_url );
        }
        
        return $return_val;
    }
    
    /**
     * Return JPEG image resource
     *
     * @access private
     * @param  str $image_src
     * @return mixed
     */   
    private function image_jpeg()
    {
        $return_val = false;
        
        if ( $this->image_curl )
        {
            $return_val = imagecreatefromstring( $this->get_image_string() );
        }
        else
        {
            $return_val = imagecreatefromjpeg( $this->image_url );
        }
        
        return $return_val;
    }
    
    /**
     * Return PNG image resource
     *
     * @access private
     * @param  str $image_src
     * @return mixed
     */   
    private function image_png()
    {
        $return_val = false;
        
        if ( $this->image_curl )
        {
            $return_val = imagecreatefromstring( $this->get_image_string() );
        }
        else
        {
            $return_val = imagecreatefrompng( $this->image_url );
        }
        
        return $return_val;
    }
    
    /**
     * Determine RGB value not in current color palette
     *
     * @access private
     * @param  str $image_src
     * @return array
     */   
    private function random_RGB( $image_src )
    {
        $total = ( imagecolorstotal( $image_src ) <= 0 ) ? 256 : imagecolorstotal( $image_src );
        $red   = ( rand() % 255 );
        $green = ( rand() % 255 );
        $blue  = ( rand() % 255 );

        for ( $i = 1; $i <= $total; $i++ )
        {
            if ( imagecolorexact( $image_src, $red, $green, $blue ) === -1 )
            {
                return array( 'r' => $red,
                              'g' => $green,
                              'b' => $blue );
            }
        }
    }
    
    /**
     * Return filtered/error image
     *
     * @access private
     * @return void
     */   
    private function return_image()
    {
        header( 'Content-type: image/png' );
        
        if ( $this->image_dest )
        {
            imagepng( $this->image_dest );
            imagedestroy( $this->image_src ); 
            imagedestroy( $this->image_dest );   
        }
        else
        {
            imagepng( $this->image_src );
            imagedestroy( $this->image_src ); 
        }
    }
    
    /**
     * Display placeholder image if file isn't an image, doesn't exist or invalid filter
     *
     * @access private
     * @return void
     */   
    private function set_filter_error()
    {
        if ( !$this->image_info || !in_array( $this->image_filter, $this->image_filter_array ) )
        {
            $this->image_src = imagecreatefrompng( 'error.png' );
         
            $this->return_image();   
        }            
    }
    
    /**
     * Process image URL and create image resource
     *
     * @access private
     * @return mixed
     */   
    private function set_image_type()
    {
        $return_val = false;
        
        if ( $this->image_info[2] == 1 )
        {
            $image = $this->image_gif();
            $image_dest = imagecreate( $this->image_info[0], $this->image_info[1] );
            imagecolortransparent( $image_dest, imagecolorallocate( $image_dest, 0, 0, 0 ) ); 
            imagecopy( $image_dest, $image, 0, 0, 0, 0, $this->image_info[0], $this->image_info[1] );
            
            $return_val = $image_dest;
            
            imagedestroy( $image );
        }
        elseif ( $this->image_info[2] == 2 )
        {
            $return_val = $this->image_jpeg();
        }
        elseif ( $this->image_info[2] == 3 )
        {
            $image = $this->image_png();
            imagesavealpha( $image, true );
            
            $return_val = $image;
        }
        
        return $return_val;
    }
 }

$image = new image_filter( $_GET['filename_gd'], $_GET['filter_gd'] );    

/* EOF image_filter.php */
/* Location: ./image_filter.php */