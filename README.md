# PushBoost

The LimeSurvey-plugin PushBoost helps you as the leader of a survey to keep up-to-date. You will receive an email everytime you reached a milestone concerning the number of participants. The plugin will let you know how many people participated in your online-survey. You can choose yourself how often you would like to receive such an email (e.g. every 10th participant, every 50th and so on).

## Getting Started

LimeSurvey is a service for online-surveys. The data can be stored local which allows control over the examined data. LimeSurvey provides a notification function where the survey leader gets an email every time a participant finished the online-survey. PushBoost extends this function. It allows administrators to define and individually customize the notifications. The administrators can decide if they prefer receiving a notification after a defined milestone, a certain number of participants or a certain amount of time. By these customizations, the administrator will not get any spam but wanted notifications. Those notifications will be send via mail which is the most convenient way since the administrator does not have to log in to get the information. For the time-dependent
notifications, it is first necessary for the administrator to activate the CronService on the
the same web server that hosts the LimeSurvey process.

## How to use it

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
    
    

## Code Examples

    public function beforeSurveySettings() {
        $event = $this->event;
        $event->set("surveysettings.{$this->id}", array(
            "name" => get_class($this),
            "settings" => array(
                "bCopyGeneralSettings" => array(
                    'type'=>'boolean',
                    'label'=>'Do you want to overwrite the gereral plugin settings?',
                    'default'=>1,
                    "current" => $this->get("bCopyGeneralSettings", "Survey", $event->get("survey"))
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
                ), ...


## Authors

The plugin has been developed by the research chair [Professional Communication in Electronic Media / Social Media, University of Duisburg Essen](https://www.uni-due.de/proco/index_en.php)

    Prof. Dr. Stefan Stieglitz - Professor
    Tobias Kroll - initial work
    Eric 
    Melina Paßfeld
    Andere Studenten

## License

See the [LICENSE](LICENSE.md) file for license rights and limitations (MIT).
