<?php
// inc/_global/quests/index.php
//
// This file simply includes every quest helper that you’ve built.
// Whenever you want to run “all relevant quest checks,” just require this file.

require_once __DIR__ . '/complete_profile.php';
require_once __DIR__ . '/chat_50_times.php';
+require_once __DIR__ . '/spin_wheel.php';
+require_once __DIR__ . '/follow_5_users.php';
+require_once __DIR__ . '/be_followed_10.php';
require_once __DIR__ . '/quest_progression.php';


// …and so on, as you add more quests in the future.
