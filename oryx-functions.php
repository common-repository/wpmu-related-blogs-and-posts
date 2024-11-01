<?php
/***************************************************************************
	
	Oryx blog/post functions
	
***************************************************************************/
global $wpdb;

function oryx_blog_post_details($blog_id, $post_id)
{
	global $wpdb;
	
	$select = "SELECT `ID`, `post_title`, `post_content`";
	$from   = " FROM `wp_{$blog_id}_posts` AS `p`";
	$where  = " WHERE `ID` = '{$post_id}'";
	$sql = "{$select} {$from} {$where}";

	return $wpdb->get_results($sql);
}

function oryx_blog_add_tags($blog_id, $post_id, $tags, $source)
{
	global $wpdb;

	if(count($tags) > 0 && $tags !== FALSE)
	{
		$min_tag_rel = oryx_option_get('tag-relevance');
		foreach($tags as $tag=>$tag_value)
		{
			$tag = mysql_real_escape_string($tag);
			$rel = $tag_value;
			if($rel >= $min_tag_rel){
				$sql = "INSERT IGNORE INTO `oryx_tags` SET `tag` = '{$tag}', `blog_id` = '{$blog_id}', `post_id` = '{$post_id}', `relevance` = '{$rel}', `source` = '{$source}'";
				$wpdb->query($sql);
			}
		}
	}
}

function oryx_blog_get_tags($blog_id, $post_id)
{
	global $wpdb;
	$sql = "SELECT tag FROM `oryx_tags` `blog_id` = '{$blog_id}' AND `post_id` = '{$post_id}'";
	$tags = $wpdb->get_results($sql);
	if(count($tags) > 0){
		return $tags;
	} else {
		return FALSE;
	}
}

function oryx_blog_get_ids_from_tags($blog_id, $post_id, $service)
{
	global $wpdb;
	
	switch($service)
	{
		case "web":
			$get_blog_tags = "SELECT `tag` FROM `oryx_tags` WHERE `blog_id` = '{$blog_id}' AND `post_id` = '{$post_id}'";
			$get_blog_tags = $wpdb->get_results($get_blog_tags);
			
			$blog_ids = array();
			$blog_ids[] = array();
			
			if(count($get_blog_tags) > 0){
				$blogs = oryx_blog_load_list();
				if(count($blogs) > 0){
					foreach($blogs as $blog)
					{
						foreach($get_blog_tags as $tag)
						{
							$sql = "SELECT DISTINCT `post_id` FROM `oryx_tags` WHERE `tag` = '{$tag->tag}' AND `blog_id` = '{$blog->blog_id}' AND `post_id` <> '{$post_id}'";
							$get_ids = $wpdb->get_results($sql);
							if(count($get_ids) > 0){
								foreach($get_ids as $get_id)
								{
									$blog_ids[$blog->blog_id][$get_id->post_id]++;
								}
							}
						}
					}
				}
			}
			return $blog_ids;
		break;
		case "user":
			$dump = array();
			$dump[] = array();
			$blogs = oryx_blog_load_list();
			foreach($blogs as $blog)
			{
				$testing_blog_id = $blog->blog_id;
				$sql = "SELECT wp_{$testing_blog_id}_term_relationships.object_id, count(object_id) as cnt FROM wp_{$testing_blog_id}_term_relationships WHERE wp_{$testing_blog_id}_term_relationships. term_taxonomy_id IN (SELECT wp_{$testing_blog_id}_term_taxonomy. term_taxonomy_id FROM wp_{$testing_blog_id}_term_taxonomy WHERE wp_{$testing_blog_id}_term_taxonomy.term_id IN (SELECT wp_{$testing_blog_id}_terms.term_id FROM wp_{$testing_blog_id}_terms WHERE wp_{$testing_blog_id}_terms.name IN (SELECT wp_{$blog_id}_terms.name FROM wp_{$blog_id}_term_relationships, wp_{$blog_id}_terms, wp_{$blog_id}_term_taxonomy WHERE wp_{$blog_id}_term_relationships.object_id = {$post_id} AND wp_{$blog_id}_term_relationships.term_taxonomy_id = wp_{$blog_id}_term_taxonomy.term_taxonomy_id AND wp_{$blog_id}_terms.term_id = wp_{$blog_id}_term_taxonomy.term_id))) GROUP BY (object_id) ORDER BY cnt DESC";
				$result = $wpdb->get_results($sql);
				if(count($result) > 0){
					foreach($result as $r)
					{
						$dump[$testing_blog_id][] = $r->cnt;
					}
				}
			}
			return $dump;
		break;
	}
}

