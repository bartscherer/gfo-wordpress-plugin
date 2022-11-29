<?php

/*
    Plugin Name: GFO - Google Fonts On-premise
    Description: A plugin for using a GDPR compliant proxy server instead of fonts.googleapis.com. Have a look at <a href="https://github.com/bartscherer/gfo">the GFO repository</a>.
    Version: 1.0
    Author: Thomas Bartscherer <thomas@bartscherer.io>
    Author URI: https://bartscherer.io
*/

global $GFO_ENABLED_LABEL;
global $GFO_MENU_SLUG;
global $GFO_MENU_TITLE;
global $GFO_PAGE_TITLE;
global $GFO_SECTION_DESCRIPTION;
global $GFO_SECTION_HEADLINE;
global $GFO_URL_LABEL;
$GFO_ENABLED_LABEL = 'Enable GFO';
$GFO_MENU_SLUG  = 'Google Fonts On-premise';
$GFO_MENU_TITLE = 'Google Fonts On-premise';
$GFO_PAGE_TITLE = 'Google Fonts On-premise';
$GFO_SECTION_DESCRIPTION = 'Here you can enable/disable GFO or change the GFO URL. If you did not setup GFO yet, here\'s <a href="https://github.com/bartscherer/gfo">the GFO repository</a>';
$GFO_SECTION_HEADLINE = 'Settings for Google Fonts On-premise';
$GFO_URL_LABEL = 'GFO instance URL';

/*

Below we prepare the hooks for GFO so that it can modify the
page output at the latest possible time.

*/

function removeGoogleFontsFromFinalOutput($originalOutput)
{
    $options = get_option('gfo_settings');
    if(!$options) return $originalOutput;
    if(!$options['gfo_enabled'] || !$options['gfo_url']) return $originalOutput;
    $replacementURL = $options['gfo_url'];

    /* remove trailing slashes */
    while(1)
    {
        if(mb_strlen($replacementURL) < 1) return $originalOutput;
        if(mb_substr($replacementURL, mb_strlen($replacementURL) - 1, mb_strlen($replacementURL)) === '/')
        {
            $replacementURL = mb_substr($replacementURL, 0, mb_strlen($replacementURL) - 1);
            continue;
        }
        break;
    }

    /* remove http[s]:// */
    $replacementURLSplit = explode('://', $replacementURL);
    $possibleProto = mb_strtolower($replacementURLSplit[0]);
    if(in_array($possibleProto, ['http', 'https']))
    {
        $replacementURL = mb_substr($replacementURL, mb_strlen($possibleProto) + 3, mb_strlen($replacementURL));
    }

    /* replace legacy calls to fonts.google.com/icon with [gfo url]/css */
    $sanitizedOutput = str_replace(
        'fonts.googleapis.com/icon',
        sprintf('%s/css', $replacementURL),
        $originalOutput
    );

    /* replace all occurences of fonts.googleapis.com */
    $sanitizedOutput = str_replace(
        'fonts.googleapis.com',
        $replacementURL,
        $sanitizedOutput
    );

    return $sanitizedOutput . $replacementURL;
}

/* We add our filtering method above as the final_output filter */
add_filter(
    'final_output',
    removeGoogleFontsFromFinalOutput
);

/* We start output buffering when the init hook gets called */
add_action(
    'init',
    function()
    {
        ob_start();
    }
);

/* We run our output "collecting" method when PHP is just about to shut down */
add_action(
    'shutdown',
    function()
    {
        /*
            We loop over every nesting level of the output buffering
            and add the content of every level to the $finalWPOutput
        */
        $finalWPOutput = '';
        $obNestingLevels = ob_get_level();
        for ($currLevel = 0; $currLevel < $obNestingLevels; $currLevel++)
        {
            $finalWPOutput .= ob_get_clean();
        }
        echo apply_filters('final_output', $finalWPOutput);
    },
    0
);

/*

Below we create the settings page for the GFO plugin which enables the admins
to enable/disable GFO and set the desired GFO URL.

*/

/*
    We leverage the admin_init hook to call GFO's settings initialization method.
    Then we continue to initialize all settings related to GFO when the admin_init
    hook gets called.
*/

add_action('admin_init', 'gfo_settings_init');

function gfo_settings_init()
{
    global $GFO_SECTION_HEADLINE;
    global $GFO_ENABLED_LABEL;
    global $GFO_URL_LABEL;
    register_setting('pluginPage', 'gfo_settings');
    add_settings_section(
        'gfo_pluginPage_section',
        __(
            $GFO_SECTION_HEADLINE,
            'gfo'
        ),
        'gfo_render_section',
        'pluginPage'
    );
    add_settings_field(
        'gfo_enabled',
        __(
            $GFO_ENABLED_LABEL,
            'gfo'
        ),
        'gfo_render_enabled',
        'pluginPage',
        'gfo_pluginPage_section'
    );
    add_settings_field(
        'gfo_url',
        __(
            $GFO_URL_LABEL,
            'gfo'
        ),
        'gfo_render_url',
        'pluginPage',
        'gfo_pluginPage_section'
    );
}

/*
    We use the admin_menu hook in order to add GFO's options page to the admin menu.
    When the hook gets called, we add the options page for GFO and render the
    different settings.
*/

add_action('admin_menu', 'gfo_add_admin_menu');

function gfo_add_admin_menu()
{
    global $GFO_MENU_SLUG;
    global $GFO_MENU_TITLE;
    global $GFO_PAGE_TITLE;
    add_options_page(
        $GFO_PAGE_TITLE,
        $GFO_MENU_TITLE,
        'manage_options',
        $GFO_MENU_SLUG,
        'display_gfo_options_page'
    );
}

function display_gfo_options_page()
{
    ?>
    <form
        action='options.php'
        method='post'
    >
        <h2>GFO</h2>
        <?php
            settings_fields('pluginPage');
            do_settings_sections('pluginPage');
            submit_button();
        ?>
    </form>
    <?php
}

function gfo_render_section()
{
    global $GFO_SECTION_DESCRIPTION;
    echo __($GFO_SECTION_DESCRIPTION, 'gfo');
}

function gfo_render_enabled()
{
    $options = get_option('gfo_settings');
    ?>
        <input
            type='checkbox'
            name='gfo_settings[gfo_enabled]'
            <?php checked($options['gfo_enabled'], 1); ?>
            value='1'
        >
    <?php
}

function gfo_render_url()
{
    $options = get_option('gfo_settings');
    ?>
        <input
            type='text'
            name='gfo_settings[gfo_url]'
            value='<?php echo $options['gfo_url'];?>'
        >
    <?php
}
