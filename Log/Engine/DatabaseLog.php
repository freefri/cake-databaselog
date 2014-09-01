<?php
/**
 * DatabaseLog.php
 *
 * PHP version 5.4.16
 *
 * @category Na
 * @package  DatabaseLog
 * @author   Freefri <freefri@freefri.es>
 * @license  The MIT License (MIT)
 * @link     Na
 * @since    17.03.14
 */
App::uses('CakeLogInterface', 'Log');
App::uses('ClassRegistry', 'Utility');

/**
 * Class DatabaseLog
 *
 * @category Na
 * @package  DatabaseLog
 * @author   Freefri <freefri@freefri.es>
 * @license  The MIT License (MIT)
 * @link     Na
 * @since    17.03.14
 */
class DatabaseLog implements CakeLogInterface
{
    protected $config;

    protected $LogModel;

    /**
     * Constructs a new File Logger.
     *
     * Config
     *
     * - `types` string or array, levels the engine is interested in
     * - `scopes` string or array, scopes the engine is interested in
     * - `file` log file name
     * - `path` the path to save logs on.
     *
     * @param array $config Options for the DatabaseLog
     *
     * @throws MissingModelException
     * @return \DatabaseLog
     */
    public function __construct($config = [])
    {
        $config = Hash::merge(
            [
                'model' => 'LogEntry',
                'types' => null,
                'scopes' => [],
                'environment' => null,
            ],
            $config
        );
        $this->config = $config;
        $this->loadModel($config['model']);
    }

    /**
     * Implements writing to log files.
     *
     * @param string $type    The type of log you are making.
     * @param string $message The message you want to log. Message kind of Title:Message
     *                        (title max len 30 char splited with colon)
     *
     * @return boolean success of write.
     */
    public function write($type, $message)
    {
        list($title) = explode(':', $message);
        $titleLen = strlen($title);
        if ($titleLen > 30) {
            $title = null;
        }

        $logEntry = [
            $this->config['model'] => [
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'environment' => $this->config['environment'],
                'server' => json_encode($_SERVER), // log php $_SERVER
            ]
        ];

        if (in_array($type, $this->config['types'])) {
            $this->LogModel->create();
            $this->LogModel->save($logEntry);
        }
    }

    /**
     * Loads and instantiates model required to store the log.
     * If the model is non existent, it will throw a missing database table error, as Cake generates
     * dynamic models for the time being.
     *
     * @param string         $modelClass Name of model class to load
     * @param integer|string $id         Initial ID the instanced model class should have
     *
     * @throws MissingModelException if the model class cannot be found.
     * @return bool True if the model was found
     */
    protected function loadModel($modelClass, $id = null)
    {
        list($plugin, $modelClass) = pluginSplit($modelClass, true);

        $this->LogModel = ClassRegistry::init(
            ['class' => $plugin . $modelClass, 'alias' => $modelClass, 'id' => $id]
        );
        if (!$this->LogModel) {
            throw new MissingModelException($modelClass);
        }
        return true;
    }
}
