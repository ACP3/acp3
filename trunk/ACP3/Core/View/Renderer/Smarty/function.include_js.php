<?php
/**
 * Fügt die angegebene JavaScript-Datei in ein Template ein
 *
 * @param array $params
 * @return string
 */
function smarty_function_include_js($params)
{
    if (isset($params['module'], $params['file']) === true &&
        (bool)preg_match('=/=', $params['module']) === false &&
        (bool)preg_match('=/=', $params['file']) === false
    ) {
        $script = '<script type="text/javascript" src="%s"></script>';
        $module = ucfirst($params['module']);
        $moduleLower = strtolower($params['module']);
        $file = $params['file'];

        if (file_exists(DESIGN_PATH_INTERNAL . $moduleLower . '/js/' . $file . '.js') === true) {
            return sprintf($script, DESIGN_PATH . $moduleLower . '/js/' . $file . '.js');
        } elseif (file_exists(MODULES_DIR . $module . '/js/' . $file . '.js') === true) {
            return sprintf($script, ROOT_DIR . 'ACP3/Modules/' . $module . '/js/' . $file . '.js');
        }
    }

    return 'Not all necessary arguments for the function ' . __FUNCTION__ . ' were passed!';
}
/* vim: set expandtab: */