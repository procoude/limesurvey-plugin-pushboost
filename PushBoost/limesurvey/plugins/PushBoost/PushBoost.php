<?php
class PushBoost extends PluginBase {
    protected $storage = "DbStorage";
    static protected $name = "PushBoost";
    static protected $description = "Sends E-Mail-Notification when a Survey reaches certain Milestones regarding Time or the Number of Participants.";
   
    /**
     * here we define the general settings for the plugin that can be reached under 'configuration' in the Plugin-Manager
     */
    protected $settings = array(
        'logo' => array(
            'type' => 'logo',
            'path' => 'assets/PushBoost_logo.png'
        ),
        "bActive" => array(      
            'type'=>'boolean',
            'label'=>'Do you want to recieve E-Mails for all your Surveys?',
            'help'=>'Can be changed individually on a survey level.',
            'default'=>1,
            
        ),
        "sAddress" => array(
            "type" => "string",
            "default" => null, // here a function is needed that automatically sets this value to the adminemail
            "label" => "To which E-Mail-Address should the notifications be send by default?",
            "help" => "Can be changed individually on a survey level."
        ),
        "sText" => array(
            "type" => "text",
            "default" => "You reached a Milestone for your survey. Congratulations!",
            "label" => "Which standard message do you want to set for your notifications?",
            "help" => "Can be changed individually on a survey level."
        ),       
        
    );
    
    public function init()
    {
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe("afterSurveyComplete");
        $this->subscribe("beforeSurveySettings");
        $this->subscribe("newSurveySettings");
    }
    
    /**
     * This event is fired by the administration panel to gather extra settings available for a survey.
     * The plugin should return setting meta data.
     * @param PluginEvent $event
     */
    public function beforeSurveySettings()
    {
        $event = $this->event;
        $event->set("surveysettings.{$this->id}", array(
            "name" => get_class($this),
            "settings" => array(
                "bOverwriteGeneralSettings" => array(
                    'type'=>'boolean',
                    'label'=>'Do you want to overwrite the gereral plugin settings?',
                    'default'=>1,
                    "current" => $this->get("bOverwriteGeneralSettings", "Survey", $event->get("survey"))
                ),
                "bActive" => array(
                    'type'=>'boolean',
                    'label'=>'Do you want to activate notifications for this survey?',
                    'default'=>1,
                    "current" => $this->get("bActive", "Survey", $event->get("survey"))
                ),
                "sEMailAddress" => array(
                    "type" => "string",
                    "label" => "To which E-Mail-Address should the Notifications be send for this survey? <br> (e.g.: admin@mail.com)",
                    "current" => $this->get("sEMailAddress", "Survey", $event->get("survey"))
                ),
                "sEMailText" => array(
                    "type" => "text",
                    "label" => "Which message do you want to set for your notifications?",
                    "help" => "Type the text for the e-mail.",
                    "current" => $this->get("sEMailText", "Survey", $event->get("survey"))
                ),
                "bAttachInfo" => array(
                    'type'=>'boolean',
                    'label'=>'Do you want to attach further Information regarding the survey to the Email?',
                    'help'=>'e.g. how many people participated, which of the Milestones was reached...',
                    'default'=>1,
                    "current" => $this->get("bActive", "Survey", $event->get("survey"))
                ),
         
                'HeaderParticipants' => array(
                    'type' => 'info',
                    'content' => '<h1>Settings that depend on the Number of Participants</h1>',
                ),
                
                "iMaxParticipants" => array(
                    "type" => "int",
                    "label" => "How many people do you want to participate in your survey?",
                    "help" => "Type the maximum number of participants you want. You will automatically get a notification, when this number is reached.",
                    "current" => $this->get("iMaxParticipants", "Survey", $event->get("survey"))
                ),
                "bRepeatNotificationsP" => array(
                    "type" => "select",
                    "options" => array(
                        0=> "Never",
                        1=> "Every 10 Participants",
                        2=> "Every 50 Participants",
                        3=> "Every 100 Participants",
                    ),
                    "label" => "Do you want to be notified regulary?",
                    "help" => "Choose, if you want to be notified whenever a certain number of people participated in your survey.",
                    "current" => $this->get("bRepeatNotificationsP", "Survey", $event->get("survey"))
                ),
                "iRepeatNotificationsP" => array(
                    "type" => "int",
                    "default"=>"",
                    "label" => "After how many participants do you want to be notified regulary?",
                    "help" => "Type a number, if you want to be notified whenever a this number of people participated in your survey.<br>Choose 'User-defined' above to edit this setting.",
                    "current" => $this->get("iRepeatNotificationsP", "Survey", $event->get("survey"))
                    ),
                "iNotificationsParticipants" => array(
                    "type" => "int",
                    "label" => "After how many participants do you want to be notified?",
                    "help" => "Type the number of participants after which you want to be notified once.",
                    "current" => $this->get("iNotificationsParticipants", "Survey", $event->get("survey"))
                ),
                'headerTime' => array(
                    'type' => 'info',
                    'content' => '<h1>Settings depending on the timewise status</h1>',
                ),
                "dExpiryDate" => array(
                    'type' => 'date',
                    'label' => 'Please enter the expiration date of your survey:',
                    'help' => 'You can find and edit the expiry date when you edit a survey under: survey settings -> Publication & access.<br> You will automatically get a notification, when this date is reached.',
                    "current" => $this->get("dExpiryDate", "Survey", $event->get("survey"))
                ),   
                "bRepeatNotificationsT" => array(
                    "type" => "select",
                    "options" => array(
                        0=> "Never",
                        1=> "Daily, 8:00",
                        2=> "Weekly on Monday, 8:00",
                        3=> "Monthly on the first, 8:00"
                    ),
                    "label" => "Do you want regular notifications at a certain time?",
                    "help" => "Choose a time.",
                    "current" => $this->get("bRepeatNotificationsT", "Survey", $event->get("survey"))
                ),
                "dDateNotification" => array(
                    'type' => 'date',
                    'label' => 'Do you want to be notified on a specific date?',
                    'help' => 'Enter a date.' ,
                    "current" => $this->get("dDateNotification", "Survey", $event->get("survey"))
                ),               
            )
            
        ));
    }
    