function oryx_blog_make_archive_joblist()
{
	global $wpdb;
	
	$blogs = oryx_blog_load_list();
	
	if(count($blogs) > 0){
		foreach($blogs as $blog)
		{
			$test = $wpdb->get_var("SELECT `id` FROM `oryx_options` WHERE `key` = 'ignored-blog' AND `value` = '{$blog->blog_id}'");
			
			if(count($test) == 0){
				$sql = "SELECT `ID` FROM `wp_{$blog->blog_id}_posts` WHERE `post_type` IN ('page','post') AND `post_status` = 'publish'";
				$get_posts = $wpdb->get_results($sql);
				if(count($get_posts) > 0){
					foreach($get_posts as $post)
					{
						oryx_jobs_add($post->ID, $blog->blog_id);
					}
				}
			}
		}
	}
}

function oryx_blog_domain($id)
{
	global $wpdb;
	$sql = 'SELECT `domain` FROM `wp_blogs` WHERE `blog_id` = "'.$id.'"';
	return $wpdb->get_var($sql);
}

function oryx_blog_title($blog_id)
{
	global $wpdb;
	$sql = "SELECT option_value FROM wp_{$blog_id}_options WHERE option_name = 'blogname'";
	return $wpdb->get_var($sql);
}

function oryx_blog_url($blog_id)
{
	global $wpdb;
	$sql = "SELECT option_value FROM wp_{$blog_id}_options WHERE option_name = 'siteurl'";
	return $wpdb->get_var($sql);
}

function oryx_post_title($blog_id, $post_id)
{
	global $wpdb;
	$sql = "SELECT `post_title` FROM `wp_{$blog_id}_posts` WHERE `ID` = {$post_id}";
	return $wpdb->get_var($sql);
}

function oryx_blog_load_list()
{
	global $wpdb;
	$sql = 'SELECT `blog_id`, `domain` FROM `wp_blogs` WHERE `deleted` = 0';
	return $wpdb->get_results($sql);
}


/***************************************************************************
	
	Oryx option functions
	
***************************************************************************/

function oryx_option_get($option)
{
	global $wpdb;
	$sql = "SELECT `value` FROM `oryx_options` WHERE `key` = '{$option}'";
	if($option !== "ignored-blog"){
		return $wpdb->get_var($sql);
	} else {
		return $wpdb->get_results($sql);
	}
}

function oryx_option_add($key, $value)
{
	global $wpdb;
	$sql = "INSERT IGNORE INTO `oryx_options` (`key`, `value`) VALUES ('{$key}', '{$value}')";
	$wpdb->query($sql);
}

function oryx_option_delete($key, $value=NULL)
{
	global $wpdb;
	if($value !== NULL){
		$sql = "DELETE IGNORE FROM `oryx_options` WHERE `key` = '{$key}' AND `value` = '{$value}'";
	} else {
		$sql = "DELETE IGNORE FROM `oryx_options` WHERE `key` = '{$key}'";
	}
	$wpdb->query($sql);
}

function oryx_option_update($key, $value)
{
	global $wpdb;
	$sql = "UPDATE `oryx_options` SET `value` = '{$value}' WHERE `key` = '{$key}'";
	$wpdb->query($sql);
}




