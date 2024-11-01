<?php
set_time_limit(0);
include_once("oryx-functions.php");

function oryx_display()
{

    if (isset($_POST['update-context']))
    {
    	$oc_api_key = trim(mysql_real_escape_string($_POST['oc-api-key']));
    	$yahoo_api_key = trim(mysql_real_escape_string($_POST['yahoo-api-key']));
    	$execute = trim(mysql_real_escape_string($_POST['execute']));
    	$tag_relevance = trim(mysql_real_escape_string($_POST['tag-relevance']));
    	$post_relevance = trim(mysql_real_escape_string($_POST['post-relevance']));
    
    	oryx_option_update('oc-api-key', $oc_api_key);
    	if(oryx_option_get('oc-api-key') == ""){ oryx_option_update('use-oc', '0'); } else { oryx_option_update('use-oc', '1'); }
    	
    	oryx_option_update('yahoo-api-key', $yahoo_api_key);
    	if(oryx_option_get('yahoo-api-key') == ""){ oryx_option_update('use-yahoo', '0'); } else { oryx_option_update('use-yahoo', '1'); }
    	
    	oryx_option_update('execute', $execute);
    	oryx_option_update('tag-relevance', $tag_relevance);
    	oryx_option_update('post-relevance', $post_relevance);
	}
	
	if(isset($_POST['run-cron-force'])){
		include_once('oryx-cron.php');
		echo "<div class=\"updated fade\"><p>Beginning cron job. Please do NOT close this browser tab/window.</p></div>";
		flush();
		// Throw everything at Open Calais and Yahoo
		oryx_cron_throw();
		// Establish relationships
		oryx_cron_relationships();
		echo "<div class=\"updated fade\"><p>Cron job completed.</p></div>";
	}
	
	if(isset($_POST['run-cron-archive'])){
		include_once('oryx-cron.php');
		echo "<div class=\"updated fade\"><p>Beginning cron job. Please do NOT close this browser tab/window.</p></div>";
		flush();
		// Make a joblist made of the archive
		oryx_blog_make_archive_joblist();
		// Throw everything at Open Calais and Yahoo
		oryx_cron_throw();
		// Establish relationships
		oryx_cron_relationships();
		echo "<div class=\"updated fade\"><p>Cron job completed.</p></div>";
	}
	
	if(isset($_POST['run-relationship'])){
		include_once('oryx-cron.php');
		echo "<div class=\"updated fade\"><p>Beginning relationships job. Please do NOT close this browser tab/window.</p></div>";
		flush();
		// Make a joblist made of the archive
		oryx_blog_make_archive_joblist();
		
		// Establish relationships
		oryx_cron_relationships(TRUE);
		echo "<div class=\"updated fade\"><p>Cron job completed.</p></div>";
	}
	
	if(isset($_GET['remove_ignored_blog']))
	{
		$btr = $_GET['remove_ignored_blog'];
		oryx_option_delete('ignored-blog', $btr);
	}
	
	if(isset($_POST['ignoreblog']))
	{
		$bti = $_POST['blogtoignore'];
		if($bti !== "0"){
			oryx_option_add('ignored-blog', $bti);
 		}
	}
	?>
	
    <div class="wrap">
    
    	<h2>WPMU Contextual Related Posts</h2>
    	
    	<?php
    	$lr = oryx_option_get('last-run'); if($lr !== "-"): ?>
    	<div class="updated fade">
			<p><strong>Last cron job ran:</strong> <?php echo $lr; ?></p>
		</div>
		<?php endif; ?>
		
		<?php
		$ocapi = oryx_option_get('oc-api-key'); if($ocapi == ""): ?>
		<div class="error">
			<p>Please enter your Open Calais API key</p>
		</div>
		<?php endif;
		?>
		
		<?php
		$yhapi = oryx_option_get('yahoo-api-key'); if($yhapi == ""): ?>
		<div class="error">
			<p>Please enter your Yahoo API key</p>
		</div>
		<?php endif;
		?>
		
		<h3>Configuration</h3>
		
		<form action="" method="post">
			
			<table class="form-table">
				<tbody>
					<tr class="form-field">
						<th scope="row">Open Calais API key <?php if(oryx_option_get('use-oc') == "1"){ echo "(on)"; } else { echo "(off)"; } ?></th>
						<td>
							<input type="text" name="oc-api-key" value="<?php echo oryx_option_get('oc-api-key'); ?>" />
							<p>If this field is empty no content will be thrown at Open Calais</p>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">Yahoo API key <?php if(oryx_option_get('use-yahoo') == "1"){ echo "(on)"; } else { echo "(off)"; } ?></th>
						<td>
							<input type="text" name="yahoo-api-key" value="<?php echo oryx_option_get('yahoo-api-key'); ?>" />
							<p>If this field is empty no content will be thrown at Yahoo Term Extraction service</p>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">Cron executes</th>
						<td>
						
						<?php
						$execute_value = oryx_option_get('execute');
						$selected = array(
							'hourly','twicedaily','daily'
						);
						switch($execute_value)
						{
							case "hourly":
								$selected['hourly'] = 'selected="selected"';
								break;
							case "twicedaily":
								$selected['twicedaily'] = 'selected="selected"';
								break;
							case "daily":
								$selected['daily'] = 'selected="selected"';
								break;
						}
						?>
						
							<select name="execute">
								<option <?php echo $selected['hourly']; ?> value="hourly">Hourly</option>
								<option <?php echo $selected['twicedaily']; ?> value="twicedaily"> Twice daily</option>
								<option <?php echo $selected['daily']; ?> value="daily">Daily</option>
							</select>
						</td>
					</tr>
					
					<tr class="form-field">
						<th scope="row">Minimum tag relevance</th>
						<td>
							<select name="tag-relevance">
								<?php
								$tag_relevance = oryx_option_get('tag-relevance');
								
								$i = 0;
								$ii = 0;
								while($i <= 90)
								{
									if(($ii) == $tag_relevance){ $is_s = 'selected="selected"'; }
									echo '<option '.$is_s.' value="'.($i/100).'">'.$i.'%</option>';
									$i = $i+10;
									$ii = $ii+0.1;
									$is_s = '';
								}
								?>
							</select> 
							<p style="display:inline">The higher the percentage the more relevant tags will be however there will be fewer tags linked to the post.</p>
						</td>
					</tr>
					
					<tr class="form-field">
						<th scope="row">Minimum post relevance</th>
						<td>
							<select name="post-relevance">
								<?php
								$post_relevance = oryx_option_get('post-relevance');
																
								$i = 0;
								$ii = 0;
								while($i <= 90)
								{
									if(($ii) == $post_relevance){ $is_s = 'selected="selected"'; }
									echo '<option '.$is_s.' value="'.($i/100).'">'.$i.'%</option>';
									$i = $i+10;
									$ii = $ii+0.1;
									$is_s = '';
								}
								?>
							</select> 
							<p style="display:inline">The higher the percentage the more relevant linked posts will be however there will be fewer posts will be displayed.</p>
						</td>
					</tr>
					
				</tbody>
			</table>
			
			<p>
				<input type="submit" class="button" name="update-context" value="Save" /> 
			</p>
	
		</form>
		
		<br/>
		
		<h3>Cron Job</h3>
		
		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row">Force cron job</th>
					<td>
						<form method="post">
							<input type="submit" class="button" name="run-cron-force" value="Run" style="width:auto" />
						</form>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row">Tag archive</th>
					<td>
						<form method="post">
							<input type="submit" class="button" name="run-cron-archive" value="Run" style="width:auto" /> <b>Warning!</b> This button starts a process that can take a <u>long</u> time to complete and will hit your server hard. Use at own will!
						</form>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row">Re-establish relationships</th>
					<td>
						<form method="post">
							<input type="submit" class="button" name="run-relationship" value="Run" style="width:auto" /> <b>Warning!</b> This button starts a process that can take a <u>long</u> time to complete and will hit your server hard. Use at own will!
						</form>
					</td>
				</tr>
			</tbody>
		</table>
		
		<br/><br/>
		
		<h3>Blogs to ignore</h3>
		
		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row">Add a blog</th>
					<td>
						<form method="post">
							<select name="blogtoignore">
								<option value="0">Select:</option>
								<?php
								$blog_list = oryx_blog_load_list();
								if(count($blog_list) > 0){
									foreach($blog_list as $blog)
									{
										echo '<option value="'.$blog->blog_id.'">'.$blog->domain.'</option>';
									}
								}
								?>
							</select> <input type="submit" value="Add" class="button" style="width:auto" name="ignoreblog" />
						</form>
					</td>
					
					<?php
					$ignored_blogs = oryx_option_get('ignored-blog');
					if(count($ignored_blogs) > 0)
					{
						$i = 0;
						foreach($ignored_blogs as $ignore)
						{
							if(!empty($ignore)){
								echo '<tr><td>&nbsp;</td><td>'.oryx_blog_domain($ignore->value).' <a href="?page=oryx-display.php&remove_ignored_blog='.$ignore->value.'">remove</a></td></tr>';
							}
							$i++;
						}
					} else {
						echo '<tr><td>&nbsp;</td><td>No blogs are ignored at the moment.</td></tr>';
					}
					?>
						
					
				</tr>
			</tbody>
		</table>
	</div>
	
	
	
<?php
}
?>