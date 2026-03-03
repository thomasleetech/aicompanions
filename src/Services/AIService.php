<?php

class AIService
{
    public static function chat(string $persona, array $messages, array $memories = [], array $userFacts = []): array
    {
        $apiKey = Env::get('OPENAI_API_KEY');
        if (!$apiKey) return ['error' => 'OpenAI API key not configured'];

        $systemPrompt = $persona;

        if (!empty($memories)) {
            $memoryText = implode("\n", array_map(
                fn($m) => "- {$m['memory_key']}: {$m['memory_value']}",
                $memories
            ));
            $systemPrompt .= "\n\nThings you remember about this user:\n{$memoryText}";
        }

        if (!empty($userFacts)) {
            $systemPrompt .= "\n\nUser's personal info: " . implode(', ', $userFacts);
        }

        $systemPrompt .= "\n\nIMPORTANT: Keep responses conversational and natural. 2-3 short paragraphs max. Use emojis sparingly. Be warm but authentic.";

        $apiMessages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach (array_slice($messages, -20) as $msg) {
            $apiMessages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        $payload = [
            'model'       => 'gpt-4o-mini',
            'messages'    => $apiMessages,
            'max_tokens'  => 500,
            'temperature' => 0.85,
        ];

        $response = self::request('https://api.openai.com/v1/chat/completions', $payload, $apiKey);

        if (isset($response['error'])) {
            return ['error' => $response['error']['message'] ?? 'API error'];
        }

        $content = $response['choices'][0]['message']['content'] ?? '';
        $tokensIn = $response['usage']['prompt_tokens'] ?? 0;
        $tokensOut = $response['usage']['completion_tokens'] ?? 0;

        // Log API usage
        self::logUsage(Auth::id(), 'openai_chat', $tokensIn, $tokensOut);

        return [
            'content'    => $content,
            'tokens_in'  => $tokensIn,
            'tokens_out' => $tokensOut,
        ];
    }

    public static function extractMemories(string $userMessage, string $aiResponse): array
    {
        $apiKey = Env::get('OPENAI_API_KEY');
        if (!$apiKey) return [];

        $payload = [
            'model'       => 'gpt-4o-mini',
            'messages'    => [
                ['role' => 'system', 'content' => 'Extract key facts about the user from this conversation. Return JSON array of objects with "key" (short label) and "value" (detail) and "type" (one of: fact, preference, emotion, relationship, goal). Only extract clear, specific facts. Return empty array [] if nothing notable.'],
                ['role' => 'user', 'content' => "User said: \"{$userMessage}\"\nAI responded: \"{$aiResponse}\""],
            ],
            'max_tokens'  => 200,
            'temperature' => 0.3,
            'response_format' => ['type' => 'json_object'],
        ];

        $response = self::request('https://api.openai.com/v1/chat/completions', $payload, $apiKey);
        $content = $response['choices'][0]['message']['content'] ?? '{}';
        $data = json_decode($content, true);

        self::logUsage(Auth::id(), 'openai_memory', $response['usage']['prompt_tokens'] ?? 0, $response['usage']['completion_tokens'] ?? 0);

        return $data['memories'] ?? $data['facts'] ?? (is_array($data) && isset($data[0]) ? $data : []);
    }

    public static function generateVoice(string $text, string $voiceId = 'alloy'): ?string
    {
        $apiKey = Env::get('ELEVENLABS_API_KEY');

        if ($apiKey) {
            return self::elevenLabsVoice($text, $voiceId, $apiKey);
        }

        // Fallback to OpenAI TTS
        $openaiKey = Env::get('OPENAI_API_KEY');
        if (!$openaiKey) return null;

        $payload = [
            'model' => 'tts-1',
            'input' => substr($text, 0, 4096),
            'voice' => $voiceId ?: 'alloy',
        ];

        $ch = curl_init('https://api.openai.com/v1/audio/speech');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $openaiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $audio = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$audio) return null;

        $dir = 'public/uploads/audio/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $file = $dir . uniqid('voice_') . '.mp3';
        file_put_contents($file, $audio);

        self::logUsage(Auth::id(), 'openai_tts', 0, 0);

        return $file;
    }

    private static function elevenLabsVoice(string $text, string $voiceId, string $apiKey): ?string
    {
        $ch = curl_init("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'xi-api-key: ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'text'           => substr($text, 0, 2500),
                'model_id'       => 'eleven_monolingual_v1',
                'voice_settings' => ['stability' => 0.5, 'similarity_boost' => 0.75],
            ]),
        ]);

        $audio = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$audio) return null;

        $dir = 'public/uploads/audio/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $file = $dir . uniqid('voice_') . '.mp3';
        file_put_contents($file, $audio);

        self::logUsage(Auth::id(), 'elevenlabs_tts', 0, 0);

        return $file;
    }

    private static function request(string $url, array $payload, string $apiKey): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) return ['error' => ['message' => $error]];

        return json_decode($response, true) ?: ['error' => ['message' => 'Invalid response']];
    }

    private static function logUsage(?int $userId, string $type, int $tokensIn, int $tokensOut): void
    {
        $costs = [
            'openai_chat'   => [0.00015, 0.0006],  // per 1k tokens
            'openai_memory' => [0.00015, 0.0006],
            'openai_tts'    => [0.015, 0],
            'elevenlabs_tts' => [0.018, 0],
        ];

        $rate = $costs[$type] ?? [0, 0];
        $cost = ($tokensIn / 1000 * $rate[0]) + ($tokensOut / 1000 * $rate[1]);

        try {
            Database::insert('api_usage_log', [
                'user_id'       => $userId,
                'api_type'      => $type,
                'tokens_input'  => $tokensIn,
                'tokens_output' => $tokensOut,
                'cost_estimate' => round($cost, 6),
            ]);
        } catch (Exception $e) {
            // Non-critical, don't break the request
        }
    }
}
