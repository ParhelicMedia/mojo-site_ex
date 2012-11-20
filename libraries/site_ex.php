<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * site_ex is an extension to the default MojoMotor site tag, which adds
 * additional functionality.
 *
 * @package		MojoMotor
 * @subpackage	Addons
 * @author		Jordan Mack
 * @license		Apache License v2.0
 * @copyright	2012 Jordan Mack
 * @version		0.6
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *		http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * PHP class
 */
class Site_ex extends Mojomotor_parser_site
{
	/**
	 * @var	The version of the addon.
	 */
	public $addon_version = '0.6';

	private $CI;
	
	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->CI =& get_instance();
	}

	/**
	 * Passthough method for site_name.
	 *
	 * @param	array
	 * @return	string
	 */
	public function site_name($tag)
	{
		return $this->CI->mojomotor_parser->site->site_name($tag);
	}

	/**
	 * Passthough method for asset_url.
	 *
	 * @param	array
	 * @return	string
	 */
	public function asset_url($tag)
	{
		return $this->CI->mojomotor_parser->site->asset_url($tag);
	}

	/**
	 * Passthough method for login.
	 *
	 * @param	array
	 * @return	string
	 */
	public function login($tag)
	{
		return $this->CI->mojomotor_parser->site->login($tag);
	}

	/**
	 * Page list
	 *
	 * Creates an unordered list of all pages in the site that haven't been
	 * opted out of appearing in the page_list.
	 *
	 * @param	array
	 * @return	string
	 */
	public function page_list($tag)
	{

		//Gather up site_ex parameters and remove from $tag.
		foreach(array('omit_li_ids', 'li_id_prefix') as $param)
		{
			if(isset($tag['parameters'][$param]))
			{
				$parameters[$param] = trim($tag['parameters'][$param]);
				unset($tag['parameters'][$param]);
			}
		}
		
		//Get page list.
		$page_list = $this->CI->mojomotor_parser->site->page_list($tag);

		//If omit was set, parse out the ids.
		if(isset($parameters['omit_li_ids']) && 'true' === strtolower($parameters['omit_li_ids']))
		{
			$page_list = preg_replace('/\sid="mojo_page_list_[^"]+"/i', '', $page_list);
		}
		
		//If a new prefix was set, replace the existing.
		elseif(isset($parameters['li_id_prefix']))
		{
			$page_list = str_replace('id="mojo_page_list_', 'id="'.$parameters['li_id_prefix'], $page_list);
		}
	
		return $page_list;
	}

	/**
	 * Debug the specified method.
	 *
	 * Executes the method specified in the "method" parameter, and returns the string surrounded by <pre> for on screen display.
	 *
	 * @param	array
	 * @return	string
	 */
	public function debug($tag)
	{
		//Check for method parameter.
		if(isset($tag['parameters']['method']))
		{
			$method = $tag['parameters']['method'];
			unset($tag['parameters']['method']);
		}
		else
		{
			return;
		}

		ob_start();
		echo '<pre>';
		echo htmlspecialchars($this->$method($tag));
		echo '</pre>';
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}
}


/* End of file site_ex.php */