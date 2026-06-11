<?php
return [
  'name' => getenv('APP_NAME') ?: 'BlueHR',
  'url' => getenv('APP_URL') ?: 'http://localhost/bluehr/public',
  'session_name' => getenv('SESSION_NAME') ?: 'bluehr_session',
  'key' => getenv('APP_KEY') ?: 'change-me'
];
