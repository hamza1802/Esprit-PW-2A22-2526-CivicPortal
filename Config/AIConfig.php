<?php
/**
 * AIConfig.php
 * Central configuration for the Gemini AI assistant.
 *
 * Two independent keys are used so that FrontOffice and BackOffice
 * AI calls run on separate quotas and cannot block each other.
 */

class AIConfig
{
    /** Provider (only Gemini is supported). */
    public static string $provider = 'gemini';

    /** Common Gemini settings shared by both contexts. */
    public static array $gemini = [
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
        // Available alternatives:
        //   'gemini-3-pro-preview'      → highest quality, slower
        //   'gemini-3-flash-preview'    → fast, Gemini 3 flash             ← active
        //   'gemini-2.5-flash'          → stable, no thinking-mode quirks
        //   'gemini-flash-latest'       → auto-tracks latest flash
        'model'   => 'gemini-3-flash-preview',
    ];

    /** Per-context API keys (Google AI Studio). */
    public static array $keys = [
        // FrontOffice: "Améliorer avec IA" in the citizen request form.
        'frontoffice' => 'AIzaSyBRyNvkLUNdoGVHC3RdXHjiD17PZd_KQ-o',
        // BackOffice: "Analyser avec IA" in the worker dashboard.
        'backoffice'  => 'AIzaSyAPEUWBnd174XDYnktGmdr8vzBjhHbyw3c',
    ];

    /** Request timeout in seconds. */
    public static int $timeout = 30;

    /**
     * Get the resolved configuration for a given context.
     *
     * @param string $context 'frontoffice' | 'backoffice'
     */
    public static function get(string $context = 'frontoffice'): array
    {
        $apiKey = self::$keys[$context] ?? '';
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
