<?php

namespace Avram\WPDebugBar\Collectors;

use DebugBar\DataCollector\MessagesCollector;

class LoadedFilesCollector extends MessagesCollector
{
    public function __construct($name = 'Database')
    {
        parent::__construct($name);
    }

    public function collect()
    {
        $files = get_included_files();

        foreach ($files as $file) {

            $relativePath = str_replace(ABSPATH, '', $file);
            if (stripos($relativePath, basename(WP_CONTENT_DIR)) === 0) {
                $parts   = explode('/', $relativePath);
                $dirName = $parts[1].'/'.$parts[2];
            } elseif (stripos($relativePath, 'wp-includes') === 0) {
                $dirName = 'wp-includes';
            } elseif (stripos($relativePath, 'wp-admin') === 0) {
                $dirName = 'wp-admin';
            } else {
                $dirName = basename(dirname($relativePath));
            }

            if ($dirName == '.') {
                $dirName = '/';
            }

            $this->addMessage($relativePath, $dirName);
        }

        $messages = $this->getMessages();
        return array(
            'count'    => count($messages),
            'messages' => $messages
        );

    }

    public function getWidgets()
    {
        return array(
            $this->getName()          => array(
                'icon'    => 'database',
                "widget"  => "PhpDebugBar.Widgets.MessagesWidget",
                "map"     => $this->getName().".messages",
                "default" => "[]"
            ),
            $this->getName().":badge" => array(
                "map"     => $this->getName().".count",
                "default" => "null"
            )
        );
    }

}