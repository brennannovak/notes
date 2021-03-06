<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:			Social Igniter : Notes : Home
* Author: 		Brennan Novak
* 		  		contact@social-igniter.com
* 
* Project:		http://social-igniter.com
* 
* Description: This file is for the Notes Home Controller
*/
class Home extends Dashboard_Controller
{
    function __construct()
    {
        parent::__construct();

		$this->load->config('notes');
		$this->load->library('notes_library');

		$this->data['page_title'] = 'Notes';
	}

	function _remap($method)
	{
 		if ($this->session->userdata('user_level_id') > config_item('notes_view_permission')) redirect(login_redirect());

 		$timeline		= NULL;
		$timeline_view	= NULL;
		
 	   	// Load
		$this->data['home_greeting']	= random_element($this->lang->line('home_greeting'));
	 	$this->data['social_post'] 		= $this->social_igniter->get_social_post($this->session->userdata('user_id'), 'social_post_horizontal');
		$this->data['groups']	 		= $this->notes_library->make_group_dropdown($this->session->userdata('user_id'), $this->session->userdata('user_level_id'), '+ Add Group'); 
 		$this->data['group_id']			= '';
 		
 		// Pick Type of Feed
		if ($this->uri->total_segments() == 2)
		{
	 	    $this->data['page_title'] 		= 'Notes';
			$timeline 						= $this->notes_library->get_timeline(NULL, 10);
 	    }
 	    elseif ($this->uri->segment(3) == 'friends')
 	    {
	 	    $this->data['page_title'] 		= 'Friends';

			if ($friends = $this->social_tools->get_relationships_owner($this->session->userdata('user_id'), 'user', 'follow'))
			{
				$timeline = $this->notes_library->get_timeline_friends($friends, 10);
			}
 	    }
 	    elseif ($this->uri->segment(3) == 'likes')
 	    {
	 	    $this->data['page_title'] 		= 'Likes';

			$likes							= $this->social_tools->get_ratings_likes_user($this->session->userdata('user_id'));
			$timeline 						= $this->notes_library->get_timeline_likes($likes, 10); 
 	    }
 	    elseif ($this->uri->segment(3) == 'group')
 	    {
 	    	$group 							= $this->social_tools->get_category($this->uri->segment(3));

	 	    $this->data['page_title'] 		= $group->category;
			$this->data['group_id']			= $this->uri->segment(3);

			$timeline 						= $this->notes_library->get_timeline_group($group->category_id, 10); 
 	    }
 	    // Fix For MODULE Checking
 	    else
 	    {
	 	    $this->data['page_title'] 		= display_nice_file_name($this->uri->segment(2));
 			$this->data['sub_title']		= 'Recent';

			$timeline 						= $this->notes_library->get_timeline($this->uri->segment(2), 10); 
 	    }

		// Build Feed				 			
		if (!empty($timeline))
		{
			foreach ($timeline as $activity)
			{			
				// Item
				$this->data['item_id']				= $activity->activity_id;
				$this->data['item_type']			= item_type_class($activity->type);
				
				// Contributor
				$this->data['item_user_id']			= $activity->user_id;
				$this->data['item_avatar']			= $this->social_igniter->profile_image($activity->user_id, $activity->image, $activity->gravatar, 'medium', 'dashboard_theme');
				$this->data['item_contributor']		= $activity->name;
				
				// User Profile
				$this->data['item_profile']			= base_url().'profile/'.$activity->username;
				
				// Activity
				$this->data['item_content']			= $this->notes_library->render_item($activity);
				$this->data['item_content_id']		= $activity->content_id;
				$this->data['item_date']			= format_datetime(config_item('notes_date_style'), $activity->created_at);			
				$this->data['item_url']				= base_url().'notes/'.$activity->content_id;
		
				if ($activity->site_id != config_item('site_id'))
				{
					$this->data['item_source']		= ' via <a href="'.prep_url(property_exists($activity,'canonical')&&$activity->canonical?$activity->canonical:$activity->url).'" target="_blank">'.$activity->title.'</a>';
				}

		 		// Actions
			 	$this->data['item_comment']			= base_url().'comment/item/'.$activity->activity_id;
			 	$this->data['item_comment_avatar']	= $this->data['logged_image'];
			 	
			 	$this->data['item_can_modify']		= $this->social_auth->has_access_to_modify('activity', $activity, $this->session->userdata('user_id'), $this->session->userdata('user_level_id'));
				$this->data['item_edit']			= base_url().'home/'.$activity->module.'/manage/'.$activity->content_id;
				$this->data['item_delete']			= base_url().'api/activity/destroy/id/'.$activity->activity_id;

				// View
				$timeline_view .= $this->load->view('../modules/notes/views/partials/item_timeline.php', $this->data, true);
	 		}
	 	}
	 	else
	 	{
	 		$timeline_view = '<li><p>Nothing to show from anyone!</p></li>';
 		}	

		// Final Output
		$this->data['timeline_view'] 	= $timeline_view;
		$this->data['timeline_template']= $this->item_timeline();		

		$this->render('dashboard_wide');		
	}

	
	function item_timeline()
	{
		$this->data['item_id']				= '{ITEM_ID}';
		$this->data['item_type']			= '{ACTIVITY_TYPE}';
		
		// Contributor
		$this->data['item_user_id']			= '{ITEM_USER_ID}';
		$this->data['item_avatar']			= '{ITEM_AVATAR}';
		$this->data['item_contributor']		= '{ITEM_CONTRIBUTOR}';
		$this->data['item_profile']			= base_url().'profiles/{ITEM_PROFILE}';
		
		// Activity
		$this->data['item_content']			= '{ITEM_CONTENT}';
		$this->data['item_content_id']		= '{ITEM_CONTENT_ID}';
		$this->data['item_date']			= '{ITEM_DATE}';
		$this->data['item_url']				= '{ITEM_URL}';

 		// Actions
		$this->data['item_comment']			= base_url().'comment/item/{ACTIVITY_ID}';
		$this->data['item_comment_avatar']	= '{ITEM_COMMENT_AVATAR}';

	 	$this->data['item_can_modify']		= '{ITEM_CAN EDIT}';
		$this->data['item_edit']			= base_url().'home/{ACTIVITY_MODULE}/manage/{ITEM_CONTENT_ID}';
		$this->data['item_delete']			= base_url().'status/delete/{ACTIVITY_ID}';			
	
		return $this->load->view('../modules/notes/views/partials/item_timeline', $this->data, true);
	}
	
}