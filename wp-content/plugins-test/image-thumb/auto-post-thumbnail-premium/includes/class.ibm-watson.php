<?php

/**
 * Class WATP_IBMWatson
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru> <Tg:@alex_brin>
 * @version 1.0.0
 * @since   1.0.0
 */
class WAPT_IBMWatson {
    /**
     * @var string
     */
    private $text;

    /**
     * @var array = [
     *     'categories' => stdClass::class
     * ]
     */
    private $features = [];

    /**
     * WAPT_IBMWatson constructor.
     *
     * @param $text
     */
    public function __construct( $text ) {
        $this->text = $text;
    }

    /**
     * @return $this
     */
    public function categories() {
        $this->features['categories'] = new stdClass();
        return $this;
    }

    /**
     * @return array
     */
    public function analyze() {
        return $this->request();
    }

    /**
     * @return array = [
     *     'usage' => [
     *          'text_units' => int,
     *          'text_characters' => int,
     *          'features' => int,
     *      ],
     *      'language' => string,
     *      'categories' => [
     *          [
     *              'label' => '/category 1/category 2/etc' | string,
     *              'score' => 0.90 | float,
     *          ]
     *      ]
     * ]
     */
    protected function request() {
        $apikey = WAPT_Plugin::app()->getPopulateOption( 'ibm-watson-apikey' );
        $endpoint = WAPT_Plugin::app()->getPopulateOption( 'ibm-watson-endpoint' );

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "$endpoint/v1/analyze?version=2019-07-12",
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_USERPWD => "apikey:$apikey",
            CURLOPT_POSTFIELDS => json_encode( [
                'text' => $this->text,
                'features' => $this->features,
            ] ),
        ]);

        $response = json_decode( curl_exec( $ch ), true );

        curl_close( $ch );

        return $response;
    }
}