<?php
require_once ROOT . '/src/Actions/Action.php';

// --- Auth ---
require ROOT . '/src/Actions/api/PostLoginAction.php';
require ROOT . '/src/Actions/api/PostRegisterAction.php';
require ROOT . '/src/Actions/api/GetRefreshAction.php';

// --- Map ---
require ROOT . '/src/Actions/api/GetMapDataAction.php';

// --- Statistics ---
require ROOT . '/src/Actions/api/GetStatisticsAction.php';

// --- Report / Download ---
require ROOT . '/src/Actions/api/GetReportDataAction.php';

// --- Upload / Admin ---
require ROOT . '/src/Actions/api/PostAccidentAction.php';
require ROOT . '/src/Actions/api/PostAccidentFileAction.php';

// --- Page: Auth ---
require ROOT . '/src/Actions/page/ViewLoginAction.php';
require ROOT . '/src/Actions/page/ViewRegisterAction.php';

// --- Page: Main ---
require ROOT . '/src/Actions/page/ViewHomeAction.php';
require ROOT . '/src/Actions/page/ViewMapAction.php';
require ROOT . '/src/Actions/page/ViewDownloadAction.php';
require ROOT . '/src/Actions/page/ViewUploadAction.php';