<?php
use \LightnCandy\SafeString as SafeString;use \LightnCandy\Runtime as LR;return function ($in = null, $options = null) {
    $helpers = array();
    $partials = array();
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'jslen' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'lambda' => false,
            'mustlok' => false,
            'mustlam' => false,
            'mustsec' => false,
            'echo' => false,
            'partnc' => false,
            'knohlp' => false,
            'debug' => isset($options['debug']) ? $options['debug'] : 1,
        ),
        'constants' => array(),
        'helpers' => isset($options['helpers']) ? array_merge($helpers, $options['helpers']) : $helpers,
        'partials' => isset($options['partials']) ? array_merge($partials, $options['partials']) : $partials,
        'scopes' => array(),
        'sp_vars' => isset($options['data']) ? array_merge(array('root' => $in), $options['data']) : array('root' => $in),
        'blparam' => array(),
        'partialid' => 0,
        'runtime' => '\LightnCandy\Runtime',
    );
    
    $inary=is_array($in);
    return '<h2>'.htmlspecialchars((string)(($inary && isset($in['carriername'])) ? $in['carriername'] : null), ENT_QUOTES, 'UTF-8').' Products and State Availability</h2>
<p>These are '.htmlspecialchars((string)(($inary && isset($in['carriername'])) ? $in['carriername'] : null), ENT_QUOTES, 'UTF-8').'\'s current products and state availability for '.htmlspecialchars((string)(($inary && isset($in['year'])) ? $in['year'] : null), ENT_QUOTES, 'UTF-8').', as well as information on contracting and appointment.</p>';
};
?>