    /**
     * save the plugin settings
     */
    public function newSurveySettings()
    {
        $event = $this->event;
        foreach ($event->get("settings") as $name => $value)
        {
            $this->set($name, $value, "Survey", $event->get("survey"));
        }
    }
    
    
    /**
     * on this event we handle the conditions for when notifications (depending on the participant number) should be send
     */
    public function afterSurveyComplete()
    {
        /**
         * needed parameters get fetched or initialised
         */
        $oEvent = $this->getEvent();

        //get surveyID
        $sSurveyId = $oEvent->get('surveyId');
        
        // stop if the Plugin is not activated
        if(!($this->isPluginActive($sSurveyId))) return;

        //get surveyInfo + adminEmail (in case nothing is sent save admin email in survey settings again)
        $surveyInfo = getSurveyInfo($sSurveyId);
        $adminEmail = $surveyInfo["adminemail"];

        //get sitename
        $sitename = $this->pluginManager->getAPI()->getConfigKey("sitename");
        

        //Function to get the complete response count (also possible countByAttributes(array('submitdate' => null) OR count();)
        $count = SurveyDynamic::model($sSurveyId)->count('submitdate is NOT NULL');

        // get and create the input for the function that sends the Email
        $body = 'Your Survey has reached a Milestone. Congratulations! <br>Number of participants: '.$count;
        $to = $adminEmail;
        
        /**
         * adapt the input for the Email according to the plugin settings
         */
        if($this->get("bOverwriteGeneralSettings", "Survey", $sSurveyId)){
            
            // overwrite body with the settings on a survey level
            if(($this->get("sEMailText", "Survey", $sSurveyId))!==null){
                $body = $this->get("sEMailText", "Survey", $sSurveyId);
            }
            // overwrite recipient address with the settings on a survey level
            $address = $this->get("sEMailAddress", "Survey", $sSurveyId);
            if($address!==null){
                if(validateEmailAddress($address)) $to = $address;
            }   
        }
        else{
            
            // overwrite body with general settings
            if(($this->get("sText"))!==null){
                $body = $this->get("sText");
            }
            // overwrite recipient address with general settings
            $address = $this->get("sAddress");
            if($address!==null){
                if(validateEmailAddress($address)) $to = $address;
            }   
        }

        //Attempt to keep it as generic as possible. In case of sendmail from mail should have the same domain like server
        //In case of SMTP-Server, it does not matter what from is (will be overwritten), but function needs a valid mail address with X@Y.Z
        $from = "no-reply@". gethostbyaddr(gethostbyname(gethostname()));

        $subject = "PushBoost Notification to Survey ".$sSurveyId;
        
        //Allow HTML-Styling for the Emailtext
        $ishtml = true;
        
        
        /**
         * Coditions for sending the E-Mail are checked
         */
        $aMilestones = $this->isMilestoneReached($sSurveyId, $count);
       
            if($aMilestones[0]){
                
                // check, if we should attach more information to the Email
                if(($this->get("bAttachInfo", "Survey", $sSurveyId))==1){
                    $body = $body.'<br> <br> Your survey has reached '.$count.' participants. <br>This Email was send to you because you wanted to be notified if:'; // Also: You still have x days left.
                    for($i=1;$i<count($aMilestones);$i++){
                        $body = $body.' <br>'.$aMilestones[$i];
                    }
                }
                // Mail function with parameters from above
                SendEmailMessage($body,$subject,$to,$from,$sitename,$ishtml);
                
            }
        
    
    }
    
    
    /**
     * functions that test all conditions
     */
    
