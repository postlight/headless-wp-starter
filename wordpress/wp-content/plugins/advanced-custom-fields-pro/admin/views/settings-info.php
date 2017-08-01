<?php 

// extract args
extract( $args );

?>
<div class="wrap about-wrap acf-wrap">
	
	<h1><?php _e("Welcome to Advanced Custom Fields",'acf'); ?> <?php echo $version; ?></h1>
	<div class="about-text"><?php printf(__("Thank you for updating! ACF %s is bigger and better than ever before. We hope you like it.", 'acf'), $version); ?></div>
	<div class="acf-icon logo">
		<i class="acf-sprite-logo"></i>
	</div>
	
	<h2 class="nav-tab-wrapper">
		<?php foreach( $tabs as $tab_slug => $tab_title ): ?>
			<a class="nav-tab<?php if( $active == $tab_slug ): ?> nav-tab-active<?php endif; ?>" href="<?php echo admin_url("edit.php?post_type=acf-field-group&page=acf-settings-info&tab={$tab_slug}"); ?>"><?php echo $tab_title; ?></a>
		<?php endforeach; ?>
	</h2>
	
<?php if( $active == 'new' ): ?>
	
	<h2 class="about-headline-callout"><?php _e("A smoother custom field experience", 'acf'); ?></h2>
	
	<div class="feature-section acf-three-col">
		<div>
			<img src="https://assets.advancedcustomfields.com/info/5.0.0/select2.png">
			<h3><?php _e("Improved Usability", 'acf'); ?></h3>
			<p><?php _e("Including the popular Select2 library has improved both usability and speed across a number of field types including post object, page link, taxonomy and select.", 'acf'); ?></p>
		</div>
		<div>
			<img src="https://assets.advancedcustomfields.com/info/5.0.0/design.png">
			<h3><?php _e("Improved Design", 'acf'); ?></h3>
			<p><?php _e("Many fields have undergone a visual refresh to make ACF look better than ever! Noticeable changes are seen on the gallery, relationship and oEmbed (new) fields!", 'acf'); ?></p>
		</div>
		<div>
			<img src="https://assets.advancedcustomfields.com/info/5.0.0/sub-fields.png">
			<h3><?php _e("Improved Data", 'acf'); ?></h3>
			<p><?php _e("Redesigning the data architecture has allowed sub fields to live independently from their parents. This allows you to drag and drop fields in and out of parent fields!", 'acf'); ?></p>
		</div>
	</div>
	
	<hr />
	
	<h2 class="about-headline-callout"><?php _e("Goodbye Add-ons. Hello PRO", 'acf'); ?></h2>
	
	<div class="feature-section acf-three-col">
	
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
			<p><?php printf(__('To help make upgrading easy, <a href="%s">login to your store account</a> and claim a free copy of ACF PRO!', 'acf'), esc_url('https://www.advancedcustomfields.com/my-account/')); ?></p>
			<p><?php printf(__('We also wrote an <a href="%s">upgrade guide</a> to answer any questions, but if you do have one, please contact our support team via the <a href="%s">help desk</a>', 'acf'), esc_url('https://www.advancedcustomfields.com/resources/updates/upgrading-v4-v5/'), esc_url('https://support.advancedcustomfields.com')); ?>
			
		</div>
					
	</div>
	
	<hr />
	
	<h2 class="about-headline-callout"><?php _e("Under the Hood", 'acf'); ?></h2>
	
	<div class="feature-section acf-three-col">
		
		<div>
			<h4><?php _e("Smarter field settings", 'acf'); ?></h4>
			<p><?php _e("ACF now saves its field settings as individual post objects", 'acf'); ?></p>
		</div>
		
		<div>
			<h4><?php _e("More AJAX", 'acf'); ?></h4>
			<p><?php _e("More fields use AJAX powered search to speed up page loading", 'acf'); ?></p>
		</div>
		
		<div>
			<h4><?php _e("Local JSON", 'acf'); ?></h4>
			<p><?php _e("New auto export to JSON feature improves speed", 'acf'); ?></p>
		</div>
		
		<br />
		
		<div>
			<h4><?php _e("Better version control", 'acf'); ?></h4>
			<p><?php _e("New auto export to JSON feature allows field settings to be version controlled", 'acf'); ?></p>
		</div>
		
		<div>
			<h4><?php _e("Swapped XML for JSON", 'acf'); ?></h4>
			<p><?php _e("Import / Export now uses JSON in favour of XML", 'acf'); ?></p>
		</div>
		
		<div>
			<h4><?php _e("New Forms", 'acf'); ?></h4>
			<p><?php _e("Fields can now be mapped to comments, widgets and all user forms!", 'acf'); ?></p>
		</div>
		
		<br />
		
		<div>
			<h4><?php _e("New Field", 'acf'); ?></h4>
			<p><?php _e("A new field for embedding content has been added", 'acf'); ?></p>
		</div>
		
		<div>
			<h4><?php _e("New Gallery", 'acf'); ?></h4>
			<p><?php _e("The gallery field has undergone a much needed facelift", 'acf'); ?></p>
		</div>
		
		<div>
			<h4><?php _e("New Settings", 'acf'); ?></h4>
			<p><?php _e("Field group settings have been added for label placement and instruction placement", 'acf'); ?></p>
		</div>
		
		<br />
		
		<div>
			<h4><?php _e("Better Front End Forms", 'acf'); ?></h4>
			<p><?php _e("acf_form() can now create a new post on submission", 'acf'); ?></p>
		</div>
		
		<div>
			<h4><?php _e("Better Validation", 'acf'); ?></h4>
			<p><?php _e("Form validation is now done via PHP + AJAX in favour of only JS", 'acf'); ?></p>
		</div>
		
		<div>
			<h4><?php _e("Relationship Field", 'acf'); ?></h4>
			<p><?php _e("New Relationship field setting for 'Filters' (Search, Post Type, Taxonomy)", 'acf'); ?></p>
		</div>
		
		<br />
		
		<div>
			<h4><?php _e("Moving Fields", 'acf'); ?></h4>
			<p><?php _e("New field group functionality allows you to move a field between groups & parents", 'acf'); ?></p>
		</div>
		
		<div>
			<h4><?php _e("Page Link", 'acf'); ?></h4>
			<p><?php _e("New archives group in page_link field selection", 'acf'); ?></p>
		</div>
		
		<div>
			<h4><?php _e("Better Options Pages", 'acf'); ?></h4>
			<p><?php _e("New functions for options page allow creation of both parent and child menu pages", 'acf'); ?></p>
		</div>
					
	</div>
		
	
	
<?php elseif( $active == 'changelog' ): ?>
	
	<p class="about-description"><?php printf(__("We think you'll love the changes in %s.", 'acf'), $version); ?></p>
	
	<?php
		
	$items = file_get_contents( acf_get_path('readme.txt') );
	$items = explode('= ' . $version . ' =', $items);
	
	$items = end( $items );
	$items = current( explode("\n\n", $items) );
	$items = array_filter( array_map('trim', explode("*", $items)) );
	
	?>
	<ul class="changelog">
	<?php foreach( $items as $item ): 
		
		$item = explode('http', $item);
			
		?>
		<li><?php echo $item[0]; ?><?php if( isset($item[1]) ): ?><a href="http<?php echo $item[1]; ?>" target="_blank">[...]</a><?php endif; ?></li>
	<?php endforeach; ?>
	</ul>

<?php endif; ?>
		
</div>