<div class="wrap about-wrap acf-wrap">
	
	<h1><?php _e("Welcome to Advanced Custom Fields",'acf'); ?> <?php echo $version; ?></h1>
	<div class="about-text"><?php printf(__("Thank you for updating! ACF %s is bigger and better than ever before. We hope you like it.", 'acf'), $version); ?></div>
	
	<h2 class="nav-tab-wrapper">
		<?php foreach( $tabs as $tab_slug => $tab_title ): ?>
			<a class="nav-tab<?php if( $active == $tab_slug ): ?> nav-tab-active<?php endif; ?>" href="<?php echo admin_url("edit.php?post_type=acf-field-group&page=acf-settings-info&tab={$tab_slug}"); ?>"><?php echo $tab_title; ?></a>
		<?php endforeach; ?>
	</h2>
	
<?php if( $active == 'new' ): ?>
	
	<div class="feature-section">
		<h2><?php _e("A Smoother Experience", 'acf'); ?> </h2>
		<div class="acf-three-col">
			<div>
				<p><img src="https://assets.advancedcustomfields.com/info/5.0.0/select2.png" /></p>
				<h3><?php _e("Improved Usability", 'acf'); ?></h3>
				<p><?php _e("Including the popular Select2 library has improved both usability and speed across a number of field types including post object, page link, taxonomy and select.", 'acf'); ?></p>
			</div>
			<div>
				<p><img src="https://assets.advancedcustomfields.com/info/5.0.0/design.png" /></p>
				<h3><?php _e("Improved Design", 'acf'); ?></h3>
				<p><?php _e("Many fields have undergone a visual refresh to make ACF look better than ever! Noticeable changes are seen on the gallery, relationship and oEmbed (new) fields!", 'acf'); ?></p>
			</div>
			<div>
				<p><img src="https://assets.advancedcustomfields.com/info/5.0.0/sub-fields.png" /></p>
				<h3><?php _e("Improved Data", 'acf'); ?></h3>
				<p><?php _e("Redesigning the data architecture has allowed sub fields to live independently from their parents. This allows you to drag and drop fields in and out of parent fields!", 'acf'); ?></p>
			</div>
		</div>
	</div>
	
	<hr />
	
	<div class="feature-section">
		<h2><?php _e("Goodbye Add-ons. Hello PRO", 'acf'); ?> 👋</h2>
		<div class="acf-three-col">
			<div>
				<h3><?php _e("Introducing ACF PRO", 'acf'); ?></h3>
				<p><?php _e("We're changing the way premium functionality is delivered in an exciting way!", 'acf'); ?></p>
				<p><?php printf(__('All 4 premium add-ons have been combined into a new <a href="%s">Pro version of ACF</a>. With both personal and developer licenses available, premium functionality is more affordable and accessible than ever before!', 'acf'), esc_url('https://www.advancedcustomfields.com/pro')); ?></p>
			</div>
			
			<div>
				<h3><?php _e("Powerful Features", 'acf'); ?></h3>
				<p><?php _e("ACF PRO contains powerful features such as repeatable data, flexible content layouts, a beautiful gallery field and the ability to create extra admin options pages!", 'acf'); ?></p>
				<p><?php printf(__('Read more about <a href="%s">ACF PRO features</a>.', 'acf'), esc_url('https://www.advancedcustomfields.com/pro')); ?></p>
			</div>
			
			<div>
				<h3><?php _e("Easy Upgrading", 'acf'); ?></h3>
				<p><?php _e('Upgrading to ACF PRO is easy. Simply purchase a license online and download the plugin!', 'acf'); ?></p>
				<p><?php printf(__('We also wrote an <a href="%s">upgrade guide</a> to answer any questions, but if you do have one, please contact our support team via the <a href="%s">help desk</a>.', 'acf'), esc_url('https://www.advancedcustomfields.com/resources/upgrade-guide-acf-pro/'), esc_url('https://www.advancedcustomfields.com/support/')); ?></p>
			</div>
		</div>		
	</div>
	
	<hr />
	
	<div class="feature-section">
		
		<h2><?php _e("New Features", 'acf'); ?> 🎉</h2>
		
		<div class="acf-three-col">
			
			<div>
				<h3><?php _e("Link Field", 'acf'); ?></h3>
				<p><?php _e("The Link field provides a simple way to select or define a link (url, title, target).", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("Group Field", 'acf'); ?></h3>
				<p><?php _e("The Group field provides a simple way to create a group of fields.", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("oEmbed Field", 'acf'); ?></h3>
				<p><?php _e("The oEmbed field allows an easy way to embed videos, images, tweets, audio, and other content.", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("Clone Field", 'acf'); ?> <span class="badge"><?php _e('Pro', 'acf'); ?></span></h3>
				<p><?php _e("The clone field allows you to select and display existing fields.", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("More AJAX", 'acf'); ?></h3>
				<p><?php _e("More fields use AJAX powered search to speed up page loading.", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("Local JSON", 'acf'); ?></h3>
				<p><?php _e("New auto export to JSON feature improves speed and allows for syncronisation.", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("Easy Import / Export", 'acf'); ?></h3>
				<p><?php _e("Both import and export can easily be done through a new tools page.", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("New Form Locations", 'acf'); ?></h3>
				<p><?php _e("Fields can now be mapped to menus, menu items, comments, widgets and all user forms!", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("More Customization", 'acf'); ?></h3>
				<p><?php _e("New PHP (and JS) actions and filters have been added to allow for more customization.", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("Fresh UI", 'acf'); ?></h3>
				<p><?php _e("The entire plugin has had a design refresh including new field types, settings and design!", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("New Settings", 'acf'); ?></h3>
				<p><?php _e("Field group settings have been added for Active, Label Placement, Instructions Placement and Description.", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("Better Front End Forms", 'acf'); ?></h3>
				<p><?php _e("acf_form() can now create a new post on submission with lots of new settings.", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("Better Validation", 'acf'); ?></h3>
				<p><?php _e("Form validation is now done via PHP + AJAX in favour of only JS.", 'acf'); ?></p>
			</div>
			
			<div>
				<h3><?php _e("Moving Fields", 'acf'); ?></h3>
				<p><?php _e("New field group functionality allows you to move a field between groups & parents.", 'acf'); ?></p>
			</div>
			
			<div><?php // intentional empty div for flex alignment ?></div>
			
		</div>
			
	</div>
		
<?php elseif( $active == 'changelog' ): ?>
	
	<p class="about-description"><?php printf(__("We think you'll love the changes in %s.", 'acf'), $version); ?></p>
	
	<?php
	
	// extract changelog and parse markdown
	$readme = file_get_contents( acf_get_path('readme.txt') );
	$changelog = '';
	if( preg_match( '/(= '.$version.' =)(.+?)(=|$)/s', $readme, $match ) && $match[2] ) {
		$changelog = acf_parse_markdown( $match[2] );
	}
	echo acf_parse_markdown($changelog);
	
endif; ?>
		
</div>