@php
  $pixelId = trim(\App\Models\Setting::get('facebook_pixel', '') ?? '');
  $isLandingSuccess = request()->is('product/*/success/*') || request()->routeIs('landing.order.success');
  $lpSlug = isset($landingPage) && is_object($landingPage) && !empty($landingPage->slug) ? $landingPage->slug : ($landingPageSlug ?? null);
  $orderNumber = isset($order) && is_array($order) && !empty($order['order_number']) ? $order['order_number'] : (request()->route('orderNumber') ?? null);
@endphp
@if(!empty($pixelId) && preg_match('/^\d{14,18}$/', $pixelId))
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq.disablePushState = true;
fbq('set', 'autoConfig', false, '{{ $pixelId }}');
fbq('init', '{{ $pixelId }}');
@if($isLandingSuccess && !empty($orderNumber))
(function() {
  var successPageViewKey = 'meta_tracked_success_pageview_{{ $orderNumber }}';
  if (!sessionStorage.getItem(successPageViewKey)) {
    sessionStorage.setItem(successPageViewKey, '1');
    fbq('track', 'PageView');
  }
})();
@elseif(!empty($lpSlug) && !$isLandingSuccess)
(function() {
  var pageViewKey = 'meta_tracked_pageview_{{ $lpSlug }}';
  if (!sessionStorage.getItem(pageViewKey)) {
    sessionStorage.setItem(pageViewKey, '1');
    fbq('track', 'PageView');
  }
})();
@else
fbq('track', 'PageView');
@endif
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
@endif