        private function isMilestoneReached($sSurveyId,$numberParticipants){
            if($numberParticipants!==0){
                
                // get all parameters from the settings
                $maxParticipants = $this->get("iMaxParticipants", "Survey", $sSurveyId);
                $repeatForm = $this->get("bRepeatNotificationsP", "Survey", $sSurveyId);
                $repeatIndividual = $this->get('iRepeatNotificationsP',"Survey", $sSurveyId);
                $fixIndividual = $this->get("iNotificationsParticipants","Survey",$sSurveyId);
                
                // create return-value. If no condition is met, it will return false
                $result = array();
                $result[0] = false;
                
                // check if maximum number of Participants is reached
                if($maxParticipants!==null){
                    if($maxParticipants==$numberParticipants) {
                        $result[0] = true;
                        $result[] = 'The maximum Number of Participants is reached.';
                    }
                    
                }
                
                // check if regular reminder is reached
                switch ($repeatForm) {
                    case 0:
                        break;
                    case 1:
                        if(($numberParticipants%10)==0) {
                            $result[0] = true;
                            $result[] = '10 new People participated in your Survey.';
                        }
                        break;
                    case 2:
                        if(($numberParticipants%50)==0) {
                            $result[0] = true;
                            $result[] = '50 new People participated in your Survey.';
                        }
                        break;
                    case 3:
                        if(($numberParticipants%100)==0) {
                            $result[0] = true;
                            $result[] = '100 new People participated in your Survey.';
                        }
                        break;
                }
                
                // check if individual regular reminder is reached
                if($repeatIndividual!==null && $repeatIndividual>0){
                    if(($numberParticipants%$repeatIndividual)==0) {
                        $result[0] = true;
                        $result[] = $repeatIndividual.' new People participated in your Survey.';
                    }
                }
                
                // check if fixed individual number of participants is reached
                if($fixIndividual!==null){
                    if($fixIndividual==$numberParticipants) {
                        $result[0] = true;
                        $result[] = 'Your Survey reached '.$fixIndividual.' Participants.';
                    }
                }
                
                // returns the value from the function
                return $result;            
            }
        }
        
        private function isPluginActive($sSurveyId){
            
            // check if Plugin is activated on a global level
            if($this -> get('bActive')==1){
                
                // check if Plugin is activated in the survey settings
                if($this->get('bActive','Survey',$sSurveyId)==1){
                    return true;
                }
                
            }
            // if the plugin is not activated for this survey, return false
            return false;
            
        }
      
}
