<?php
/**
 * 
* @copyright Copyright (C) 2012 Jean-Luc TRYOEN. All rights reserved.
* @license GNU/GPL
*
* Version 1.0.1
*
*/
namespace JLTRY\Plugin\Content\JOUserFields\Extension;

use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Utility\Utility;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

define('PF_REGEX_USERFIELDS_PATTERN', "#{userfields ([^}]*?)}#s");

/**
* JOUserFields Content Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.JOUserFields
 */
class JOUserFields extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
                'onContentPrepare' => 'onContentPrepare'
                ];
    }

    /**
    * Example prepare content method in Joomla 1.6/1.7/2.5
    *
     * @param  ContentPrepareEvent The context for content prepare
    */   
    public function onContentPrepare(ContentPrepareEvent $event)
    {
        //Escape fast
        if (!$this->params->get('enabled', 1)) {
            return;
        }
        if (!$this->getApplication()->isClient('site')) {
            return;
        }
        // use this format to get the arguments for both Joomla 4 and Joomla 5
        // In Joomla 4 a generic Event is passed
        // In Joomla 5 a concrete ContentPrepareEvent is passed
        [$context, $row, $params, $page] = array_values($event->getArguments());

         if ( strpos( $row->text, '{userfields' ) === false ) {
            return true;
        }
        preg_match_all(PF_REGEX_USERFIELDS_PATTERN, $row->text, $matches);
        // Number of plugins
        $count = count($matches[0]);
        // plugin only processes if there are any instances of the plugin in the text
        if ($count) {
            
            $document = Factory::getDocument();
            for ($i = 0; $i < $count; $i++)
            {
                $result = array();
                if (@$matches[1][$i]) {
                    $inline_params = $matches[1][$i];
                    $localparams = Utility::parseAttributes($inline_params);
                    $p_content = $this->userfields($localparams);
                    $row->text = str_replace($matches[0][$i], $p_content, $row->text);
                }
            }
        }
        else
        {
            $row->text = str_replace("{userfields ", "erreur de syntaxe: {userfields name=name}", $row->text);
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
        if (isset($params['display']))
        {
            $display = $params['display'];
        } else {
            $display = true;
        }
        $user =  Factory::getUser();
        if ($user)
        {
            $customFields = FieldsHelper::getFields('com_users.user', Factory::getUser(), $display);
        }
        if (property_exists($user, $params['name'])) { 
            $content = print_r($user->{$params['name']}, 1);
        }
        else
        {
            if ($this->getValueByFieldName($customFields, $params['name']) != '') {
                $content = print_r($this->getValueByFieldName($customFields, $params['name']), 1);
            } elseif (array_key_exists('default', $params) && $this->getValueByFieldName($customFields, $params['default']) != '') {
                $content = print_r($this->getValueByFieldName($customFields, $params['default']), 1);
            }
        }
        return $content;
    }
}
