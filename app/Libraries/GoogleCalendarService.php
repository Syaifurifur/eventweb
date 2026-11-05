<?php

namespace App\Libraries;

use App\Models\GoogleTokenModel;
use Config\GoogleCalendar;
use GuzzleHttp\Client;

class GoogleCalendarService
{
    private GoogleCalendar $config;
    private Client $client;
    private GoogleTokenModel $tokens;

    public function __construct()
    {
        $this->config = new GoogleCalendar();
        $this->client = service('curlrequest', [
            'timeout' => 20,
            'http_errors' => false,
        ]);
        $this->tokens = new GoogleTokenModel();
    }

    public function isConfigured(): bool
    {
        return $this->config->clientId !== '' && $this->config->clientSecret !== '';
    }

    public function authorizationUrl(int $userId): string
    {
        $state = bin2hex(random_bytes(24));
        session()->put('google_oauth_state', $state);
        session()->put('google_oauth_user_id', $userId);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $this->config->clientId,
            'redirect_uri' => $this->config->redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/calendar.events.readonly',
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function handleCallback(int $userId, string $code): void
    {
        $response = $this->client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'code' => $code,
                'client_id' => $this->config->clientId,
                'client_secret' => $this->config->clientSecret,
                'redirect_uri' => $this->config->redirectUri,
                'grant_type' => 'authorization_code',
            ],
        ]);

        $payload = $this->decodeResponse($response->getStatusCode(), (string) $response->getBody());
        $existing = $this->tokens->tokenForUser($userId);

        $data = [
            'user_id' => $userId,
            'provider' => 'google_calendar',
            'access_token' => $payload['access_token'],
            'refresh_token' => $payload['refresh_token'] ?? ($existing['refresh_token'] ?? null),
            'scope' => $payload['scope'] ?? null,
            'token_type' => $payload['token_type'] ?? 'Bearer',
            'expires_at' => date('Y-m-d H:i:s', time() + (int) ($payload['expires_in'] ?? 3600) - 60),
        ];

        if ($existing) {
            $this->tokens->update($existing['id'], $data);
            return;
        }

        $this->tokens->insert($data);
    }

    public function tokenStatus(int $userId): array
    {
        $token = $this->tokens->tokenForUser($userId);

        if (! $token) {
            return ['connected' => false, 'expires_at' => null];
        }

        return [
            'connected' => true,
            'expires_at' => $token['expires_at'],
        ];
    }

    /**
     * Admin Sistem may download from the shared admin calendar connection.
     * Prefer the current admin's connection and fall back to another active
     * Admin Sistem so every super admin does not need to repeat OAuth setup.
     */
    public function adminDownloadTokenUserId(int $userId): ?int
    {
        if ($this->tokens->tokenForUser($userId)) {
            return $userId;
        }

        $token = $this->tokens->latestActiveAdminToken();

        return $token ? (int) $token['user_id'] : null;
    }

    public function syncEvent(array $event, int $userId, ?string $googleEventId = null): array
    {
        $accessToken = $this->validAccessToken($userId);
        $resource = $this->calendarEventResource($event);
        $existingEventId = trim((string) $googleEventId);

        if ($existingEventId === '') {
            $existingGoogleEvent = $this->findExistingEventHubEvent($event, $accessToken);
            $existingEventId = (string) ($existingGoogleEvent['id'] ?? '');
        }

        if ($existingEventId !== '') {
            $response = $this->client->request(
                'PATCH',
                'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->config->calendarId) . '/events/' . rawurlencode($existingEventId),
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($resource, JSON_THROW_ON_ERROR),
                ],
            );

            return $this->decodeResponse($response->getStatusCode(), (string) $response->getBody());
        }

        $response = $this->client->post(
            'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->config->calendarId) . '/events',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($resource, JSON_THROW_ON_ERROR),
            ],
        );

        return $this->decodeResponse($response->getStatusCode(), (string) $response->getBody());
    }

    private function calendarEventResource(array $event): array
    {
        $start = new \DateTimeImmutable($event['event_date'] . ' ' . $event['event_time'], new \DateTimeZone($this->config->timezone));
        $endDate = trim((string) ($event['event_end_date'] ?? ''));
        $end = $endDate !== '' && $endDate >= (string) $event['event_date']
            ? (new \DateTimeImmutable($endDate . ' ' . $event['event_time'], new \DateTimeZone($this->config->timezone)))->modify('+2 hours')
            : $start->modify('+2 hours');

        return [
            'summary' => $event['title'],
            'location' => $event['location'],
            'description' => trim(($event['description'] ?? '') . "\n\nEventHub code: " . $event['code']),
            'start' => [
                'dateTime' => $start->format('c'),
                'timeZone' => $this->config->timezone,
            ],
            'end' => [
                'dateTime' => $end->format('c'),
                'timeZone' => $this->config->timezone,
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 24 * 60],
                    ['method' => 'popup', 'minutes' => 30],
                ],
            ],
        ];
    }

    public function listEvents(int $userId, \DateTimeInterface $timeMin, \DateTimeInterface $timeMax): array
    {
        $accessToken = $this->validAccessToken($userId);

        return $this->listEventsWithToken($accessToken, $timeMin, $timeMax);
    }

    public function deleteEvent(int $userId, string $googleEventId): void
    {
        $googleEventId = trim($googleEventId);
        if ($googleEventId === '') {
            return;
        }

        $accessToken = $this->validAccessToken($userId);
        $response = $this->client->request(
            'DELETE',
            'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->config->calendarId) . '/events/' . rawurlencode($googleEventId),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
            ],
        );

        $statusCode = $response->getStatusCode();
        if (in_array($statusCode, [204, 404, 410], true)) {
            return;
        }

        $this->decodeResponse($statusCode, (string) $response->getBody());
    }

    private function listEventsWithToken(string $accessToken, \DateTimeInterface $timeMin, \DateTimeInterface $timeMax): array
    {
        $items = [];
        $pageToken = null;

        do {
            $query = [
                'timeMin' => $timeMin->format('c'),
                'timeMax' => $timeMax->format('c'),
                'singleEvents' => 'true',
                'orderBy' => 'startTime',
                'showDeleted' => 'false',
                'maxResults' => 2500,
                'timeZone' => $this->config->timezone,
            ];

            if ($pageToken !== null) {
                $query['pageToken'] = $pageToken;
            }

            $response = $this->client->get(
                'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->config->calendarId) . '/events',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                    ],
                    'query' => $query,
                ],
            );

            $payload = $this->decodeResponse($response->getStatusCode(), (string) $response->getBody());
            $items = array_merge($items, $payload['items'] ?? []);
            $pageToken = $payload['nextPageToken'] ?? null;
        } while ($pageToken !== null);

        return $items;
    }

    private function findExistingEventHubEvent(array $event, string $accessToken): ?array
    {
        $timezone = new \DateTimeZone($this->config->timezone);
        $eventDate = new \DateTimeImmutable((string) $event['event_date'], $timezone);
        $endDate = trim((string) ($event['event_end_date'] ?? ''));
        $lastDate = $endDate !== '' && $endDate >= (string) $event['event_date']
            ? new \DateTimeImmutable($endDate, $timezone)
            : $eventDate;
        $timeMin = $eventDate->setTime(0, 0)->modify('-1 day');
        $timeMax = $lastDate->setTime(23, 59, 59)->modify('+1 day');
        $needle = '/EventHub code:\s*' . preg_quote((string) $event['code'], '/') . '\b/i';

        foreach ($this->listEventsWithToken($accessToken, $timeMin, $timeMax) as $item) {
            if (($item['status'] ?? '') === 'cancelled') {
                continue;
            }

            if (preg_match($needle, (string) ($item['description'] ?? '')) === 1) {
                return $item;
            }
        }

        return null;
    }

    private function validAccessToken(int $userId): string
    {
        $token = $this->tokens->tokenForUser($userId);

        if (! $token) {
            throw new \RuntimeException('Google Calendar belum terhubung. Klik Connect Google Calendar terlebih dahulu.');
        }

        if (strtotime((string) $token['expires_at']) > time() + 60) {
            return $token['access_token'];
        }

        if (empty($token['refresh_token'])) {
            throw new \RuntimeException('Refresh token Google tidak tersedia. Hubungkan ulang Google Calendar.');
        }

        $response = $this->client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'client_id' => $this->config->clientId,
                'client_secret' => $this->config->clientSecret,
                'refresh_token' => $token['refresh_token'],
                'grant_type' => 'refresh_token',
            ],
        ]);

        $payload = $this->decodeResponse($response->getStatusCode(), (string) $response->getBody());
        $this->tokens->update($token['id'], [
            'access_token' => $payload['access_token'],
            'expires_at' => date('Y-m-d H:i:s', time() + (int) ($payload['expires_in'] ?? 3600) - 60),
            'scope' => $payload['scope'] ?? $token['scope'],
            'token_type' => $payload['token_type'] ?? $token['token_type'],
        ]);

        return $payload['access_token'];
    }

    private function decodeResponse(int $statusCode, string $body): array
    {
        $payload = json_decode($body, true);

        if (! is_array($payload)) {
            throw new \RuntimeException('Google API mengembalikan response yang tidak valid.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = $payload['error_description'] ?? $payload['error']['message'] ?? $payload['error'] ?? 'Google API request gagal.';
            throw new \RuntimeException((string) $message);
        }

        return $payload;
    }
}
