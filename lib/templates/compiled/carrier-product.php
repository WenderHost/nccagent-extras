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
    return '<'.htmlspecialchars((string)(($inary && isset($in['headingElement'])) ? $in['headingElement'] : null), ENT_QUOTES, 'UTF-8').'>'.htmlspecialchars((string)((isset($in['carrier']) && is_array($in['carrier']) && isset($in['carrier']['name'])) ? $in['carrier']['name'] : null), ENT_QUOTES, 'UTF-8').' '.htmlspecialchars((string)((isset($in['carrier']) && is_array($in['carrier']) && isset($in['carrier']['product'])) ? $in['carrier']['product'] : null), ENT_QUOTES, 'UTF-8').'</'.htmlspecialchars((string)(($inary && isset($in['headingElement'])) ? $in['headingElement'] : null), ENT_QUOTES, 'UTF-8').'>
<h2>State Availability</h2>
'.((LR::ifvar($cx, ((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['states_review_date'])) ? $in['product_details']['states_review_date'] : null), false)) ? '<p class="review-date">Current as of '.htmlspecialchars((string)((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['states_review_date'])) ? $in['product_details']['states_review_date'] : null), ENT_QUOTES, 'UTF-8').''.((LR::ifvar($cx, ((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['plan_year'])) ? $in['product_details']['plan_year'] : null), false)) ? ' &ndash; <span class="plan-year">Plan Year '.htmlspecialchars((string)((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['plan_year'])) ? $in['product_details']['plan_year'] : null), ENT_QUOTES, 'UTF-8').'</span>' : '').'</p>' : '').'
  '.(($inary && isset($in['states'])) ? $in['states'] : null).'

<h2>Plan Information</h2>
'.((LR::ifvar($cx, ((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['desc_review_date'])) ? $in['product_details']['desc_review_date'] : null), false)) ? '<p class="review-date">Current as of '.htmlspecialchars((string)((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['desc_review_date'])) ? $in['product_details']['desc_review_date'] : null), ENT_QUOTES, 'UTF-8').''.((LR::ifvar($cx, ((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['plan_year'])) ? $in['product_details']['plan_year'] : null), false)) ? ' &ndash; <span class="plan-year">Plan Year '.htmlspecialchars((string)((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['plan_year'])) ? $in['product_details']['plan_year'] : null), ENT_QUOTES, 'UTF-8').'</span>' : '').'</p>' : '').'
'.((LR::ifvar($cx, ((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['lower_issue_age'])) ? $in['product_details']['lower_issue_age'] : null), false)) ? '<p>Issue ages '.htmlspecialchars((string)((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['lower_issue_age'])) ? $in['product_details']['lower_issue_age'] : null), ENT_QUOTES, 'UTF-8').'&ndash;'.htmlspecialchars((string)((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['upper_issue_age'])) ? $in['product_details']['upper_issue_age'] : null), ENT_QUOTES, 'UTF-8').'.</p>' : '').'
'.((isset($in['product_details']) && is_array($in['product_details']) && isset($in['product_details']['description'])) ? $in['product_details']['description'] : null).'';
};
?>