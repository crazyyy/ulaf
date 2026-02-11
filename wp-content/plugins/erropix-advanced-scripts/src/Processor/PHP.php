<?php

namespace ERROPiX\AdvancedScripts\Processor;

class PHP extends Processor
{
    protected $file;

    public function init()
    {
        if (!$this->create_file()) {
            $this->hooks = [];
        }
    }

    public function __destruct()
    {
        if (is_writable($this->file)) {
            @unlink($this->file);
        }
    }

    public function create_file()
    {
        if ($this->code) {
            $dir = get_temp_dir();
            $filename = $this->term_id . "-" . sanitize_file_name(strtolower($this->title)) . '.php';
            $this->file = $dir . $filename;

            return (bool) file_put_contents($this->file, $this->code);
        }

        return false;
    }

    public function execute()
    {
        if (file_exists($this->file) || $this->create_file()) {
            include $this->file;
        }
    }
}
