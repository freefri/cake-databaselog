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

        $env = $this->config['environment'];
        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
            $env = 'localhost';
        }

        $logEntry = [
            $this->config['model'] => [
                'type' => $type,
                'title' => $title,
                'message' => mb_substr($message, 0, 65535),
                'environment' => $env,
                'server' => json_encode($this->_getSecuredServer()),
            ]
        ];

        if (in_array($type, $this->config['types'])) {
            $this->LogModel->create();
            $this->LogModel->save($logEntry);
        }
    }

    private function _getSecuredServer()
    {
        $server['AUTH_TOKEN_UID'] = $_SERVER['AUTH_TOKEN_UID'] ?? '';
        $server['TAG_VERSION'] = $_SERVER['TAG_VERSION'] ?? '';
        $server['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? '';
        $server['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '';
        $server['APPLICATION_ENV'] = $_SERVER['APPLICATION_ENV'] ?? '';
        $server['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $server['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? '';
        $server['SERVER_ADDR'] = $_SERVER['SERVER_ADDR'] ?? '';
        $server['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $server['QUERY_STRING'] = $_SERVER['QUERY_STRING'] ?? '';
        $server['REQUEST_TIME_FLOAT'] = $_SERVER['REQUEST_TIME_FLOAT'] ?? '';
        return $server;
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
