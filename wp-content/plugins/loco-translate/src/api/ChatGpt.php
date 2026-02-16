<?php
/**
 * OpenAI compatible "Chat Completions" auto-translation provider.
 */
abstract class Loco_api_ChatGpt extends Loco_api_Client {


    public static function supports( string $vendor ): bool {
        return Loco_api_Providers::VENDOR_GOOGLE === $vendor || Loco_api_Providers::VENDOR_OPENAI === $vendor || Loco_api_Providers::VENDOR_OROUTE === $vendor;
    }


    /**
     * @param string[][] $items input messages with keys "source", "context" and "notes"
     * @return string[] Translated strings
     * @throws Loco_error_Exception
     */
    public static function process( array $items, Loco_Locale $locale, array $config ): array {

        $targets = [];

        $vendor = $config['vendor'] ?? Loco_api_Providers::VENDOR_OPENAI;

        $endpoint = [
            Loco_api_Providers::VENDOR_OPENAI => 'https://api.openai.com/v1/chat/completions',
            Loco_api_Providers::VENDOR_GOOGLE => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
            Loco_api_Providers::VENDOR_OROUTE => 'https://openrouter.ai/api/v1/chat/completions',
        ][$vendor] ?? 'https://api.openai.com/v1/chat/completions';

        $model = $config['model'] ?? '';
        if ('' === $model) {
            $model = [
                Loco_api_Providers::VENDOR_OPENAI => 'gpt-4.1-nano',
                Loco_api_Providers::VENDOR_GOOGLE => 'gemini-2.5-flash-lite',
                Loco_api_Providers::VENDOR_OROUTE => 'openai/gpt-4.1-nano',
            ][$vendor] ?? 'gpt-4.1-nano';
        }

        $temperature = isset($config['temperature']) ? (float) $config['temperature'] : 0.0;
        if ($temperature < 1.0 && str_starts_with($model, 'gpt-5')) {
            $temperature = 1.0;
        }

        $sourceTag  = 'en_US';
        $sourceLang = 'English';
        $targetTag  = (string) $locale;
        $targetLang = self::wordy_language($locale);

        $params = Loco_mvc_PostParams::get();
        if (isset($params['source']) && is_string($params['source']) && '' !== $params['source']) {
            $srcLocale = Loco_Locale::parse($params['source']);
            if ($srcLocale->isValid()) {
                $sourceTag  = $params['source'];
                $sourceLang = self::wordy_language($srcLocale);
            }
        }

        Loco_data_CompiledData::flush();

        $instructions = [
            'Respond only in ' . $targetLang,
        ];

        $tone = $locale->getFormality();
        if ('' !== $tone) {
            $instructions[] = 'Use only the ' . $tone . ' tone of ' . $targetLang;
        }

        $prompt = "# Identity\n\nYou are a translator that translates from "
            . $sourceLang . ' (' . $sourceTag . ') to '
            . $targetLang . ' (' . $targetTag . ").\n\n"
            . "# Instructions\n\n* " . implode(".\n* ", $instructions) . '.';

        $custom = apply_filters('loco_gpt_prompt', $config['prompt'] ?? '', $locale);
        if (is_string($custom)) {
            $custom = trim($custom, "\n* ");
            if ('' !== $custom) {
                $prompt .= "\n\n* " . $custom;
            }
        }

        $offset     = 0;
        $totalItems = count($items);

        while ($offset < $totalItems) {

            $bytes = 0;
            $batch = [];

            while ($bytes < 5000 && $offset < $totalItems) {

                $item = $items[$offset];

                $meta = [];
                if (!empty($item['context'])) {
                    $meta[] = $item['context'];
                }
                if (!empty($item['notes'])) {
                    $meta[] = $item['notes'];
                }

                $source = [
                    'id'      => $offset,
                    'text'    => (string) ($item['source'] ?? ''),
                    'context' => implode("\n", $meta),
                ];

                $bytes += strlen($source['text'] . $source['context']);
                $batch[] = $source;
                $offset++;
            }

            $requestBody = [
                'model'       => $model,
                'temperature' => $temperature,
                'messages'    => [
                    [ 'role' => 'developer', 'content' => $prompt ],
                    [ 'role' => 'user', 'content' => 'Translate the `text` properties of the following JSON objects, using the `context` property to identify the meaning' ],
                    [ 'role' => 'user', 'content' => json_encode($batch, JSON_UNESCAPED_UNICODE) ],
                ],
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name'   => 'translations_array',
                        'strict' => true,
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'result' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => [
                                                'type' => 'number',
                                            ],
                                            'text' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                        'required' => ['id', 'text'],
                                        'additionalProperties' => false,
                                    ],
                                ],
                            ],
                            'required' => ['result'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ];

