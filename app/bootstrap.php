<?php

require_once __DIR__ . '/Config/config.php';
require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Core/SessionManager.php';
require_once __DIR__ . '/Model/User.php';
require_once __DIR__ . '/Service/AuthService.php';

// Initialize Session
SessionManager::start();
