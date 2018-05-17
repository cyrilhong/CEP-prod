<?php
/*
* @Author 		PickPlugins
* Copyright: 	2015 PickPlugins.com
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

$thisuser = new UP_User( get_current_user_id() );

?> 
<div class="user-profile-edit">

	<?php do_action('user_profile_action_user_profile_edit_before', $thisuser ); ?>	

	<?php if( is_user_logged_in() ) : do_action('user_profile_action_user_profile_edit_main', $thisuser ); ?>
	<?php else : do_action('user_profile_action_user_profile_edit_loggout'); endif; ?>

	<?php do_action('user_profile_action_user_profile_edit_after', $thisuser ); ?>
    
</div>