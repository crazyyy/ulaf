<?php return array(
    'root' => array(
        'name' => 'yahnis-elsts/wp-toolbar-editor',
        'pretty_version' => 'trunk',
        'version' => 'dev-trunk',
        'reference' => NULL,
        'type' => 'project',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'yahnis-elsts/plugin-update-checker' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '3b1becb956ca4993752c4b1131f98a700fb4fa4f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../yahnis-elsts/plugin-update-checker',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'yahnis-elsts/wp-toolbar-editor' => array(
            'pretty_version' => 'trunk',
            'version' => 'dev-trunk',
            'reference' => NULL,
            'type' => 'project',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
