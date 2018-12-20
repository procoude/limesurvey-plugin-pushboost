# PushBoost

The LimeSurvey-plugin PushBoost helps a survey administrator to keep up-to-date.
You will receive an email everytime you reached a milestone concerning the number of participants.
The plugin will let you know how many people participated in your survey.
You can choose how often you want to receive an email (e.g. every 10th participant, every 50th and so on).

## Getting Started

LimeSurvey is a web software for online surveys.
The data can be stored on-premise which allows control as regards data safety and privacy requirements.

LimeSurvey provides a notification function where the survey administrator gets an email every time a participant finished the online survey.
PushBoost extends this function.
It allows administrators to define and individually customize the notifications.
The administrators can decide if they prefer receiving a notification after a defined milestone, a certain number of participants or a certain amount of time.
The notifications will be send via mail which is the most convenient way since the administrator does not have to log in to get the information.
For the time-dependent notifications, further configurations of a cron job is necessary.

## How to use it

## How to use it

To use this Plugin, you can either download PushBoost in one zip file and then unzip it in your LimeSurvey installation. Or, if you prefer, you can use GitHub and fork LotteryBoost via GIT to use it. At the end, make sure that you activate the PluginManager in your Limesurvey settings.

Via ZIP dowload

    Get the PushBoost file and unzip it
    Move the file included to plugins/limesurvey-plugin-lotteryboost directory

Via GIT

    Go to your LimeSurvey Directory 
    Clone in plugins/limesurvey-plugin-lotteryboost directory

## How it works

    1.  LimeSurvey Event        PushBoost is activated
        Cron Service 
    2.  sendEmail()             Main function
    
    Additional functions:
        isPluginActive()        Proves if the plugin is active
        ifToUseGlobalSettings() Proves if the global or survey settings are supposed to be used
        isMilestoneReached()    Proves if milestone is reached
    3.
        Sending Email
    
## Disclaimer / Status of the plugin

In case you decide to use this Plugin, you understand that PushBoost is not suitable for a productoin environment. Further, you understand and agree that we will not be liable to you or any third party for any loss of profits, use, goodwill, or - in this case especially important - data, or for any incidental, indirect, special, consequential or exemplary damages, however arising.

Our liability is limited whether or not we have been informed of the possibility of such damages, and even if a remedy set forth in this Agreement is found to have failed of its essential purpose. We will have no liability for any failure or delay due to matters beyond our reasonable control.

## Code Examples
```php
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
	        
	    }rent" => $this->get("sEMailText", "Survey", $event->get("survey"))
	),
```

## Authors

The plugin has been developed by the research chair [Professional Communication in Electronic Media / Social Media, University of Duisburg Essen](https://www.uni-due.de/proco/index_en.php)


## License

This plugin is licenced under the GPL 2.0. The LimeSurvey Logo is a registered trademarks of LimeSurvey GmbH, Hamburg, Germany.
