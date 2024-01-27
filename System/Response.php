<?php

namespace AMT\System;

/**
 * Wordpress api response class
 *
 */
class Response
{

    /**
     * @param $data
     * @param int $code
     */
    public function json($data = null, int $code = 200): void
    {
        wp_send_json($data, $code);
    }


    /**
     * @param $data
     * @param int $code
     * @return \WP_REST_Response
     */
    public function send($data = null, int $code = 200): \WP_REST_Response
    {
        return new \WP_REST_Response($data, $code);
    }


    /**
     * @param $data
     * @param int $code
     * @return \WP_REST_Response
     */
    public function success($data = null, int $code = 200): \WP_REST_Response
    {
        return new \WP_REST_Response($data, $code);
    }


    /**
     * @param $data
     * @param int $code
     * @return \WP_REST_Response
     */
    public function error($data = null, int $code = 423): \WP_REST_Response
    {
        return new \WP_REST_Response($data, $code);
    }
}
