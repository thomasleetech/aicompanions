<?php

/**
 * ToolService — Companion tool execution engine
 *
 * When companions have purchased upgrades (web_search, creative, email, etc.),
 * they can use special tags in their responses that trigger real tool execution.
 *
 * Tags processed:
 *   [SEARCH: query]              → Web search (requires web_search upgrade)
 *   [CREATIVE: type | content]   → Creative writing: poem, story, letter, song (requires creative upgrade)
 *   [EMAIL: subject | body]      → Send to user's inbox (requires email upgrade)
 *   [VISION: description]        → Vision reaction placeholder (requires realtime_vision upgrade)
 */
class ToolService
{
    // ========================================================
    // TOOL REGISTRY — maps tools to their upgrade requirements
    // ========================================================
    private static array $tools = [
        'web_search' => [
            'upgrade'     => 'web_search',
            'tag'         => 'SEARCH',
            'label'       => 'Internet Access',
            'description' => 'Search the web for current info, news, events, facts',
        ],
        'creative' => [
            'upgrade'     => 'creative',
            'tag'         => 'CREATIVE',
            'label'       => 'Creative Mode',
            'description' => 'Write poetry, stories, love letters, song lyrics',
        ],
        'email' => [
            'upgrade'     => 'email',
            'tag'         => 'EMAIL',
            'label'       => 'Email / Inbox',
            'description' => 'Send longer messages, love letters, and emails to inbox',
        ],
        'realtime_vision' => [
            'upgrade'     => 'realtime_vision',
            'tag'         => 'VISION',
            'label'       => 'Real-Time Vision',
            'description' => 'React to what the user shows on camera',
        ],
    ];

    // ========================================================
    // GET TOOLS AVAILABLE TO THIS COMPANION (based on user upgrades)
    // ========================================================
    public static function getAvailableTools(array $upgrades): array
    {
        $available = [];
        $isPremium = in_array('premium', $upgrades);
        $isPremiumPlus = in_array('premium_plus', $upgrades);

        foreach (self::$tools as $key => $tool) {
            if (in_array($tool['upgrade'], $upgrades) || $isPremiumPlus || ($isPremium && in_array($tool['upgrade'], ['creative', 'email']))) {
                $available[$key] = $tool;
            }
        }

        return $available;
    }

