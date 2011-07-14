<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Disqus Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Marc Tanis
 * @link		
 */

class Disqus_mcp {
	
	public $return_data;
	
	private $_base_url;
	private $settings = array();
	private $data = array();
	private $secret_key;
	private $shortname;	
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->_base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=disqus';
		
		$this->EE->cp->set_right_nav(array(
			'module_home'	=> $this->_base_url,
			// Add more right nav items here.
		));
		
		$this->get_settings();
		
		
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		$this->EE->cp->set_variable('cp_page_title', 
								lang('disqus_module_name'));
		
		/**
		 * This is the addons home page, add more code here!
		 */		
		 
   	$this->EE->load->library('javascript');
   	$this->EE->load->library('table');
   	$this->EE->load->helper('form');
  	$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('disqus_module_name'));
  	$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=disqus';
  	$vars['form_hidden'] = NULL;
		 
		
    $this->data['echo'] = "<h1>Disqus Sync</h1>";
    if(	$this->settings['shortname'] != ""){ 

   	$action_id = $this->EE->cp->fetch_action_id('Disqus', 'Sync');
    $link = 'http://'.$_SERVER['SERVER_NAME'].'/?ACT='.$action_id;

     $this->data['echo'] = "<p>Disqus Sync URL  - <a href=\"$link\">$link</a>. Click this link to manually sync comments or to setup a cron job for automatic syncing.</p>";
        	 
   }

		// load libraries
		$this->EE->load->library('form_validation');
		$this->EE->load->library('table');

		// setup validation
		$this->EE->form_validation->set_rules('shortname', 'shortname', 'required');
		$this->EE->form_validation->set_rules('secret_key', 'secret_key', 'required');

		// process form submission
		if( $this->EE->input->post('submit') )
		{
			if( $this->EE->form_validation->run() )
			{
				$this->settings['shortname'] = $this->EE->input->post('shortname');
				$this->settings['secret_key'] = $this->EE->input->post('secret_key');
				
				if( $this->save_settings() )
				{
					$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('config_save_success'));
				}
				else
				{
					$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('config_save_failure'));
				}
			}
			else
			{
				$this->EE->session->set_flashdata('message_failure', validation_errors());
			}

			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=disqus');
		}

		// Set page title
//		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('safeharbor_module_name_settings'));

		// set navigation
		$this->EE->cp->set_right_nav(array(
			'Home'=> BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=disqus'
		));

	$this->shortname = "biblicalcounselingcoalition"; //THe forum $this->shortname for your Disqus account
  $this->secret_key = "xkg5aegXtPdSQHe5zgVb60FswNdBMD5tQ6nKfgIL8rxWDCFZOgXCnw1IIhOxZs4J"; //Disqus API Secret KEY


		// send data to view
		$this->data['settings'] = $this->settings;
		return $this->EE->load->view('settings', $this->data, TRUE);
	}
	
	private function get_settings(){
	  $site_id = $this->EE->config->config['site_id'];
			$settings = $this->EE->db->query("SELECT * FROM ".$this->EE->db->dbprefix('disqus_settings')." WHERE site_id=$site_id LIMIT 1");
			$settings = $settings->row_array();
			unset($settings['id']);
		
		
			$defaults = array(
				'shortname' => '',
				'secret_key' => '',
				'site_id' => $site_id
  			);
  			
			$this->settings = array_merge($defaults, $settings);
			
	  return $this->settings;
	}
  
	private function save_settings(){
	  $query_string = $this->EE->db->insert_string('disqus_settings', $this->settings);
		$query_string = preg_replace('/^INSERT/', 'REPLACE', $query_string);		
		$this->EE->db->query($query_string);
		
	  return TRUE;
	}
	
}
/* End of file mcp.disqus.php */
/* Location: /system/expressionengine/third_party/disqus/mcp.disqus.php */