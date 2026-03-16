<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var string $concreteVersion
 */

$installerProps = [
    'logo' => ASSETS_URL_IMAGES . '/logo_hand_only.svg',
    'loadStringsUrl' => (string) URL::to('/install/i18n'),
    'reloadPreconditionsUrl' => (string) URL::to('/install'),
    'validateEnvironmentUrl' => (string) URL::to('/install/validate_environment'),
    'beginInstallationUrl' => (string) URL::to('/install/begin_installation'),
    'lang' => $lang,
    'locales' => $locales,
    'languages' => $languages,
    'onlineLocales' => $onlineLocales,
    'concreteVersion' => $concreteVersion,
    'siteLocaleLanguage' => $siteLocaleLanguage,
    'countries' => $countries,
    'siteLocaleCountry' => $siteLocaleCountry,
    'timezone' => $timezone,
    'timezones' => $timezones,
    'defaultStartingPoint' => 'atomik',
    'startingPointRoutineUrl' => (string) URL::to('/install/run_routine'),
    'installationCompleteUrl' => (string) URL::to('/'),
];

if (isset($locale)) {
    $installerProps['locale'] = $locale;
}

if (isset($preconditions)) {
    $installerProps['preconditions'] = $preconditions;
}

if (isset($startingPoints)) {
    $installerProps['startingPoints'] = $startingPoints;
}
?>
<div class="min-h-screen flex">
    <div v-cloak id="ccm-page-install" class="pt-8 pb-24 w-full mx-auto max-w-screen-lg my-auto"></div>
    <script type="application/json" id="ccm-page-install-props"><?= json_encode($installerProps, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
</div>

<noscript>
    <div class="text-center lead"><?= t('JavaScript is required to run the Concrete CMS installer. Please enable it in your browser.') ?></div>
</noscript>
