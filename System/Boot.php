<?php

namespace AMT\System;

class Boot
{

    protected Engine $engine;
    protected string $path;

    public function __construct(string $path)
    {

        do_action('am_test/before_loaded', $this);

        $this->path = $path;
        $this->engine = new Engine();

        add_action('init', [$this, 'i18n']);
        add_action('rest_api_init', [$this, 'apiRoutes']);
        add_action('plugins_loaded', [$this, 'init']);
    }


    public function i18n()
    {
        load_plugin_textdomain(
            'am-test',
            false,
            plugin_dir_path(plugin_basename($this->path)) . '/languages/'
        );
    }


    public function init()
    {

        //Nothing for now....
        //rest_api_init

        do_action('amt_loaded', $this->engine);
    }

    public function apiRoutes($wpRestServer) {

    }
}
