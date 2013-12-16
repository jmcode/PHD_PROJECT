<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>> <!--<![endif]-->
<head>
	<title><?php wp_title( ' ', true, 'right' ); /* filtered in libraries/filers.php */ ?></title>	
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />		
	<link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/apple-touch-icon.png" />	
	<link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png" />
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />	
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php wp_head(); /* do not remove this */ ?>
	<!--[if IE]><script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/ie.js"></script><![endif]-->			
</head>
<body <?php body_class(); ?>>

	<!-- header -->
	<header id="header">
		<div class="header-holder">
			
			<div class="logogroup">
				<!-- logo -->
				<h1 class="logo"><?php echo cudazi_get_logo(); ?></h1>		
				<!-- tagline -->			
				<?php if ( ! cudazi_get_option( 'disable_tagline', false ) ) { ?><em class="tagline"><?php bloginfo( 'description' ); ?></em><?php } ?>			
			</div>
			
			<!-- main navigation -->
			<nav class="main-nav">				
				<?php wp_nav_menu( array( 'menu_id' => 'nav', 'theme_location' => 'primary', 'fallback_cb' => 'cudazi_menu_fallback' ) ); ?>
				<?php echo cudazi_alternate_menu( array( 'menu_name' => 'primary', 'display' => 'select' ) ); ?>
			</nav><!-- //main-nav -->
						
		</div><!-- //header-holder -->
	</header><!-- //header -->
		
	<!-- wrapper -->
	<div id="wrapper">
		<!-- main -->
		<div id="main">
