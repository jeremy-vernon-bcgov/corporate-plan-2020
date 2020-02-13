<nav class="wtbx_header_part wtbx_menu_nav">
	<?php
	$menu_array = array();

	if ( !empty($props['nav']) ) {
		$menu = $props['nav'];
		$menu_array = array('menu' => $menu);
	} else {
		if ( $header_style === 'm' ) {
			$menu = wtbx_option_levelled('header-m-menu');
			$locations = get_nav_menu_locations();

			if ( $menu === '' ) {
				wtbx_option_levelled('header-menu');
			}
			if ( !empty($menu) ) {
				$menu_array = array('menu' => $menu);
			} else {
				if ( isset($locations['mobile']) && !empty(wp_get_nav_menu_object( $locations['mobile'] )) ) {
					$menu_array = array('theme_location'  => 'mobile');
				}
			}

			if ( isset($locations['header']) && !empty(wp_get_nav_menu_object( $locations['header'] )) &&
				isset($locations['mobile']) && !empty(wp_get_nav_menu_object( $locations['mobile'] ))) {
				$header_menu = get_term($locations['header'], 'nav_menu')->slug;
				$mobile_menu = get_term($locations['mobile'], 'nav_menu')->slug;

				if ( $header_menu === $mobile_menu ) {
					$menu_array = array();
					?><span class="wtbx_header_placeholder" data-slug="<?php echo $header_menu ?>"></span><?php
				}
            }
		} else {
			$menu = wtbx_option_levelled('header-menu');

			if ( !empty($menu) ) {
				$menu_array = array('menu' => $menu);
			} else {
				$locations = get_nav_menu_locations();
				if ( isset($locations['header']) && !empty(wp_get_nav_menu_object( $locations['header'] )) ) {
					$menu_array = array('theme_location'  => 'header');
				}
			}
		}
	}


	if ( !empty($menu_array) ) {
		wp_nav_menu($menu_array);
	}
	?>
</nav>