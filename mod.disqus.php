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
 * Disqus Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Marc Tanis
 * @link		
 */

class Disqus {
	
	public $return_data;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('disqusapi'); 	
    $this->text = 'Discussion';
    $this->id = '';
    $this->link= '';
    $this->int= 60;
    $this->get_settings();
		
    
    
	}
	
	// ----------------------------------------------------------------

	/**
	 * Start on your custom code here...
	 */
	 
	public function comment(){

	  $id = $this->EE->TMPL->fetch_param('id');
    	  
	  $this->return_data = '<div id="disqus_thread"></div>';
    $this->return_data .= '<script type="text/javascript">';
    $this->return_data .= "var disqus_shortname = '".$this->settings['shortname']."';"; 
    $this->return_data .= "var disqus_identifier = '".$this->settings['shortname'].'_'.$id."';";
    $this->return_data .= "(function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();";
  $this->return_data .= "</script>";
  
    
	  return $this->return_data;
	}
	 
	 
 	public function link(){
 	  

  	  $id = $this->EE->TMPL->fetch_param('id');  	  
  	  $link = $this->EE->TMPL->fetch_param('link');
    
 	  
   	  $this->return_data =  '<script type="text/javascript">';
   	  $this->return_data .= "var disqus_shortname = '".$this->settings['shortname']."';"; 
      $this->return_data .= "(function () {
            var s = document.createElement('script'); s.async = true;
            s.type = 'text/javascript';
            s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';
            (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
        }());
    </script>";
     $this->return_data .= '<a href="'.$link.'#disqus_thread" data-disqus-identifier="'.$this->settings['shortname'].'_'.$id.'" class="disqus-comment-link">Discussion</a>';
     return $this->return_data;
 	  
 	}
 	
 	private function check_time($int){
 	  
 	  $last_sync = $this->settings['last_sync'];
 	  
 	  $time = time();
    $next_sync = $time - $int * 60;
 	  
 	  if($last_sync < $next_sync){
 	    return TRUE;
 	  }else{
 	    return FALSE;
 	  }
 	  
 	}
	

	
	public function sync()
	{
	  
	  if(!empty($this->EE->TMPL)){
	    $int = $this->EE->TMPL->fetch_param('int');
	    if($int == 'NOW'){
	      $int = 0;
      }else{
	      $int = ($int > 0 ? $int : 60);  	
      }    
	    
    }else{//If not called from a Template Go ahead and sync now
      $int = 0;
    
    }
    
  if($this->check_time($int)){

	  $disqus = new DisqusAPI($this->settings['secret_key']);

    $threads =  array();

            //Get All Posts
          $posts = $disqus->threads->list(array('forum'=>$this->settings['shortname']));
          foreach($posts as $post){

            $threads[$post->id] = $post;

          }     

            //Get All Comments
            $comments = $disqus->posts->list();

            $comment_info = array();


            foreach($comments as $comment){

              $comment_info['id'] = $comment->id;        
              $comment_info['date'] = $comment->createdAt;
              $comment_info['name'] = $comment->author->name;
              $comment_info['comment'] = $comment->message;
              $comment_info['thread'] = $comment->thread;                   

              
              //Check if the Post exists before processing it
              $post = (!empty($threads[$comment_info['thread']]) ? $threads[$comment_info['thread']] : FALSE);
              
              if($post){

                $id = FALSE;
                $thread_info = array();
                foreach($post->identifiers as $identifier){

                  $id = $identifier;

                }

           
               
                          $params = explode("_",$id);//Retrieve the ENtry ID by splitting out the ID Stored in DB, eg $this->shortname_XXXX [XXXX=entry_id]
                          $thread_info['entry_id'] = end($params);  

                          $thread_info['link'] = $post->link;

                          $sql = "INSERT IGNORE INTO exp_comments (comment_id,site_id,entry_id,channel_id,name,comment_date,comment,status)   
                          SELECT 
                          '".$comment_info['id']."',
                          site_id, 
                          '".$thread_info['entry_id']."',                   
                          channel_id,
                          '".$comment_info['name']."',
                          unix_timestamp('".$comment_info['date']."'),
                          '".mysql_real_escape_string($comment_info['comment'])."',
                          'o' from exp_channel_titles 
                          WHERE entry_id = '".$thread_info['entry_id']."'";

                 $result = $this->EE->db->query($sql);                                  
               }
               
           } 
               $this->settings['last_sync'] = time();
               $this->save_settings();
        
           }
           
		$this->delete_spam($disqus);

    
	}
	
	private function delete_spam($disqus){
		$spam = $disqus->posts->list(array('include'=>'spam'));
		$delete = $disqus->posts->list(array('include'=>'deleted'));        
		$bad = array_merge($spam,$delete);
		$ids = array();
		foreach($bad as $comment){
			$ids[] = $comment->id;                    
		}
		if(count($ids) > 0){
			$idstring = implode(",",$ids);			
			$sql = "SELECT entry_id FROM exp_comments WHERE comment_id IN ($idstring)";
			$result = $this->EE->db->query($sql);
			if ($result->num_rows() > 0)
			{//If there are any comments that need to be deleted, delete them			
				$sql = "DELETE FROM exp_comments WHERE comment_id IN ($idstring)";
				$result = $this->EE->db->query($sql);	
			}
		}
	}
	
	private function save_settings(){
	  $query_string = $this->EE->db->insert_string('disqus_settings', $this->settings);
		$query_string = preg_replace('/^INSERT/', 'REPLACE', $query_string);		
		$this->EE->db->query($query_string);
		
	  return TRUE;
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
	
}
/* End of file mod.disqus.php */
/* Location: /system/expressionengine/third_party/disqus/mod.disqus.php */