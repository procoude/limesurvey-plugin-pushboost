<?php

/**
 * 
 * Perform a raffle after each afterSurveyComplete
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
                'path' => 'assets/logo.png'
            ),
            'bUse' => array(
                'type' => 'select',
                'options'=>array(
                    0=>'No',
                    1=>'Yes'
                ),
                'default'=>1,
                'label' => 'Would you like to perform a lottery?',
                'help'=>'You have to activate it in each survey setting.',
            ),
        );
        
        
        /**
         * Add setting on survey level: activate lotteries for certain surveys / define the sgqa code for your e-mail query
         */
        public function beforeSurveySettings(){
            $oEvent = $this->event;
            $oEvent->set("surveysettings.{$this->id}", array(
                'name' => get_class($this),
                'settings' => array(
                    'bUse' => array(
                        'type' => 'select',
                        'label' => 'Would you like to perform a lottery in this survey?',
                        'options'=>array(
                            0=> 'No (default)',
                            1=> 'Yes'
                        ),
                        'default'=>0,
                        'help'=>'Turn yes if you want to.',
                        'current'=> $this->get('bUse','Survey',$oEvent->get('survey')),
                    ),
                    'kCode' => array(
                        'type' => 'select',
                        'label' => 'Which kind of code would you like to enter?',
                        'options'=>array(
                            0=> 'Question-Code (default)',
                            1=> 'SGQA-Code'
                        ),
                        'default'=>0,
                        'help'=>'The question code is the one you select by yourself for each question; The SGQA code is given by LimeSurvey and consists of <Survey-ID>X<Group-ID>X<Question-ID>.',
                        'current'=> $this->get('kCode','Survey',$oEvent->get('survey')),
                    ),                        
                    'code' => array(
                        'type' => 'string',
                        'label' => 'Code',
                        'help' => "The Question-Code / SGQA-Code of the question in which you ask for the participant's e-mail adress",
                        'current' => $this->get('code', 'Survey', $oEvent->get('survey'))
                    ),
                    'adminmail'=>array(
                        'type'=>'checkbox',
                        'label'=>'Would you like to receive an e-mail after deactivating the survey?',
                        'help'=>'The e-mail would contain the adresses of your lottery winner(s).',
                    ),
                    'winner' => array(
                        'type' => 'int',
                        'label' => 'How many winners would you like to draw?',
                        'help' => 'Select more than one if you are going to have multiple winners. Please only type in numbers.',
                        'current' => $this->get('winner', 'Survey', $oEvent->get('survey'))
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
            
            // database connection
            $this->connection = App()->getDb();
            
            $prefix = Yii::app()->db->tablePrefix;
            
            // parameters for SQL-instructions
            //$sSgqaCode = $this->get('sgqaCode','Survey',$sSurveyId);
            $sSgqaCode = $this->getKindOfCode($sSurveyId);
            $wingametable = "{$prefix}wingame_{$sSurveyId}";
            $tableName = "{$prefix}survey_{$sSurveyId}";    // "{$prefix}survey_{$sSurveyId}"
            
            // SQL Code
            $this->connection->createCommand("CREATE TABLE IF NOT EXISTS $wingametable (mails VARCHAR(150) NOT NULL);")->execute();
            $this->connection->createCommand("INSERT INTO $wingametable(`mails`) SELECT $sSgqaCode FROM $tableName WHERE $sSgqaCode IS NOT NULL;")->execute();
            $this->connection->createCommand("UPDATE $tableName SET $sSgqaCode = NULL;")->execute();
            $this->connection->createCommand("SELECT * FROM $wingametable ORDER BY `mails`;")->execute();
        }
        
        
        public function beforeSurveyDeactivate(){
            
            $oEvent = $this->getEvent();
            $sSurveyId = $oEvent->get('surveyId');
            
            // database connection
            $this->connection = App()->getDb();
            
            $prefix = Yii::app()->db->tablePrefix;
            $sWinner = $this->get('winner','Survey',$sSurveyId);
            $wingametable = "{$prefix}wingame_{$sSurveyId}";
            
            //$this->connection->createcommand("SELECT mails FROM $wingametable ORDER BY RAND() LIMIT $sWinner;")->execute();
            $this->connection->createCommand("CREATE TABLE IF NOT EXISTS winners (adresses VARCHAR(150) NOT NULL);")->execute();
            $this->connection->createCommand("INSERT INTO winners(`adresses`) SELECT mails FROM $wingametable ORDER BY RAND() LIMIT $sWinner;")->execute();
            //$this->connection->createCommand("UPDATE $tableName SET $sSgqaCode = NULL;")->execute();
            //$this->connection->createCommand("SELECT * FROM $wingametable ORDER BY `mails`;")->execute();
            
        }
        
        
        /**
         * 
         * @param string $sProperty
         * @param string $connectionString
         * @return string|NULL
         */
        public function getDBConnectionStringProperty($sProperty, $connectionString = null)
        {
            if (!isset($connectionString)) {
                $connectionString = $this->dbConnectionArray['connectionString'];
            }
            // Yii doesn't give us a good way to get the database name
            if (preg_match('/'.$sProperty.'=([^;]*)/', $connectionString, $aMatches) == 1) {
                return $aMatches[1];
            }
            return null;
        }
        
        /**
         * check if the plugin should
         * be used in certain survey
         * 
         * @param string $sSurveyId
         * @return boolean
         */
        private function isWingameEnabled($sSurveyId)
        {
            return ($this->get('bUse','Survey',$sSurveyId)==0)||($this->get('bUse',null,null,$this->settings['bUse'])==0);
        }
        
        
        private function getKindOfCode($sSurveyId){
            if ($this->get('kCode','Survey',$sSurveyId)==0){
                return $this->get('code','Survey',$sSurveyId).sgqa;
            }
            else{
                return $this->get('code','Survey',$sSurveyId);
            }
        }
        
        
    }

?>