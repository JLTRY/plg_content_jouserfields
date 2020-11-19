<?php
/**
 * 
* @copyright Copyright (C) 2012 Jean-Luc TRYOEN. All rights reserved.
* @license GNU/GPL
*
* Version 1.0
*
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
define('PF_REGEX_USERFIELDS_PATTERN', "#{userfields(.*?)}#s");
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
/**
* UserFields Content Plugin
*
*/
class plgContentUserFields extends JPlugin
{

	/**
	* Constructor
	*
	* @param object $subject The object to observe
	* @param object $params The object that holds the plugin parameters
	*/
	function __construct( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}

	/**
	* Example prepare content method in Joomla 1.5
	*
	* Method is called by the view
	*
	* @param object The article object. Note $article->text is also available
	* @param object The article params
	* @param int The 'page' number
	*/
	function onPrepareContent( &$article, &$params, $limitstart )
	{
		return $this->OnPrepareRow($article);
	}

 	/**
	* Example prepare content method in Joomla 1.6/1.7/2.5
	*
	* Method is called by the view
	*
	* @param object The article object. Note $article->text is also available
	* @param object The article params
	*/   
	function onContentPrepare($context, &$row, &$params, $page = 0){
		return $this->OnPrepareRow($row);
	}

		
	
	function onPrepareRow(&$row) 
	{
		//Escape fast
		if (!$this->params->get('enabled', 1)) {
			return true;
		}
 		if ( strpos( $row->text, '{userfields' ) === false ) {
			return true;
		}		
		preg_match_all(PF_REGEX_USERFIELDS_PATTERN, $row->text, $matches);
		// Number of plugins
		$count = count($matches[0]);	
		// plugin only processes if there are any instances of the plugin in the text
		if ($count) {
			
			$document =& JFactory::getDocument();
			for ($i = 0; $i < $count; $i++)
			{
				$result = array();
				if (@$matches[1][$i]) {
					$inline_params = $matches[1][$i];
				   
					$pairs = explode(' ', trim($inline_params));
					foreach ($pairs as $pair) {
						$pos = strpos($pair, "=");
						$key = substr($pair, 0, $pos);
						$value = substr($pair, $pos + 1);
						$result[$key] = $value;
					}
					$p_content = $this->userfields($result);
					$row->text = str_replace("{userfields" . $matches[1][$i] . "}", $p_content, $row->text);
				}	
				else
				{
					$p_content = $this->userfields($result);	
					$row->text	= preg_replace('#{userfields.*}#', $p_content, $row->text);
				}
			}
			
		}
		else
		{
			$row->text = str_replace("{userfields ", "erreur de syntaxe: {userfields style=normal|bold|italic}", $row->text);
		}
		return true;
	}





	function getValueByFieldName($fields, $field_name) {
		foreach ($fields as $field) {
			if ($field->name == $field_name){
				return $field->value;
				break;
			}
		}
	}


 	/**
	* Function to insert userfields world
	*
	* Method is called by the onContentPrepare or onPrepareContent
	*
	* @param string The text string to find and replace
	*/	   
	function userfields( $params )
	{
		$user =  JFactory::getUser();
		if ($user)
		{
			$customFields = FieldsHelper::getFields('com_users.user', JFactory::getUser(), true);
		}		
		if (isset($params['type']))
		{
			switch($params['type'])
			{
				case 'imagelink':			
					$content1 = print_r($this->getValueByFieldName($customFields, $params['image']), 1);
					$content2 = print_r($this->getValueByFieldName($customFields, $params['link']), 1);
					if (array_key_exists('title', $params)) {
						$content3 = print_r($this->getValueByFieldName($customFields, $params['title']), 1);
					} else {
						$content3 = "";
					}
					$content = preg_replace('/(<a.*>)(.*)(<\/a>)/', '\1'. ${content1} . ${content3} .'\3', $content2);
					break;
				default:
					 break;
			}
		}
		else	
		{
			if (property_exists($user, $params['name'])) { 
				$content = print_r($user->{$params['name']}, 1);
				//$content = print_r($params['name'], 1);
			}
			else
			{
				$content = print_r($this->getValueByFieldName($customFields, $params['name']), 1);
			}
		}			
		return $content;
	}
}
