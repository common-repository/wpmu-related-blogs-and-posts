<?php
error_reporting(E_ALL);
set_time_limit(0);
include_once('oryx-functions.php');

// This function sends the updated/new posts to either Open Calais or Yahoo Term Extraction (or both)

function oryx_cron_throw()
{
	oryx_debug("------------");
	oryx_debug("Beginning new throw cron - ".date('d/m/Y H:i:s'));
	$joblist = oryx_jobs_get();
	oryx_debug("Throw joblist size = ".count($joblist));
	if(count($joblist) > 0)
	{
		foreach($joblist as $job)
		{
			oryx_debug("Starting throw for {$job->blog_id}:{$job->post_id}");
			$post = oryx_blog_post_details($job->blog_id, $job->post_id);
			$post = $post[0];
			$text = $post->post_title . ' ' .$post->post_content;
			//oryx_debug("{$job->blog_id}:{$job->post_id} text = ".$text);
			
			if(oryx_option_get('use-yahoo') == 1){
				$yh_tags = oryx_yh_send($text);
				oryx_debug("Updating {$job->blog_id}:{$job->post_id} with ".count($yh_tags)." YH tags");
				oryx_blog_add_tags($job->blog_id, $job->post_id, $yh_tags, "extracted");
			}
			
			if(oryx_option_get('use-oc') == 1){
				$oc_tags = oryx_oc_send($text);
				oryx_debug("Updating {$job->blog_id}:{$job->post_id} with ".count($oc_tags)." OC tags");
				oryx_blog_add_tags($job->blog_id, $job->post_id, $oc_tags, "semantic");
			}
			
			
			oryx_jobs_update('web_service', $job->ID);
			oryx_debug("Finished throw for {$job->blog_id}:{$job->post_id}");
		}
	}
	oryx_debug("Finished throw cron");
}

// This function establishes relationships
function oryx_cron_relationships($force = FALSE)
{
	oryx_debug("------------");
	oryx_debug("Beginning new relationship cron - ".date('d/m/Y H:i:s'));
	
	// Get the joblist
	if($force){
		$joblist = oryx_jobs_get('rc');
	} else {
		$joblist = oryx_jobs_get('r');
	}
	
	// Count the joblist
	oryx_debug("Joblist size = ".count($joblist));
	
	
	// If there is a joblist...
	if(count($joblist) > 0)
	{
		// Foreach job
		foreach($joblist as $job)
		{
			oryx_debug("Getting web tags for {$job->blog_id}:{$job->post_id}");
			// Get the semantic tags and extracted terms
			$web = oryx_blog_get_ids_from_tags($job->blog_id, $job->post_id, "web");
			oryx_debug("Got web tags");
			
			oryx_debug("Getting user tags {$job->blog_id}:{$job->post_id}");
			// Get the user tags
			$user = oryx_blog_get_ids_from_tags($job->blog_id, $job->post_id, "user");
			oryx_debug("Got user tags");
			
			oryx_debug("Found ".count($web)." (web) related posts");
			oryx_debug("Found ".count($user)." (user) related posts");
			
			// Establish the what the highest number of related terms/tags is
			$highest = 0;
			$results = array();
			$results[] = array();
			
			if(count($web) > 0){
				foreach($web as $bid=>$pid)
				{
					foreach($pid as $p=>$v){
						if($v > $highest){
							$highest = $v;
						}
						$results[$bid][$p] = $v;
					}
				}
			}
			
			if(count($user) > 0){
				foreach($user as $bid=>$pid)
				{
					foreach($pid as $p=>$v){
						if($v > $highest){
							$highest = $v;
						}
						$results[$bid][$p] = $v;
					}
				}
			}
			
			// If there are results
			if(count($results) > 0){
				
				oryx_debug("Highest = ".$highest);
				
				foreach($results as $result_blog=>$result_post)
				{
					foreach($result_post as $result_p=>$result_value)
					{
						$relevance = ceil(($result_value/$highest)*100);
						if($relevance == 0){ $relevance = 1; }
						$active_blog = $result_blog;
						$active_post = $result_p;
						$passive_blog = $job->blog_id;
						$passive_post = $job->post_id;
						oryx_relationship_create($active_blog, $active_post, $passive_blog, $passive_post, $relevance);
						oryx_debug("Created a relationship between {$active} and {$passive} with a relevance of {$relevance}");
					}
				}
			} else {
				oryx_debug("Did not create any relationships for {$job->blog_id}:{$job->post_id}");
			}
			oryx_jobs_update('relationship', $job->ID);			
		}
	}
	oryx_jobs_clean();
}
?>