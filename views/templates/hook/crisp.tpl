{*
*  @author  Baptiste Jamin <baptiste@crisp.im>
*  @copyright  Crisp IM 2016
*  @license
*  @version  Release: $Revision: 0.3.2 $
*}
<script type='text/javascript'>
window.$crisp=[];
window.CRISP_WEBSITE_ID = "{$crisp_website_id|escape:'htmlall':'UTF-8'}";
(function(){
  d=document;
  s=d.createElement('script');
  s.src='https://client.crisp.im/l.js';
  s.async=1;
  d.getElementsByTagName('head')[0].appendChild(s);

})();

{if $crisp_customer->isLogged() == true}
	$crisp.push(["set", "user:nickname", "{$crisp_customer->firstname|escape:'htmlall':'UTF-8'} {$crisp_customer->lastname|escape:'htmlall':'UTF-8'}"]);
	$crisp.push(["set", "user:email", "{$crisp_customer->email|escape:'htmlall':'UTF-8'}"]);
{/if}

</script>

