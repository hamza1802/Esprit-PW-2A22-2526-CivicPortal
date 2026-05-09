<?php
/**
 * AIConfig.php
 * Central configuration for the Gemini AI assistant used by the Service
 * Requests module (FrontOffice "Improve with AI" + BackOffice "Analyze with AI").
 *
 * Two independent keys are used so that FrontOffice and BackOffice
 * AI calls run on separate quotas and cannot block each other.
 *
 * NOTE: API keys are placeholders. To enable AI features, set environment
 * variables GEMINI_API_KEY_FRONT and GEMINI_API_KEY_BACK, OR replace the
 * placeholder strings below with real Google AI Studio keys.
 * If a key is missing, the service degrades gracefully (a friendly message
 * is shown to the user; nothing crashes).
 */

class AIConfig
{
    /** Provider (only Gemini is supported). */
    public static string $provider = 'gemini';

    /**
     * Common Gemini settings shared by both contexts.
     *
     * Available alternatives (cost, lowest first):
     *   'gemini-2.5-flash-lite' or 'gemini-3.1-flash-lite' (when available)
     *      cheapest, ideal for high-volume short JSON
     *   'gemini-2.5-flash'         stable, no thinking-mode quirks      <- active
     *   'gemini-3-flash-preview'   newer flash, slightly more expensive
     *   'gemini-flash-latest'      auto-tracks the latest flash
     */
    public static array $gemini = [
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
        'model'    => 'gemini-2.5-flash',
    ];

    /**
     * Per-context API keys (Google AI Studio).
     * Prefer environment variables for security; fall back to inline placeholders.
     */
    public static function getKeys(): array
    {
        return [
            'frontoffice' => getenv('GEMINI_API_KEY_FRONT') ?: '',
            'backoffice'  => getenv('GEMINI_API_KEY_BACK')  ?: '',
        ];
    }

    /** Request timeout in seconds. */
    public static int $timeout = 30;

    /**
     * Get the resolved configuration for a given context.
     *
     * @param string $context 'frontoffice' | 'backoffice'
     */
    public static function get(string $context = 'frontoffice'): array
    {
        $keys   = self::getKeys();
        $apiKey = $keys[$context] ?? '';

        return [
            'provider' => self::$provider,
            'context'  => $context,
            'api_key'  => $apiKey,
            'endpoint' => self::$gemini['endpoint'],
            'model'    => self::$gemini['model'],
            'timeout'  => self::$timeout,
        ];
    }
}
