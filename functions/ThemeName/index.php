<?php

use ThemeName\Theme\Theme;
use ThemeName\Constants;
use ThemeName\Globals;
use ThemeName\Theme\Api;

Constants::initialize();
$ThemeActions = (new Theme())->addActions();
$ThemeApi = (Constants::getContainer())->make('Api');