            $args = self::init_request_arguments($config, $requestBody);
            $args['timeout']     = 120;
            $args['httpversion'] = '1.1';
            $args['blocking']    = true;

            $result = wp_remote_request($endpoint, $args);

            if (is_wp_error($result)) {
                throw new Loco_error_Exception(
                    'HTTP request failed: ' . $result->get_error_message()
                );
            }

            try {

                $data = self::decode_response($result);

                foreach ($data['choices'] as $choice) {

                    $blob = $choice['message'] ?? null;
                    if (!is_array($blob) || ($blob['role'] ?? '') !== 'assistant') {
                        continue;
                    }

                    $content = json_decode(trim((string) $blob['content']), true);

                    if (!is_array($content) || !isset($content['result']) || !is_array($content['result'])) {
                        continue;
                    }

                    $responseItems = $content['result'];

                    foreach ($responseItems as $output) {

                        if (!isset($output['id'], $output['text'])) {
                            continue;
                        }

                        $id = (int) $output['id'];

                        if (isset($batch[$id])) {
                            $targets[$id] = (string) $output['text'];
                        }
                    }
                }

            } catch (Throwable $e) {

                $name = $config['name'] ?? $vendor;
                throw new Loco_error_Exception($name . ': ' . $e->getMessage());
            }
        }

        ksort($targets);

        return $targets;
    }



    private static function wordy_language( Loco_Locale $locale ):string {
        $names = Loco_data_CompiledData::get('languages');
        return $names[ $locale->lang ] ?? $locale->lang;
    }


    private static function init_request_arguments( array $config, array $data ): array {

        $origin = '';
        if ( ! empty($_SERVER['HTTP_ORIGIN']) && is_string($_SERVER['HTTP_ORIGIN']) ) {
            $origin = $_SERVER['HTTP_ORIGIN'];
        } else {
            $origin = home_url();
        }

        return [
            'method'      => 'POST',
            'timeout'     => 120, // explicit timeout instead of global filter
            'redirection' => 0,
            'httpversion' => '1.1',
            'blocking'    => true,
            'user-agent'  => parent::getUserAgent(),
            'reject_unsafe_urls' => false,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $config['key'],
                'Origin'        => $origin,
                'Referer'       => trailingslashit($origin) . 'wp-admin/',
                'Connection'    => 'close',
            ],
            'body' => json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            ),
        ];
    }



    private static function decode_response( $result ):array {
        $data = parent::decodeResponse($result);
        $status = $result['response']['code'];
        if( 200 !== $status ){
            $message = $data['error']['message'] ?? null;
            if( is_null($message) ){
                // Gemini returns array of errors, instead of single object.
                foreach( $data as $item ){
                    $message = $item['error']['message'] ?? null;
                    if( is_string($message) ){
                        break;
                    }
                }
            }
            throw new Exception( sprintf('API returned status %u: %s',$status,$message??'Unknown error') );
        }
        // all responses have form {choices:[...]}
        if( ! array_key_exists('choices',$data) || ! is_array($data['choices']) ){
            throw new Exception('API returned unexpected data');
        }
        return $data;
    }


}
