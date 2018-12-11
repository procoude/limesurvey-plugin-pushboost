<?php
/**
 * Sends E-Mail to Survey-Admin when a Survey reaches a Milestone
 *
 * @author LimeBoost <name@email.de>
 * @copyright 2018 Organisation <https://www.URL.de>
 * @license GPL v3
 * @version 1.0.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

class Pendelstruktur extends PluginBase {
    
    protected $storage = 'DbStorage';
    static protected $description = 'Enables the User to create a Survey-Structure that is automatically translated into Question Groups and Links.';
    static protected $name = 'Pendelstruktur';
    
    public function init() {
        
        // Sets the Settings for the Plugin
        $this->subscribe('beforeSurveySettings');
        
        // Saves the Settings for the Plugin
        $this->subscribe('newSurveySettings');
        
        // Fires after a Survey is submitted -> Place for Notifications concerning the Survey-Size
        $this->subscribe('afterSurveyComplete');
    }
    
     /**
      * This Array saves the general Survey Settings that can be found under "configuration" in the Plugin-Manager
      */
     protected $settings = array(
     'bUse' => array(
     'type' => 'select',
     'options'=>array(
     0=>'Nein',
     1=>'Ja'
     ),
     'default'=>1,
     'label' => 'Willst du Benachrichtigungen über Milestones deiner Umfrage erhalten?',
     'help'=>'Entscheide, ob du über den Teilnahme-Stand bzw. die zeitlichen Rahmenbedingungen deiner Umfrage per E-Mail informiert werden willst.',
     ),
     
     );
    
    public function beforeSurveySettings()
    {
        $oEvent = $this->event;
        $oEvent->set("surveysettings.{$this->id}", array(
            'name' => get_class($this),
            // generates the Settings of the Plugin (viewed under the general Settings of a Survey)
            'settings' => array(
                'bUse' => array(
                    'type' => 'string',
                    'label' => 'Wie viele Gruppen haben Sie:',
                    'options'=>array(
                        0=> 'Nein',
                        1=> 'Ja',
                    ),
                    'default'=>1,
                    'help'=>'Anzahl unterschiedlicher FragebÃ¶gen',
                    'current'=> $this->get('bUse','Survey',$oEvent->get('survey')),
                ),
                'bUse' => array(
                    'type' => 'string',
                    'label' => 'Wie viele Gruppen haben Sie:',
                    'options'=>array(
                        0=> 'Nein',
                        1=> 'Ja',
                    ),
                    'default'=>1,
                    'help'=>'Anzahl unterschiedlicher FragebÃ¶gen',
                    'current'=> $this->get('bUse','Survey',$oEvent->get('survey')),
                ),
                'bUse' => array(
                    'type' => 'string',
                    'label' => 'Wie viele Gruppen haben Sie:',
                    'options'=>array(
                        0=> 'Nein',
                        1=> 'Ja',
                    ),
                    'default'=>1,
                    'help'=>'Anzahl unterschiedlicher FragebÃ¶gen',
                    'current'=> $this->get('bUse','Survey',$oEvent->get('survey')),
                ),
                
            ),
        ));
    }
    
    /**
     * Save the settings (copied from Zesthook-Plugin)
     */
    public function newSurveySettings()
    {
        $event = $this->event;
        foreach ($event->get('settings') as $name => $value)
        {
            /* In order use survey setting, if not set, use global, if not set use default */
            $default=$event->get($name,null,null,isset($this->settings[$name]['default'])?$this->settings[$name]['default']:NULL);
            $this->set($name, $value, 'Survey', $event->get('survey'),$default);
        }
    }
    
    /**
     * Sends the Notification depending on which Milestones were set and how many participants the Survey already has
     */
    public function afterSurveyComplete()
    {
        // awesome Code
    }
    
    
    
}
