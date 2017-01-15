{*
*  @author  Baptiste Jamin <baptiste@crisp.im>
*  @copyright  Crisp IM 2016
*  @license
*  @version  Release: $Revision: 0.3.0 $
*}
<script type='text/javascript'>

CRISP_WEBSITE_ID = "{$website_id|escape:'htmlall':'UTF-8'}";
(function(){
  d=document;
  s=d.createElement('script');
  s.src='https://client.crisp.im/l.js';
  s.async=1;
  d.getElementsByTagName('head')[0].appendChild(s);
})();

{if $customer->isLogged() == true}
	window.CRISP_READY_TRIGGER = function() {
	  	$crisp.push(["set", "user:nickname", "{$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}"]);
		$crisp.push(["set", "user:email", "{$customer->email|escape:'htmlall':'UTF-8'}"]);
	};
{/if}

</script>

