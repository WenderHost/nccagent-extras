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
    return '<p>To view our Agent Docs Library, you\'ll need to either login or register for a free account:</p>

<div class="elementor-button-wrapper" style="display: inline">
  <a href="'.htmlspecialchars((string)(($inary && isset($in['home_url'])) ? $in['home_url'] : null), ENT_QUOTES, 'UTF-8').'/login/" class="elementor-button-link elementor-button elementor-size-sm" role="button">
    <span class="elementor-button-content-wrapper">
      <span class="elementor-button-text">Login</span>
    </span>
  </a>
</div>

<div class="elementor-button-wrapper" style="display: inline">
  <a href="'.htmlspecialchars((string)(($inary && isset($in['home_url'])) ? $in['home_url'] : null), ENT_QUOTES, 'UTF-8').'/register/" class="elementor-button-link elementor-button elementor-size-sm" role="button">
    <span class="elementor-button-content-wrapper">
      <span class="elementor-button-text">Register</span>
    </span>
  </a>
</div>
';
};
?>