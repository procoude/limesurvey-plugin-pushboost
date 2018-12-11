<?php

/**
 * 
 * Perform a lottery after each afterSurveyComplete
 * 
 * @author  Eric Hochstrate - 
 *          UNIVERSITY OF DUISBURG-ESSEN - 
 *          Department of Computer Science and Applied Cognitive Science - 
 *          Professional Communication in Electronic Media / Social Media - 
 *          Prof. Dr. Stefan Stieglitz
 * @copyright
 * @license
 * @version 1.0.0
 * 
 * 
 * 
 */

    class LotteryBoost extends PluginBase{
        
        protected $storage = 'DbStorage';
        static protected $description = 'Lottery for LimeSurvey. Attract more participants for your Survey!';
        static protected $name = 'LotteryBoost';
        
        public function init(){  
            $this->subscribe('afterSurveyComplete');
            $this->subscribe('beforeSurveySettings');
            $this->subscribe('newSurveySettings');
            $this->subscribe('beforeSurveyDeactivate');     
        }
        
        protected $settings = array(
            'logo' => array(
                'type' => 'logo',
                'label' => '<strong>LotteryBoost</strong>',
                'path' => 'assets/logo.gif'
            ),
            'bUse' => array(            // Deactivate the lotteries without deactivating the plugin (surveysettings remain stored)
                'type' => 'boolean',
                'label' => 'Would you like to perform a lottery?',
                'help' => 'You have to activate it in each survey setting.',
                'default' => 1,
            ),
        );
        
        
        /**
         * Add setting on survey level: activate lotteries for certain surveys / define the sgqa code or question code for your e-mail query / activate or deactivate the e-mail notification / define the number of winners
         */
        public function beforeSurveySettings(){
            $oEvent = $this->event;
            $oEvent->set("surveysettings.{$this->id}", array(
                'name' => get_class($this),
                'settings' => array(
                    'bUse' => array(
                        'type' => 'boolean',
                        'label' => 'Would you like to perform a lottery in this survey?',
                        'help' => 'Turn yes if you offer a lottery in your survey.',
                        'default' => 0,
                        'current' => $this->get('bUse','Survey',$oEvent->get('survey')),
                    ),
                    'kCode' => array(               // Selection of the kind of code [SGQA or Question-code]
                        'type' => 'select',
                        'label' => 'Which kind of code would you like to enter?',
                        'options' => array(
                            0 => 'Question Code',
                            1 => 'SGQA-Code'
                        ),
                        'default' => 0,
                        'help' => 'The question code is the one you select by yourself for each question; The SGQA code is given by LimeSurvey and consists of [Survey-ID]X[Group-ID]X[Question-ID].',
                        'current' => $this->get('kCode','Survey',$oEvent->get('survey')),
                    ),                        
                    'code' => array(
                        'type' => 'string',
                        'label' => 'Code',
                        'help' => "The Question-Code / SGQA-Code of the question in which you ask for the participant's e-mail adress",
                        'current' => $this->get('code', 'Survey', $oEvent->get('survey'))
                    ),
                    'bAdminReceive' => array(           // Boolean for e-mail notification
                        'type' => 'boolean',
                        'label' => 'Would you like to receive an e-mail after deactivating the survey?',
                        'help' => 'The e-mail would contain the adresses of your lottery winner(s). If you deactivate this, you will have to draw the winners manually.',
                        'default' => 1,
                        'current' => $this->get('bAdminReceive', 'Survey', $oEvent->get('survey'))
                    ),
                    'adminAdress' => array(
                        'type' => 'string',
                        'label' => 'E-Mail adress',
                        'help' => "Type in the e-mail address to which the winner-addresses should be sent.",
                        'current' => $this->get('adminAdress', 'Survey', $oEvent->get('survey'))
                    ),
                    'cWinners' => array(            // Integer for number of winners
                        'type' => 'int',
                        'label' => 'How many winners would you like to draw?',
                        'help' => 'Select more than one if you are going to have multiple winners. Please only type in numbers.',
                        'current' => $this->get('cWinners', 'Survey', $oEvent->get('survey'))
                    )
                ),
            ));
        }
        
        
        /**
         * Save the settings
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
         * save the given e-mail adresses in another table
         */
        public function afterSurveyComplete(){
            
            $oEvent = $this->getEvent();
            $sSurveyId = $oEvent->get('surveyId');
            
            // Do not run the code if the plugin is disabled for this survey or in general
            if($this->isWingameEnabled($sSurveyId))
            {
                return;
            }
            
            // establishes database connection
            $this->connection = App()->getDb();
            
            // parameters for SQL-instructions
            $prefix = Yii::app()->db->tablePrefix;
            $sSgqaCode = $this->getKindOfCode($sSurveyId);
            $wingametable = "{$prefix}wingame_{$sSurveyId}";        // the name of the new table for all participants fits to the name-structure of all other tables
            $tableName = "{$prefix}survey_{$sSurveyId}";
            
            // SQL Code
            // Duplication checking by PRIMARY KEY -> participants cannot enter their e-mail address twice 
            $this->connection->createCommand("CREATE TABLE IF NOT EXISTS $wingametable (mails VARCHAR(150) PRIMARY KEY NOT NULL);")->execute();
            
            // Move the e-mail address from the survey table into the newly created table
            $this->connection->createCommand("INSERT INTO $wingametable(`mails`) SELECT $sSgqaCode FROM $tableName WHERE $sSgqaCode IS NOT NULL;")->execute();
            
            // Set the e-mail addresses in survey table to NULL
            $this->connection->createCommand("UPDATE $tableName SET $sSgqaCode = NULL;")->execute();
            
            // and order the content of the new table aplphabetically
            $this->connection->createCommand("SELECT * FROM $wingametable ORDER BY `mails`;")->execute();
        }
        
        /**
         *  send an e-mail which includes the lottery winners 
         */
        public function beforeSurveyDeactivate(){
            
            $oEvent = $this->getEvent();
            $sSurveyId = $oEvent->get('surveyId');
            
            // Do not run the code if the admin does not want to receive an e-mail
            if($this->isAdminEmailEnabled($sSurveyId)){
                return;
            }
            
            // establishes database connection
            $this->connection = App()->getDb();
            
            // parameters for SQL an e-mail instructions
            $prefix = Yii::app()->db->tablePrefix;
            $numberWinners = $this->get('cWinners','Survey',$sSurveyId);
            $wingametable = "{$prefix}wingame_{$sSurveyId}";
            $tableName = "{$prefix}survey_{$sSurveyId}";
            $languageTable = "{$prefix}surveys_languagesettings";       // in this table the names of all surveys are saved
            
            // SQL Code - get title of the survey
            $surveyName = Yii::app()->db->createCommand("SELECT surveyls_title FROM $languageTable WHERE surveyls_survey_id = $sSurveyId;")->query()->readAll();            
            $sName = $surveyName[0]["surveyls_title"];
            
            //readAll() returns an array that contains an array in SQL structure. So, because the column is called "mails", the content is selected through variable[x]["mails"]
            $randomDraw = Yii::app()->db->createCommand("SELECT mails FROM $wingametable ORDER BY RAND() LIMIT $numberWinners;")->query()->readAll();       // This a completely random draw through 'ORDER BY RAND()'
            // foreach ($draw as $v) {
            //     echo $v["mails"];
            // }
            $allParticipants = Yii::app()->db->createCommand("SELECT mails FROM $wingametable;")->query()->readAll();

            
            $mailBody = "There is/are $numberWinners winner/s after you deactivated the survey ".$sName.". Here are the drawn addresses: \n";
            foreach ($randomDraw as $v) {
                    $mailBody = $mailBody . $v["mails"] . "\n";
            }
            $mailBody = $mailBody . "\nIf an e-mail address is  invalid, you can choose some manually. Here are all participants: \n";
            foreach ($allParticipants as $v){
                $mailBody = $mailBody . $v["mails"] . "\n";
            }

            // parameters for e-mail function
            $subject = "Lottery Winner of Survey $sName ($tableName)";
            $to = $this->get('adminAdress','Survey',$sSurveyId);        // gets the e-mail address that was typed in in the survey settings
            $limeBoost = "noreply@LimeBoost.com";                       // no-reply e-mail address
            $sitename = $this->pluginManager->getAPI()->getConfigKey("sitename");
            
            SendEmailMessage($mailBody,$subject,$to,$limeBoost,$sitename);
        }
        
        
        /**
         * check if the plugin should be used in certain survey
         * 
         * @param string $sSurveyId
         * @return boolean
         */
        private function isWingameEnabled($sSurveyId)
        {
            return ($this->get('bUse','Survey',$sSurveyId)==0)||($this->get('bUse',null,null,$this->settings['bUse'])==0);
        }
        
        /**
         *  check if the admin would like to receive an e-mail
         *  
         *  @param string $sSurveyId
         *  @return boolean 
         */
        private function isAdminEmailEnabled($sSurveyId){
            return ($this->get('bAdminReceive','Survey',$sSurveyId)==0);
        }
        
        /**
         * converts the question-code into the SGQA-Code and returns it or just returns the SGQA-Code 
         * 
         * @param string $sSurveyId
         * @return string|boolean
         */
        private function getKindOfCode($sSurveyId){
            if ($this->get('kCode','Survey',$sSurveyId)==0){
                // convert the question-code into the SGQA-Code
                $qcode = $this->get('code','Survey',$sSurveyId);
                $prefix = Yii::app()->db->tablePrefix;
                $qTable = "{$prefix}questions";
                
                $groupIdQuery = Yii::app()->db->createCommand("SELECT `b`.`gid` FROM $qTable AS b WHERE `b`.`title` = '$qcode' LIMIT 1;")->query()->readAll();
                $questionIdQuery = Yii::app()->db->createCommand("SELECT `b`.`qid` FROM $qTable AS b WHERE `b`.`title` = '$qcode' LIMIT 1;")->query()->readAll();
                
                $groupId = $groupIdQuery[0]["gid"];
                $questionId = $questionIdQuery[0]["qid"];
                
                $sgqaCode = "{$sSurveyId}X{$groupId}X{$questionId}";
                return $sgqaCode;
            }
            else{
                return $this->get('code','Survey',$sSurveyId);
            }
        }
        
        
    }

?>