/***************************************************************************
	
	Oryx joblist functions
	
***************************************************************************/
function oryx_jobs_add($post_id, $blogid=null)
{
	global $blog_id;
	if($blogid !== NULL){ $blog_id = $blogid; }
	global $wpdb;
	
	$test = $wpdb->get_var("SELECT `id` FROM `oryx_options` WHERE `key` = 'ignored-blog' AND `value` = '{$blog_id}'");
	if(count($test) == 0){
		$sql = "INSERT IGNORE INTO `oryx_joblist` (`blog_id`, `post_id`) VALUES ('{$blog_id}','{$post_id}')";
		$wpdb->query($sql);
	}
}

function oryx_jobs_get($get='all')
{
	global $wpdb;
	switch($get)
	{
		case "all":
		case "ws":
			$sql = 'SELECT `ID`, `blog_id`, `post_id` FROM `oryx_joblist` WHERE `web_service` = 0 AND `relationship` = 0';
			break;
		case "r":
			$sql = 'SELECT `ID`, `blog_id`, `post_id` FROM `oryx_joblist` WHERE `web_service` = 1 AND `relationship` = 0';
			break;
		case "rc":
			$pre = 'UPDATE `oryx_joblist` SET `web_service` = 1';
			$wpdb->query($pre);
			$sql = 'SELECT `ID`, `blog_id`, `post_id` FROM `oryx_joblist` WHERE `web_service` = 1 AND `relationship` = 0';
			break;
	}
	return $wpdb->get_results($sql);
}

function oryx_jobs_update($column, $id)
{
	global $wpdb;
	$sql = "UPDATE `oryx_joblist` SET `{$column}` = 1 WHERE `id` = {$id}";
	$wpdb->query($sql);
}

function oryx_jobs_clean()
{
	global $wpdb;
	$sql = 'DELETE FROM `oryx_joblist` WHERE `web_service` = 1 AND `relationship` = 1';
	return $wpdb->query($sql);
}




/***************************************************************************
	
	Oryx relationship functions
	
***************************************************************************/

function oryx_relationship_create($active_blog, $active_post, $passive_blog, $passive_post, $relevance = FALSE)
{
	global $wpdb;
	if($relevance == FALSE)
	{
		$relevance = 50;
	}
	if($active_blog !== $passive_blog){
		$activeq = "INSERT IGNORE INTO oryx_relationships (active_blog, active_post, passive_blog, passive_post, relevance) VALUES ('{$active_blog}', '{$active_post}', '{$passive_blog}', '{$passive_post}', '{$relevance}')";
		$passiveq = "INSERT IGNORE INTO oryx_relationships (active_blog, active_post, passive_blog, passive_post, relevance) VALUES ('{$passive_blog}', '{$passive_post}', '{$active_blog}', '{$active_post}', '{$relevance}')";
		$wpdb->query($activeq);
		$wpdb->query($passiveq);

	}
}

function oryx_relationship_get($passive_blog, $passive_post=FALSE)
{
	global $wpdb;
	if($passive_post == FALSE){
		$sql = "SELECT DISTINCT `active_blog`, count(active_blog) as `c` FROM `oryx_relationships` WHERE `passive_blog` = '{$passive_blog}' AND `active_blog` <> '{$passive_blog}' AND `active_blog` NOT IN (SELECT `value` FROM oryx_options WHERE `key` = 'ignored-blog') GROUP BY(active_blog) ORDER BY `c` DESC, `relevance` DESC";
	} else {
		$sql = "SELECT DISTINCT `active_blog`, `active_post`, `relevance` FROM `oryx_relationships` WHERE `passive_blog` = '{$passive_blog}' AND `passive_post` = '{$passive_post}' AND `active_blog` <> '{$passive_blog}' AND `active_blog` NOT IN (SELECT `value` FROM oryx_options WHERE `key` = 'ignored-blog') ORDER BY relevance DESC";
	}
	
	$results = $wpdb->get_results($sql);
	$return = $results;
	
	if(count($results) > 0){
		$i = 0;
		foreach($results as $result)
		{
			// Check it's not set to private
			$check = "SELECT option_value FROM wp_".$result->active_blog."_options WHERE option_name = 'blog_public'";
			if($wpdb->get_var($check) !== "1"){
				unset($return[$i]); 	
			}
			$i++;
		}
	
	}
	
	return $return;
}

