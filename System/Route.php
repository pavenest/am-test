<?php

namespace AMT\System;

class Route
{
    protected string $rnm;
    protected $handler;
    protected $method;
    protected string $parsedUri;
    protected string $uri;
    protected array $paramPattern = [];
    protected $options;

    public function __construct($rNamespace, $uri, $handler, $method)
    {
        $this->rnm = $rNamespace;
        $this->uri = $uri;

        $this->handler = $handler;
        $this->method = $method;

    }

    public function register()
    {
        $this->options = [
            'args' => [],
            'methods' => $this->method,
            'callback' => [$this, 'callback'],
            'permission_callback' => [$this, 'permissionCallback']
        ];

        $uri = $this->parseRoute($this->uri);

        return register_rest_route($this->rnm, "/{$uri}", $this->options);
    }


    protected function parseRoute($uri): array|string|null
    {

        /**
         * v2/check_cache/(?P<type>\w+)/(?P<category>[\d]+)/(?P<sort>[\w]+)
         *
         * v2/check_cache/instagram/2/asc
         *
         * request->params : $data['type'] == 'instagram', $data['category'] = 2, $data['sort'] == 'asc'
         *
         */


        $parsed = preg_replace_callback('/\/{([.\S]*?)}/', function ($matches) {

            $match = $matches[1];
            $params[] = $match;
            $def = '\w+';


            if (isset($this->paramPattern[$match])) {
                $def = $this->paramPattern[$match];
            }

            $this->options['args'][$match]['required'] = true;

            return '/(?P<' . $match . '>' . $def . ')';

        }, $uri);


        return $this->parsedUri = $parsed;
    }


    public function callback(WP_REST_Request $request)
    {
        try {
            $this->setRestRequest($request);

            $response = $this->app->call(
                $this->app->parseRestHandler($this->handler),
                array_values($request->get_url_params())
            );

            if (!($response instanceof WP_REST_Response)) {
                if (is_wp_error($response)) {
                    $response = $this->sendWPError($response);
                } else {
                    $response = $this->app->response->sendSuccess($response);
                }
            }

            return $response;

        } catch (ValidationException $e) {
            return $this->app->response->sendError(
                $e->errors(), $e->getCode()
            );
        }  catch (ModelNotFoundException $e) {
            return $this->app->response->sendError([
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return $this->app->response->sendError([
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }


    /**
     *
     * @param $idd
     * @return $this
     */
    public function number($idd)
    {
        $idd = is_array($idd) ? $idd : func_get_args();

        foreach ($idd as $nm) {
            $this->paramPattern[$nm] = '[\d]+';
        }

        return $this;
    }

    public function chars($idd)
    {
        $idd = is_array($idd) ? $idd : func_get_args();

        foreach ($idd as $nm) {
            $this->paramPattern[$nm] = '[a-zA-Z]+';
        }

        return $this;
    }


    public function aln($idd)
    {
        $idd = is_array($idd) ? $idd : func_get_args();

        foreach ($idd as $nm) {
            $this->paramPattern[$nm] = '[a-zA-Z0-9-_]+';
        }

        return $this;
    }

}
