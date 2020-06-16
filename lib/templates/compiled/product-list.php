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
    return ''.LR::sec($cx, (($inary && isset($in['products'])) ? $in['products'] : null), null, $in, true, function($cx, $in) {$inary=is_array($in);return '  <h2 class="product-title">'.htmlspecialchars((string)(($inary && isset($in['title'])) ? $in['title'] : null), ENT_QUOTES, 'UTF-8').'</h2>
  <p><a href="'.htmlspecialchars((string)(($inary && isset($in['permalink'])) ? $in['permalink'] : null), ENT_QUOTES, 'UTF-8').'">View this information as a web page.</a></p>
  <h3>State Availability</h3>
  '.((LR::ifvar($cx, (($inary && isset($in['states_review_date'])) ? $in['states_review_date'] : null), false)) ? '<p class="review-date">Current as of '.htmlspecialchars((string)(($inary && isset($in['states_review_date'])) ? $in['states_review_date'] : null), ENT_QUOTES, 'UTF-8').'</p>' : '').'
  <p>'.(($inary && isset($in['states'])) ? $in['states'] : null).'</p>
  <h3>Plan Information</h3>
  '.((LR::ifvar($cx, (($inary && isset($in['desc_review_date'])) ? $in['desc_review_date'] : null), false)) ? '<p class="review-date">Current as of '.htmlspecialchars((string)(($inary && isset($in['desc_review_date'])) ? $in['desc_review_date'] : null), ENT_QUOTES, 'UTF-8').'</p>' : '').'
  '.((LR::ifvar($cx, (($inary && isset($in['lower_issue_age'])) ? $in['lower_issue_age'] : null), false)) ? '<p>Issue ages '.htmlspecialchars((string)(($inary && isset($in['lower_issue_age'])) ? $in['lower_issue_age'] : null), ENT_QUOTES, 'UTF-8').'&ndash;'.htmlspecialchars((string)(($inary && isset($in['upper_issue_age'])) ? $in['upper_issue_age'] : null), ENT_QUOTES, 'UTF-8').'.</p>' : '').'
  '.(($inary && isset($in['description'])) ? $in['description'] : null).'
  '.((LR::ifvar($cx, (($inary && isset($in['medicare_product'])) ? $in['medicare_product'] : null), false)) ? '<p><em>Some information may vary by state. <a href="'.htmlspecialchars((string)(($inary && isset($in['medicare_quote_engine_url'])) ? $in['medicare_quote_engine_url'] : null), ENT_QUOTES, 'UTF-8').'">See state-specific information and rates</a>.</em></p>' : '').'
  <div class="kit-request">
    <h3>Request a Product Kit for '.htmlspecialchars((string)(($inary && isset($in['carriername'])) ? $in['carriername'] : null), ENT_QUOTES, 'UTF-8').' '.htmlspecialchars((string)(($inary && isset($in['title'])) ? $in['title'] : null), ENT_QUOTES, 'UTF-8').'</h3>
    <p>We’ll email you a kit with brochures, commissions, and rates all specific to the state of your choice.</p>
    <p><a class="elementor-button" href="'.htmlspecialchars((string)(($inary && isset($in['kit_request_url'])) ? $in['kit_request_url'] : null), ENT_QUOTES, 'UTF-8').'">Request a Kit</a></p>
  </div>
';}).'';
};
?>