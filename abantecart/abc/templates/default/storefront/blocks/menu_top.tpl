<div class="topnavbar navbar" id="topnav">
	<span class="sr-only"><?php echo $heading_title; ?></span>
 	<ul id="main_menu_top" class="nav navbar-nav main_menu">
	<?php
	    foreach ($storemenu as $i => $menu_item) {
	 	   if ($menu_item['id'] == 'home') {
	    		unset($storemenu[$i]);
	    		break;
	    	}
	    }?>
	    <?php 
	    	//NOTE:
	    	//HTML tree built in helper/html.php
	    	//To control look and style of the menu use CSS in styles.css
	    ?>
	    <?php echo \abc\core\helper\AHelperHtml::buildStoreFrontMenuTree( $storemenu ); ?>
	</ul>
</div>