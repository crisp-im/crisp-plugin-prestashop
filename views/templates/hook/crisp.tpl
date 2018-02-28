{*
*  @author  Baptiste Jamin <baptiste@crisp.chat>
*  @copyright  Crisp IM 2016
*  @license
*  @version  Release: $Revision: 0.3.4 $
*}
<script type='text/javascript'>
window.$crisp=[];
window.CRISP_WEBSITE_ID = "{$crisp_website_id|escape}";
(function(){
  d=document;
  s=d.createElement('script');
  s.src='https://client.crisp.chat/l.js';
  s.async=1;
  d.getElementsByTagName('head')[0].appendChild(s);

})();

{if $crisp_customer->isLogged() == true}
	$crisp.push(["set", "user:nickname", "{$crisp_customer->firstname|escape} {$crisp_customer->lastname}"]);
	$crisp.push(["set", "user:email", "{$crisp_customer->email|escape}"]);
{/if}

</script>

