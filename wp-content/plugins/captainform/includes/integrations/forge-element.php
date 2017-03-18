<?php
define('FORGE_ELEMENT_DOMAIN', 'captainform');

function adding_captainform_forge_element_css()
{
    wp_enqueue_style('forge_element_css', plugin_dir_url( __FILE__ ) . '../css/forge_element.css', false, uniqid());
}

function forge_element_captainform($atts, $content)
{
    if ($atts['form_id'] != "none") {
        $container_id = md5($atts['form_id'] + time());
        $container = "<div id=\"{$container_id}\">%s</div>";
        return sprintf($container, captain_form($atts['form_id']));
    } else {
        return '<div class="forge-captainform-form-placeholder"></div>';
    }
}

add_filter('forge_elements', 'forge_element_captainform_metadata');
function forge_element_captainform_metadata($data)
{

    $fields = array();

    $selectField = array(
        'type' => "list",
        'name' => "form_id",
        'label' => __('Select Form', FORGE_ELEMENT_DOMAIN),
        'default' => "none",
        'choices' => array('none' => __('Please select...', FORGE_ELEMENT_DOMAIN)),
    );

    $fields['select'] = $selectField;

    $response = captainform_get_forms('page_or_post');
    if ($response->status == "ok") {
        foreach ($response->forms as $form) {
            $fields['select']['choices'][$form->f_id] = $form->f_name;
        }
        $fields['my_forms'] = get_forge_captainform_button("My Forms", admin_url("admin.php?page=CaptainForm"), "_captainFormTab");
    } else {
        $fields['select']['choices']['none'] = __('No forms available', FORGE_ELEMENT_DOMAIN);
        $fields['select']['description'] = __('There are no forms available, please create one by clicking the button below.', FORGE_ELEMENT_DOMAIN);
        $fields['create'] = get_forge_captainform_button("New Form", admin_url("admin.php?page=CaptainForm-NewForm"), "_captainFormTab");
    }

    $data['captainform'] = array(
        'title' => __('CaptainForm', FORGE_ELEMENT_DOMAIN),
        'description' => __('User-friendly form builder', FORGE_ELEMENT_DOMAIN),
        'featured' => 50,
        'group' => 'forms',
        'callback' => 'forge_element_captainform',
        'fields' => $fields,
    );

    return $data;
}

function forge_field_captainform_button($args, $value = '')
{
    if (!isset($args['target']) || empty($args['target']))
        $args['target'] = "_blank";

    $fieldContainer = '<div class="forge-captainform-field">%s</div>';
    $controlsContainer = '<div class="forge-captainform-field-controls">%s</div>';
    $aTag = '<a class="forge-captainform-field-button forge-captainform-create" href="%s" target="%s">%s</a>';

    $aTag = sprintf($aTag, $args['url'], $args['target'], $args['text']);
    $controlsContainer = sprintf($controlsContainer, $aTag);

    return sprintf($fieldContainer, $controlsContainer);
}

function get_forge_captainform_button($text = "null", $url = "null", $target = "_blank")
{

    return array(
        'type' => "captainform_button",
        'name' => "selector",
        'default' => "none",
        'text' => __($text, FORGE_ELEMENT_DOMAIN),
        'url' => $url,
        'target' => $target,
    );

}

add_action('wp_enqueue_scripts', 'adding_captainform_forge_element_css');