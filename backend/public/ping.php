<?php

// Static health check for Render (no Laravel bootstrap required).
http_response_code(200);
header('Content-Type: text/plain');
echo 'pong';
