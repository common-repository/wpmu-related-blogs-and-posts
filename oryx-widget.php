<?php
include_once('oryx-functions.php');

function oryx_widget_display()
{
	global $blog_id;
	global $post;
	
	$oryx_options = array('fp-show' => 1, 'pp-show' => 1, 'pp-type' => 'rp');
	
	if (!get_blog_option($blog_id, 'oryx_widget')){
		add_blog_option($blog_id, 'oryx_widget' , $oryx_options);
	} else {
		$oryx_options = get_blog_option($blog_id, 'oryx_widget');
	}
	
	if(is_front_page()){
		
		// We're on the home page
		echo '<li class="widget">';
		echo '<h2 class="widgettitle">Related Blogs</h2>';
		oryx_relationship_print($blog_id, FALSE, $oryx_options['fp-show']);
		echo '</li>';
	
	} else {
	
		// We're on a post page
		echo '<li class="widget">';
		if($oryx_options['pp-type'] == 'rb'){
			echo '<h2 class="pp">Related Blogs</h2>';
			oryx_relationship_print($blog_id, FALSE, $oryx_options['pp-show']);
		} else {
			echo '<h2>Related Posts</h2>';
			oryx_relationship_print($blog_id, $post->ID, $oryx_options['pp-show']);
		}
		echo '</li>';
		
	}
}

function oryx_widget_control_display()
{
	global $blog_id;
	
	if(isset($_POST['oryx_save'])){
		$oryx_options = array('fp-show' => $_POST['fp-show'], 'pp-show' => $_POST['pp-show'], 'pp-type' => $_POST['pp-type']);
		update_blog_option($blog_id, 'oryx_widget', $oryx_options);
	}
	
	$oryx_options = get_blog_option($blog_id, 'oryx_widget');
	if (!get_blog_option($blog_id, 'oryx_widget')){	
		add_blog_option($blog_id, 'oryx_widget' , array('fp-show' => 1, 'pp-show' => 1, 'pp-type' => 'rp'));
	}
	?>
		<p>
			<strong>Blog front page</strong><br/> Show 
			<select name="fp-show">
				<option value="1" <?php if($oryx_options['fp-show'] == 1):?>selected="selected"<?php endif; ?>>1</option>
				<option value="2" <?php if($oryx_options['fp-show'] == 2):?>selected="selected"<?php endif; ?>>2</option>
				<option value="3" <?php if($oryx_options['fp-show'] == 3):?>selected="selected"<?php endif; ?>>3</option>
				<option value="4" <?php if($oryx_options['fp-show'] == 4):?>selected="selected"<?php endif; ?>>4</option>
				<option value="5" <?php if($oryx_options['fp-show'] == 5):?>selected="selected"<?php endif; ?>>5</option>
				<option value="6" <?php if($oryx_options['fp-show'] == 6):?>selected="selected"<?php endif; ?>>6</option>
				<option value="7" <?php if($oryx_options['fp-show'] == 7):?>selected="selected"<?php endif; ?>>7</option>
				<option value="8" <?php if($oryx_options['fp-show'] == 8):?>selected="selected"<?php endif; ?>>8</option>
				<option value="9" <?php if($oryx_options['fp-show'] == 9):?>selected="selected"<?php endif; ?>>9</option>
				<option value="10" <?php if($oryx_options['fp-show'] == 10):?>selected="selected"<?php endif; ?>>10</option>
			</select> 
			<select>
				<option>related blogs</option>
			</select>
		</p>
		<p>
			<strong>Blog post/page</strong><br/> Show 
			<select name="pp-show">
				<option value="1" <?php if($oryx_options['pp-show'] == 1):?>selected="selected"<?php endif; ?>>1</option>
				<option value="2" <?php if($oryx_options['pp-show'] == 2):?>selected="selected"<?php endif; ?>>2</option>
				<option value="3" <?php if($oryx_options['pp-show'] == 3):?>selected="selected"<?php endif; ?>>3</option>
				<option value="4" <?php if($oryx_options['pp-show'] == 4):?>selected="selected"<?php endif; ?>>4</option>
				<option value="5" <?php if($oryx_options['pp-show'] == 5):?>selected="selected"<?php endif; ?>>5</option>
				<option value="6" <?php if($oryx_options['pp-show'] == 6):?>selected="selected"<?php endif; ?>>6</option>
				<option value="7" <?php if($oryx_options['pp-show'] == 7):?>selected="selected"<?php endif; ?>>7</option>
				<option value="8" <?php if($oryx_options['pp-show'] == 8):?>selected="selected"<?php endif; ?>>8</option>
				<option value="9" <?php if($oryx_options['pp-show'] == 9):?>selected="selected"<?php endif; ?>>9</option>
				<option value="10" <?php if($oryx_options['pp-show'] == 10):?>selected="selected"<?php endif; ?>>10</option>
			</select> 
			<select name="pp-type">
				<option value="rp" <?php if($oryx_options['pp-type'] == 'rp'):?>selected="selected"<?php endif; ?>>related posts</option>
				<option value="rb" <?php if($oryx_options['pp-type'] == 'rb'):?>selected="selected"<?php endif; ?>>related blogs</option>
			</select>
			<input type="hidden" name="oryx_save" value="" />	
		</p>
	<?php
}
?>