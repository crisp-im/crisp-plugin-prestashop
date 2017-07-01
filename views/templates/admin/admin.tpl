{*
*  @author  Baptiste Jamin <baptiste@crisp.chat>
*  @copyright  Crisp IM 2016
*  @license
*  @version  Release: $Revision: 0.3.3 $
*}


<script>
	function linkWithCrisp () {
		var add_to_crisp_link = "https://app.crisp.chat/initiate/plugin/be40c894-22bb-408c-8fdc-aafb5e6b1985?payload=";
		add_to_crisp_link += Base64.encode("{$http_callback|escape:'javascript':'UTF-8'}")
		window.open(add_to_crisp_link,"_self")
	}
</script>

{if $is_crisp_working == true}
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-gear"></i> Connected with Crisp
		</div>
		<div class="wrap crisp-wrap">
		    <div class="crisp-modal">
		     	<p class="alert alert-success">You can now use Crisp from your homepage</p>
		     	<a class="crisp-button crisp-neutral" href="https://app.crisp.chat/settings/website/{$website_id|escape:'htmlall':'UTF-8'}">Go to my Crisp settings</a>
     			<a class="crisp-button crisp" href="#" onclick="linkWithCrisp()">Reconfigure</a>
		    </div>
	  	</div>
	</div>
{else}
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-gear"></i> Connect with Crisp
		</div>
		<div class="wrap crisp-wrap">
		    <div class="crisp-modal">
		      <p class="alert alert-info">This link will redirect you to Crisp and configure your Prestashop. Magic</p>
		       	<a class="crisp-button crisp-neutral" href="https://app.crisp.chat/settings/website/{$website_id|escape:'htmlall':'UTF-8'}">Go to my Crisp settings</a>
     			<a class="crisp-button crisp" href="#" onclick="linkWithCrisp()">Connect with Crisp</a>
		    </div>
	  	</div>
	</div>
{/if}