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
 * Disqus Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Marc Tanis
 * @link		
 */

class Disqus_upd {
	
	public $version = '1.0';
	
	private $EE;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{
		$mod_data = array(
			'module_name'			=> 'Disqus',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);
		
		$this->EE->db->insert('modules', $mod_data);
		
		
		$data = array(
			'class'		=> 'Disqus' ,
			'method'	=> 'sync'
		);

		$this->EE->db->insert('actions', $data);

		
		 $this->EE->load->dbforge();
		/**
		 * In order to setup your custom tables, uncomment the line above, and 
		 * start adding them below!
		 */
		
		$this->EE->db->query("CREATE TABLE IF NOT EXISTS `".$this->EE->db->dbprefix('disqus_settings')."` (
				`id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
				`site_id` INT(6) NOT NULL,
				`secret_key` varchar(100) NOT NULL,
				`shortname` varchar(100) NOT NULL,
				`last_sync` int(12) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY (`site_id`));");
		
		return TRUE;
	}

	// ----------------------------------------------------------------
	
	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function uninstall()
	{
		$mod_id = $this->EE->db->select('module_id')
								->get_where('modules', array(
									'module_name'	=> 'Disqus'
								))->row('module_id');
		
		$this->EE->db->where('module_id', $mod_id)
					 ->delete('module_member_groups');
		
		$this->EE->db->where('module_name', 'Disqus')
					 ->delete('modules');
		
		 $this->EE->load->dbforge();
		 $this->EE->db->query("DROP TABLE IF EXISTS ".$this->EE->db->dbprefix('disqus_settings'));
 		
		// Delete your custom tables & any ACT rows 
		// you have in the actions table
		
		return TRUE;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function update($current = '')
	{
		// If you have updates, drop 'em in here.
		return TRUE;
	}
	
}
/* End of file upd.disqus.php */
/* Location: /system/expressionengine/third_party/disqus/upd.disqus.php */