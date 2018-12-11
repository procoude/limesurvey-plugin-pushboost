<?php

    class Gewinnspiel extends PluginBase{
        
        protected $storage = 'DbStorage';
        static protected $description = 'Gewinnspiel für LimeSurvey. Locke mehr Teilnehmer an.';
        static protected $name = 'Gewinnspiel';
        
        public function init(){
            
            $this->subscribe('afterSurveyComplete');
            $this->subscribe('beforeSurveySettings');
            $this->subscribe('newSurveySettings');
            
        }
        
        protected $settings = array(
            /*'mySqlHost' => array(
                'type' => 'string',
                //'default'=>'',
                'label' => 'The MySQL-Host',
                'help'=>'The server your project lies on',
            ),
            'username' => array(
                'type' => 'string',
                //'default'=>'',
                'label' => 'Username',
                'help'=>'The username used to connect to the database',
            ),
            'password' => array(
                'type' => 'string',
                //'default'=>'',
                'label' => 'Password',
                'help'=>'The password used to connect to the database',
            ),
            'dbName' => array(
                'type' => 'string',
                //'default'=>'',
                'label' => 'Database name',
                'help'=>'The name of your LimeSurvey database',
            ),*/
            'sgqaCode' => array(
                'type' => 'string',
                'default' => '000000X00X00',
                'label' => 'SGQA Code',
                'help' => "The SGQA Code of the question in which you ask for the participant's e-mail adress",
            )
        );
        
              // --- Settings für einzelne Umfragen ---
        public function beforeSurveySettings(){
            $oEvent = $this->event;
            $oEvent->set("surveysettings.{$this->id}", array(
                'name' => get_class($this),
                'settings' => array(
                    'bUse' => array(
                        'type' => 'select',
                        'label' => 'Send a hook for this survey',
                        'options'=>array(
                            0=> 'No',
                            1=> 'Yes',
                            2=> 'Use site settings (default)'
                        ),
                        'default'=>2,
                        'help'=>'Leave default to use global setting',
                        'current'=> $this->get('bUse','Survey',$oEvent->get('survey')),
                    )
                    /*'sMySqlHost' => array(
                        'type' => 'string',
                        'label' => 'The MySQL-Host',
                        'help'=> 'The server your project lies on',
                        'current'=> $this->get('mySqlHost','Survey',$oEvent->get('survey'))
                    ),
                    'sUsername' => array(
                        'type' => 'string',
                        'label' => 'Username',
                        'help' => 'The username used to connect to your database',
                        'current' => $this->get('username','Survey',$oEvent->get('survey'))
                    ),
                    'sPassword' => array(
                        'type' => 'string',
                        'label' => 'Password',
                        'help'=> 'The password used to connect to your database',
                        'current'=> $this->get('password','Survey',$oEvent->get('survey'))
                    ),
                    'sDbName' => array(
                        'type' => 'string',
                        'label' => 'Database name',
                        'help' => 'The name of your LimeSurvey database',
                        'current' => $this->get('dbName','Survey',$oEvent->get('survey'))
                    )*/
                ),
            ));
        }
        
        
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
        
        public function afterSurveyComplete(){
            
            $readFromConfig = realpath($aArguments[0]);
            $this->configuration = include($readFromConfig);
            $this->dbConnectionArray = $this->configuration['components']['db'];
            
            foreach($this->configuration as $configKey => $configValue){
                Yii::app()->params[$configKey] = $configValue;
            }
            
            Yii::import('application.helpers.common_helper', true);
            
            $this->connection = App()->getDb();
            $this->connection->connectionString = $this->dbConnectionArray['connectionString'];
            //$dbName = $this->connection->quoteTableName($this->getDBConnectionStringProperty('dbname'));
            //$mySqlHost = $this->connection->quoteTableName($this->getDBConnectionStringProperty('host'));
            $this->connection->connectionString = $this->dbConnectionArray['connectionString'];
            $username = $this->connection->username = $this->dbConnectionArray['username'];
            $password = $this->connection->password = $this->dbConnectionArray['password'];
            
            //$this->connection->createCommand("ALTER DATABASE limesurvey_dev DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")->execute();
            $this->connection->createCommand("CREATE TABLE IF NOT EXISTS gewinnspiel (mails VARCHAR(150) NOT NULL);")->execute();
            
            $sgqaCode = $this->get('sgqaCode',null,null,$this->settings['sgqaCode']);
            $this->connection->createCommand("INSERT INTO `gewinnspiel`(`mails`) SELECT $sgqaCode FROM `lime_survey_759916` WHERE $sgqaCode IS NOT NULL;")->execute();
            
            $this->connection->createCommand("UPDATE lime_survey_759916 SET $sgqaCode = NULL;")->execute();
            $this->connection->createCommand("SELECT * FROM `gewinnspiel` ORDER BY `mails`;")->execute();
            
            //$this->connection->createCommand("INSERT INTO `gewinnspiel`(`mails`)VALUES('$sgqaCode');")->execute();
        }
        
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
        
    }

?>