<?php
/*
* @Author 		PickPlugins
* Copyright: 	2015 PickPlugins.com
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

global $userprofile;

// echo "<pre>"; print_r(  ); echo "</pre>";

printf( "<div class='addon-container'>" );

foreach( $userprofile->get_addons_data() as $key => $addon ) : 

    $title      = isset( $addon['title'] ) ? $addon['title'] : '';
    $version    = isset( $addon['version'] ) ? $addon['version'] : '';
    $price      = isset( $addon['price'] ) ? $addon['price'] : '';
    $content    = isset( $addon['content'] ) ? $addon['content'] : '';
    $item_link  = isset( $addon['item_link'] ) ? $addon['item_link'] : '';
    $thumb      = isset( $addon['thumb'] ) ? $addon['thumb'] : '';

    printf( "<div class='single'>" );
    printf( "<div class='thumb'><a href='%s'><img src='%s' /></a></div>", $item_link, $thumb );
    printf( "<div class='title'><a href='%s'>%s</a></div>", $item_link, $title );
    printf( "<p class='content'>%s</p>", $content );
    
    printf( "<div class='meta-data'>" );
    printf( "<p class='meta version'><i class='icofont icofont-numbered'></i> %s</p>", $version );

    if( $price == 0 ) printf( "<p class='meta download'><i class='icofont icofont-download-alt'></i> %s</p>", __('Free', 'user-profile') );
    else printf( "<p class='meta price'><i class='icofont icofont-cur-dollar-true'></i> %s</p>", $price );
    
    printf( "</div>" );

    printf( "</div>" );

endforeach;


printf( "</div>" );