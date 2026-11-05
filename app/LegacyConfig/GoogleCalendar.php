<?php

namespace Config;

final class GoogleCalendar
{
    public string $clientId;
    public string $clientSecret;
    public string $redirectUri;
    public string $calendarId;
    public string $timezone;

    public function __construct()
    {
        $this->clientId = (string) env('GOOGLE_CALENDAR_CLIENT_ID', '');
        $this->clientSecret = (string) env('GOOGLE_CALENDAR_CLIENT_SECRET', '');
        $this->redirectUri = (string) env('GOOGLE_CALENDAR_REDIRECT_URI', site_url('google-calendar/callback'));
        $this->calendarId = (string) env('GOOGLE_CALENDAR_ID', 'primary');
        $this->timezone = (string) env('GOOGLE_CALENDAR_TIMEZONE', 'Asia/Jakarta');
    }
}
