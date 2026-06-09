<?php
require_once ROOT . '/src/Actions/Action.php';

// --- Auth ---
require ROOT . '/src/Actions/api/PostLoginAction.php';
require ROOT . '/src/Actions/api/PostRegisterAction.php';
require ROOT . '/src/Actions/api/GetRefreshAction.php';
require ROOT . '/src/Actions/api/DeleteLogoutAction.php';

// --- Map ---
require ROOT . '/src/Actions/api/GetMapDataAction.php';

// --- Statistics ---
require ROOT . '/src/Actions/api/GetStatisticsAction.php';

// --- Report / Download ---
require ROOT . '/src/Actions/api/GetReportDataAction.php';
require ROOT . '/src/Actions/api/GetReportFileAction.php';

// --- Upload / Admin ---
require ROOT . '/src/Actions/api/PostAccidentAction.php';
require ROOT . '/src/Actions/api/PostAccidentFileAction.php';
require ROOT . '/src/Actions/api/PutAccidentFileAction.php';

// --- Profile ---
require ROOT . '/src/Actions/api/GetProfileAction.php';
require ROOT . '/src/Actions/api/PatchProfileAction.php';

// --- API: RSS ---
require ROOT . '/src/Actions/api/GetRssAction.php';

// --- API: NLP ---
require ROOT . '/src/Actions/api/QueryNlpAction.php';

// --- Page: Auth ---
require ROOT . '/src/Actions/page/ViewLoginAction.php';
require ROOT . '/src/Actions/page/ViewRegisterAction.php';

// --- Page: Main ---
require ROOT . '/src/Actions/page/ViewAboutAction.php';
require ROOT . '/src/Actions/page/ViewIndexAction.php';
require ROOT . '/src/Actions/page/ViewHomeAction.php';
require ROOT . '/src/Actions/page/ViewAccountAction.php';
require ROOT . '/src/Actions/page/ViewAdminAction.php';