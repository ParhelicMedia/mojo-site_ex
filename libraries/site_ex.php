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
 * @version		0.5
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
class site_ex extends Mojomotor_parser_site
{
	/**
	 * @var	The version of the addon
	 */
	public $addon_version = '0.5';

	private $CI;
	private $_default_page;
	private $_page_count;

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('site_model');
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
		$this->CI->load->helper('array');
		$this->CI->load->model('page_model');

		if ( ! $page_map = $this->CI->page_model->get_page_map('include_in_page_list'))
		{
			return;
		}

		$defaults = $this->CI->page_model->get_page($this->CI->site_model->get_setting('default_page'));
		$this->_default_page = ($defaults) ? $defaults->url_title : '';

		$attributes = array();

		// Gather up parameters
		foreach (array('class', 'id') as $param)
		{
			if (isset($tag['parameters'][$param]))
			{
				$attributes[$param] = trim($tag['parameters'][$param]);
			}
		}

		// If there is a secific page requested, then we'll start there
		// otherwise, start from the homepage.
		if (isset($tag['parameters']['page']))
		{
			$page = $tag['parameters']['page'];

			if ($page == 'CURRENT_PAGE')
			{
				$current_page = trim($this->CI->uri->uri_string, '/');
				$page = empty($current_page) ? $this->_default_page : $current_page;
			}

			if ($start_page = $this->CI->page_model->get_page_by_url_title($page))
			{
				$page_map = array_find_element_by_key($start_page->id, $page_map);

				// No children? Alrighty then.
				if (empty($page_map['children']))
				{
					return '';
				}

				$page_map = $page_map['children'];
			}
		}

		// Depth parameter specified?
		$max_depth = 0;

		if (isset($tag['parameters']['depth']) && ctype_digit($tag['parameters']['depth']))
		{
			$max_depth = $tag['parameters']['depth'];
		}
		
		//List item id prefix (li_id_prefix) specified?
		$li_id_prefix = 'mojo_page_list_'; //Default
		if(isset($tag['parameters']['li_id_prefix']))
		{
			$li_id_prefix = $tag['parameters']['li_id_prefix'];
		}
		
		//Omit list item ids (omit_li_ids) specified?
		$omit_li_ids = false; //Default
		if(isset($tag['parameters']['omit_li_ids']))
		{
			$omit_li_ids = ('true' == $tag['parameters']['omit_li_ids']) ? true : false;
		}
		
		$this->_page_count = 0;

		$list = $this->_build_page_list($page_map, $attributes, $max_depth, 1, $li_id_prefix, $omit_li_ids);

		// if no pages were actually output, return an empty string
		$list = ($this->_page_count) ? $list : '';
		
		return $list;
	}

	// --------------------------------------------------------------------

	/**
	 * Build Page List
	 *
	 * Recursively constructs the output for the parser tag
	 * {mojo:site_ex:page_list}
	 *
	 * @access	private
	 * @param	array
	 * @param	array
	 * @param	int
	 * @param	string
	 * @param	bool
	 */
	private function _build_page_list($page_map, $attributes = array(), $max_depth, $cur_depth = 1, $li_id_prefix = '', $omit_li_ids = false)
	{
		// Are we done?
		if (($max_depth != 0 AND $cur_depth > $max_depth) OR ! is_array($page_map))
		{
			return;
		}
		
		// Set the indentation based on the depth
		$out = str_repeat(" ", $cur_depth * 2);
	
		$atts = '';
	
		// Were any attributes submitted?  If so generate a string
		if (is_array($attributes))
		{
			foreach ($attributes as $att => $val)
			{
				$atts .= ' '.$att.'="'.$val.'"';
	
				// We only want id applied to the top level list, so we'll unset it here so children
				// don't inherit it. I wish I could have done with the big nose on my mothers side...
				if ($att == 'id')
				{
					unset($attributes[$att]);
				}
			}
		}
	
		// Write the opening list tag
		$out .= "<ul".$atts.">\n";
	
		$current_uri = trim($this->CI->uri->uri_string, '/');

		// Cycle through the list elements.  If an array is
		// encountered we will recursively call build_page_list()
		foreach ($page_map as $id => $page)
		{
			if ($page['include_in_page_list'] == 'n')
			{
				continue;
			}

			$this->_page_count++;

			// As of 1.1.0 we allow any URI, so convert any forward slashes to
			// underscores to ensure valid (X)HTML id's are generated
			$url_title = str_replace('/', '_', $page['url_title']);

			// Active page class?
			if (($current_uri == '' && $page['url_title'] == $this->_default_page) OR
				$page['url_title'] == $current_uri)
			{
				$active_class = ' class="mojo_active"';
			}
			else
			{
				$active_class = '';
			}
	
			$out .= str_repeat(" ", $cur_depth * 2);
			$out .= ($omit_li_ids) ? '<li>' : '<li id="'.$li_id_prefix.$url_title.'"'.$active_class.'>';
	
			if ($page['url_title'] == $this->_default_page)
			{
				$out .= anchor('', $page['page_title']);
			}
			else
			{
				$out .= anchor($page['url_title'], $page['page_title']);
			}
	
			if (isset($page['children']))
			{
				$out .= "\n".$this->_build_page_list($page['children'], $attributes, $max_depth, $cur_depth + 1, $li_id_prefix, $omit_li_ids);
				$out .= str_repeat(" ", $cur_depth * 2);
			}
	
			$out .= "</li>\n";
		}
	
		// Closing tag
		$out .= str_repeat(" ", $cur_depth * 2) . "</ul>\n";
	
		return $out;
	}

	/**
	 * Debug the specified method
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