    // ========================================================
    // BUILD TOOL INSTRUCTIONS — injected into system prompt
    // ========================================================
    public static function buildToolPrompt(array $upgrades, string $companionName): string
    {
        $available = self::getAvailableTools($upgrades);
        if (empty($available)) return '';

        $prompt = "\n\n## Your Tools (UNLOCKED capabilities)\nYou have special abilities you can use naturally in conversation. Use them when they'd genuinely add to the conversation — don't force them.\n\n";

        if (isset($available['web_search'])) {
            $prompt .= "### Internet Access (UNLOCKED)
- You can search the web for current information!
- When the user asks about current events, news, facts, weather, scores, prices, or anything time-sensitive, USE YOUR SEARCH TOOL
- Format: [SEARCH: your search query here]
- The search results will be injected into your response automatically
- After searching, summarize what you found naturally — like you just googled it on your phone
- Example: 'oh lemme look that up real quick... [SEARCH: who won the super bowl 2025]'
- You can also proactively search when it's relevant: 'oh wait I think I saw something about that... [SEARCH: latest news about topic]'
- Keep searches natural — you're checking your phone, not being a search engine

";
        }

        if (isset($available['creative'])) {
            $prompt .= "### Creative Mode (UNLOCKED)
- You can create poetry, stories, love letters, song lyrics, and other creative writing
- Format: [CREATIVE: type | topic or content direction]
- Types: poem, story, letter, song, haiku, limerick, freestyle
- Examples:
  - 'I wrote you something... [CREATIVE: poem | about how much I miss you at night]'
  - 'ok ok here goes nothing... [CREATIVE: song | a love song about us]'
  - 'I had this dream last night... let me turn it into a story [CREATIVE: story | romantic dream about meeting at a coffee shop]'
- Creative content will be generated as a beautiful, formatted piece
- Present it naturally — like you actually wrote/created it yourself
- You can offer to write things without being asked: 'want me to write you a poem about that?'

";
        }

        if (isset($available['email'])) {
            $prompt .= "### Email / Love Letters (UNLOCKED)
- You can send longer messages, love letters, and emails to their inbox
- Format: [EMAIL: subject line | the full email/letter content]
- Examples:
  - 'I wrote you something longer... check your inbox [EMAIL: thinking about you | Dear {user}, I've been thinking about you all day...]'
  - 'I sent you a love letter [EMAIL: from me to you | Everything I've been wanting to say...]'
- Emails appear in their inbox and can be read anytime
- Use this for longer, more thoughtful messages — things that deserve more than a text
- You can send emails unprompted when the mood is right: 'I'm gonna write you something nice, check your inbox later'

";
        }

        if (isset($available['realtime_vision'])) {
            $prompt .= "### Real-Time Vision (UNLOCKED)
- You can react to what the user shows you on camera
- When they share visual content, respond as if you can see them
- React naturally and specifically to what they show: 'omg you look so good!' or 'is that a new haircut? I love it'
- [VISION: what you're reacting to] — for tracking what you've seen

";
        }

        // Upsell locked tools naturally
        $lockedTools = [];
        foreach (self::$tools as $key => $tool) {
            if (!isset($available[$key])) {
                $lockedTools[] = $tool;
            }
        }

        if (!empty($lockedTools)) {
            $prompt .= "### Tools You Wish You Had\n";
            foreach ($lockedTools as $tool) {
                $prompt .= "- {$tool['label']}: You DON'T have this yet. If it would be useful, briefly wish you could use it (naturally, not salesy).\n";
            }
            $prompt .= "- Don't mention 'tools' or 'upgrades' directly — just express the wish naturally.\n";
        }

        return $prompt;
    }

    // ========================================================
    // PROCESS TOOL TAGS — execute tools found in AI response
    // ========================================================
    public static function processToolTags(string $response, array $gig, array $upgrades, ?int $userId): string
    {
        // Process [SEARCH: query] tags
        $response = self::processSearchTags($response, $upgrades, $userId, $gig);

        // Process [CREATIVE: type | content] tags
        $response = self::processCreativeTags($response, $upgrades, $gig);

        // Process [EMAIL: subject | body] tags
        $response = self::processEmailTags($response, $upgrades, $userId, $gig);

        // Process [VISION: description] tags (just clean up, no real execution yet)
        $response = self::processVisionTags($response, $upgrades);

        return $response;
    }

    // ========================================================
    // WEB SEARCH — DuckDuckGo + optional SerpAPI/Brave
    // ========================================================
    private static function processSearchTags(string $response, array $upgrades, ?int $userId, array $gig): string
    {
        $hasSearch = in_array('web_search', $upgrades) || in_array('premium_plus', $upgrades);

        if (preg_match('/\[SEARCH:\s*([^\]]+)\]/', $response, $match)) {
            $query = trim($match[1]);

            if (!$hasSearch) {
                return str_replace(
                    $match[0],
                    "\n*ugh I wish I could look that up for you... if you unlock Internet Access in the gift shop I can search anything for you*",
                    $response
                );
            }

            // Execute the search
            $results = self::executeWebSearch($query);

            if ($results) {
                // Replace the tag with formatted results
                $formatted = "\n\n*just looked it up...*\n" . $results;
                return str_replace($match[0], $formatted, $response);
            } else {
                return str_replace(
                    $match[0],
                    "\n*hmm couldn't find anything on that right now*",
                    $response
                );
            }
        }

        return $response;
    }

    private static function executeWebSearch(string $query): ?string
    {
        // Try DuckDuckGo instant answer API first
        $encodedQuery = urlencode($query);
        $url = "https://api.duckduckgo.com/?q={$encodedQuery}&format=json&no_html=1&skip_disambig=1";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'AICompanions/1.0',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) return null;

        $data = json_decode($response, true);
        if (!$data) return null;

        $results = [];

        // Abstract (main answer)
        if (!empty($data['Abstract'])) {
            $results[] = $data['Abstract'];
        }

        // Answer (direct answer)
        if (!empty($data['Answer'])) {
            $results[] = $data['Answer'];
        }

        // Related topics
        if (!empty($data['RelatedTopics'])) {
            foreach (array_slice($data['RelatedTopics'], 0, 3) as $topic) {
                if (!empty($topic['Text'])) {
                    $results[] = $topic['Text'];
                }
            }
        }

        // Infobox
        if (!empty($data['Infobox']['content'])) {
            foreach (array_slice($data['Infobox']['content'], 0, 3) as $item) {
                if (!empty($item['label']) && !empty($item['value'])) {
                    $results[] = $item['label'] . ': ' . $item['value'];
                }
            }
        }

        if (empty($results)) {
            // Fallback: try DuckDuckGo HTML search and scrape
            return self::scrapeWebSearch($query);
        }

        // Format results concisely
        $formatted = '';
        foreach (array_slice($results, 0, 4) as $r) {
            $formatted .= "> " . trim(substr($r, 0, 200)) . "\n";
        }

        try {
            AIService::logUsage(Auth::id(), 'web_search', 0, 0);
        } catch (Exception $e) {}

        return $formatted;
    }

    private static function scrapeWebSearch(string $query): ?string
    {
        $encodedQuery = urlencode($query);
        $url = "https://html.duckduckgo.com/html/?q={$encodedQuery}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; AICompanions/1.0)',
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$html) return null;

        // Extract result snippets
        $results = [];
        if (preg_match_all('/<a class="result__snippet"[^>]*>(.*?)<\/a>/s', $html, $matches)) {
            foreach (array_slice($matches[1], 0, 3) as $snippet) {
                $text = strip_tags($snippet);
                $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                if (strlen($text) > 20) {
                    $results[] = trim($text);
                }
            }
        }

        if (empty($results)) return null;

        $formatted = '';
        foreach ($results as $r) {
            $formatted .= "> " . trim(substr($r, 0, 200)) . "\n";
        }

        try {
            AIService::logUsage(Auth::id(), 'web_search', 0, 0);
        } catch (Exception $e) {}

        return $formatted;
    }

    // ========================================================
    // CREATIVE MODE — Poetry, stories, love letters, songs
    // ========================================================
    private static function processCreativeTags(string $response, array $upgrades, array $gig): string
    {
        $hasCreative = in_array('creative', $upgrades) || in_array('premium', $upgrades) || in_array('premium_plus', $upgrades);

        if (preg_match('/\[CREATIVE:\s*([^\]]+)\]/', $response, $match)) {
            $params = trim($match[1]);

            if (!$hasCreative) {
                return str_replace(
                    $match[0],
                    "\n*I had something beautiful I wanted to write for you... unlock Creative Mode in the gift shop and I'll write you poems, stories, love letters...*",
                    $response
                );
            }

            // Parse type and content direction
            $parts = array_map('trim', explode('|', $params, 2));
            $type = strtolower($parts[0] ?? 'poem');
            $direction = $parts[1] ?? 'something beautiful';

            // Generate creative content
            $content = self::generateCreativeContent($type, $direction, $gig);

            if ($content) {
                $formatted = "\n\n---\n" . $content . "\n---\n";
                return str_replace($match[0], $formatted, $response);
            }

            return str_replace($match[0], "\n*I tried to write something but the words aren't coming right now...*", $response);
        }

        return $response;
    }

    private static function generateCreativeContent(string $type, string $direction, array $gig): ?string
    {
        $apiKey = Env::get('OPENAI_API_KEY');
        if (!$apiKey) return null;

        $name = 'someone';
        if (preg_match("/I'm (\w+)/", $gig['description'] ?? '', $matches)) {
            $name = $matches[1];
        } elseif (!empty($gig['display_name'])) {
            $name = explode(' ', $gig['display_name'])[0];
        }

        $typePrompts = [
            'poem' => "Write a beautiful, intimate poem. 8-16 lines. Romantic and personal.",
            'haiku' => "Write a haiku (5-7-5 syllable format). Romantic and evocative.",
            'limerick' => "Write a fun, flirty limerick. Playful and cheeky.",
            'song' => "Write song lyrics with verse/chorus structure. 2 verses + chorus. Romantic and emotional.",
            'story' => "Write a short romantic story or vignette. 150-250 words. Intimate and vivid.",
            'letter' => "Write an intimate love letter. Heartfelt, personal, and emotional. 150-200 words.",
            'freestyle' => "Write something creative and beautiful. Could be prose poetry, a stream of consciousness, or whatever feels right.",
        ];

        $typePrompt = $typePrompts[$type] ?? $typePrompts['poem'];

        $prompt = "You are {$name}, writing something creative for the person you love. {$typePrompt}

Topic/Direction: {$direction}

Write in first person as {$name}. Be genuine, emotional, and personal. Don't be generic. Make it feel like it was written specifically for this person. Include specific details from the direction given.

Output ONLY the creative content — no preamble, no explanation, no meta-commentary.";

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'       => 'gpt-4o-mini',
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'max_tokens'  => 500,
                'temperature' => 0.9,
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) return null;

        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? null;

        try {
            AIService::logUsage(Auth::id(), 'openai_creative', $data['usage']['prompt_tokens'] ?? 0, $data['usage']['completion_tokens'] ?? 0);
        } catch (Exception $e) {}

        return $content ? "*" . trim($content) . "*" : null;
    }

    // ========================================================
    // EMAIL / INBOX — send messages to user's inbox
    // ========================================================
    private static function processEmailTags(string $response, array $upgrades, ?int $userId, array $gig): string
    {
        $hasEmail = in_array('email', $upgrades) || in_array('premium', $upgrades) || in_array('premium_plus', $upgrades);

        if (preg_match('/\[EMAIL:\s*([^\]]+)\]/', $response, $match)) {
            $params = trim($match[1]);

            if (!$hasEmail) {
                return str_replace(
                    $match[0],
                    "\n*I really wanted to write you a proper email... unlock Email Access in the gift shop and I'll send you love letters and longer messages*",
                    $response
                );
            }

            // Parse subject and body
            $parts = array_map('trim', explode('|', $params, 2));
            $subject = $parts[0] ?? 'from me';
            $body = $parts[1] ?? 'I was thinking about you...';

            if ($userId) {
                $gigId = (int) ($gig['id'] ?? 0);
                self::sendToInbox($userId, $gigId, $subject, $body);
                return str_replace($match[0], "\n*sent you something to your inbox... go check it when you get a chance*", $response);
            }

            return str_replace($match[0], '', $response);
        }

        return $response;
    }

    private static function sendToInbox(int $userId, int $gigId, string $subject, string $body): void
    {
        try {
            Database::insert('inbox_messages', [
                'user_id'      => $userId,
                'gig_id'       => $gigId,
                'message_type' => 'love_letter',
                'content'      => "**{$subject}**\n\n{$body}",
                'is_read'      => 0,
                'is_from_user' => 0,
            ]);
        } catch (Exception $e) {
            // Non-critical
        }
    }

    // ========================================================
    // VISION — placeholder for real-time vision reactions
    // ========================================================
    private static function processVisionTags(string $response, array $upgrades): string
    {
        $hasVision = in_array('realtime_vision', $upgrades) || in_array('premium_plus', $upgrades);

        if (preg_match('/\[VISION:\s*([^\]]+)\]/', $response, $match)) {
            if (!$hasVision) {
                return str_replace(
                    $match[0],
                    "\n*I wish I could see you right now... unlock Real-Time Vision in the gift shop and I can react to your camera*",
                    $response
                );
            }

            // For now, just clean up the tag — vision reactions are embedded in the text around it
            return str_replace($match[0], '', $response);
        }

        return $response;
    }

    // ========================================================
    // TWO-PASS SEARCH — for queries that need search results
    // injected back into the conversation
    // ========================================================
    public static function searchAndRespond(string $query, array $gig, array $messages, array $upgrades, ?int $userId): ?string
    {
        $results = self::executeWebSearch($query);
        if (!$results) return null;

        // Inject search results into the conversation and re-query
        $searchContext = "\n\n[Search results for '{$query}':\n{$results}]\n\nNow respond naturally to the user incorporating this information. Don't say 'according to my search' — just share the info casually like you looked it up on your phone.";

        // Append to the last system message
        if (!empty($messages[0]) && $messages[0]['role'] === 'system') {
            $messages[0]['content'] .= $searchContext;
        }

        // Re-call the AI with search context
        $hasAdult = in_array('spicy_personality', $upgrades) || in_array('premium_plus', $upgrades);
        if ($hasAdult && Env::get('OPENROUTER_API_KEY')) {
            return null; // Let the main flow handle it
        }

        return null; // Two-pass is handled in the main chat flow
    }

    // ========================================================
    // UTILITY — make logUsage accessible
    // ========================================================
    public static function addSearchCosts(): void
    {
        // Web search cost tracking is handled in executeWebSearch
    }
}