function oryx_relationship_print($blog_id, $post_id=FALSE, $count)
{
	global $wpdb;
	// Related blogs
	if($post_id == FALSE){
	
		$results = oryx_relationship_get($blog_id, FALSE);
		if(count($results) > 0)
		{
			echo '<ul>';
			$i = 1;
			foreach($results as $result)
			{
				if($i <= $count){
					$blogid = $result->active_blog;
					$blogname = oryx_blog_title($blogid);
					$blogurl = oryx_blog_url($blogid);
					echo '<li>';
						echo '<a href="'.$blogurl.'">'.$blogname.'</a>';
					echo '</li>'."\n";
				}
				$i++;
			}
			echo '</ul>';
		
		} else {
			echo "<p>Couldn't find any related blogs.</p>";
		}
		
	// Related posts
	} else {
		
		$results = oryx_relationship_get($blog_id, $post_id);
		if(count($results) > 0)
		{
			echo '<ul>';
			$i = 1;
			foreach($results as $result)
			{
				if($i <= $count){
					$blogid = $result->active_blog;
					$postid = $result->active_post;
					$postname = oryx_post_title($blogid, $postid);
					$blogurl = oryx_blog_url($blogid);
					echo '<li>';
						echo '<a href="'.$blogurl.'?p='.$postid.'">'.$postname.'</a>';
					echo '</li>'."\n";
				}
				$i++;
			}
			echo '</ul>';
		} else {
			echo "<p>Couldn't find any related posts.</p>";
		}
		
	}
}

function oryx_relationship_delete()
{
	global $wpdb;
	global $blog_id;
	// Delete active relationship
	$active = "DELETE IGNORE FROM oryx_relationships WHERE `active` = '{$blog_id}:{$post_id}'";
	// Delete passive relationship
	$passive = "DELETE IGNORE FROM oryx_relationships WHERE `passive` = '{$blog_id}:{$post_id}'";
	$wpdb->query($active);
	$wpdb->query($passive);
}


/***************************************************************************
	
	Oryx Open Calais functions
	
***************************************************************************/

function oryx_oc_send($text)
{
	include_once('oc.php');
	$oc = new OpenCalais(oryx_option_get('oc-api-key'), $text);
	return $oc->tags;
}



/***************************************************************************
	
	Oryx Yahoo API functions
	
***************************************************************************/

function oryx_yh_send($text)
{
	include_once('yh.php');
	$yh = new Yahoo(oryx_option_get('yahoo-api-key'), $text);
	return $yh->tags;
}




/***************************************************************************
	
	Oryx debug
	
***************************************************************************/
function oryx_debug($message, $flush=TRUE)
{
	global $wpdb;
	$message = mysql_real_escape_string($message);
	$sql = "INSERT INTO `oryx_debug` (`message`) VALUES ('{$message}')";
	$wpdb->query($sql);
	if($flush){
		//echo $message."<br/>\n";
		//flush();
	}
}

function oryx_debug_empty()
{
	global $wpdb;
	$sql = 'TRUNCATE oryx_debug';
	$wpdb->query($sql);
}

define('ORYX_TAG_RELEVENCE', oryx_option_get('tag-relevence'));
define('ORYX_OC_API_KEY', oryx_option_get('oc-api-key'));
define('ORYX_YH_API_KEY', oryx_option_get('yahoo-api-key'));
define('ORYX_USE_OC', oryx_option_get('use-oc'));
define('ORYX_USE_YH', oryx_option_get('use-yahoo'));
?>