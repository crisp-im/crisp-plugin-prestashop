{*
*  @author  Baptiste Jamin <baptiste@crisp.im>
*  @copyright  Crisp IM 2016
*  @license
*  @version  Release: $Revision: 0.3.0 $
*}

{if $is_crisp_working == true}
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-gear"></i> Connected with Crisp
		</div>
		<div class="wrap crisp-wrap">
		    <div class="crisp-modal">
		     	<p class="crisp-subtitle">You can now use Crisp from your homepage</p>
		     	<a class="crisp-button crisp-neutral" href="https://app.crisp.im/settings/website/{$website_id|escape:'htmlall':'UTF-8'}">Go to my Crisp settings</a>
     			<a class="crisp-button crisp" href="{$add_to_crisp_link|escape:'htmlall':'UTF-8'}">Reconfigure</a>
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
		      <p class="crisp-subtitle">This link will redirect you to Crisp and configure your Wordpress. Magic</p>
		       	<a class="crisp-button crisp-neutral" href="https://app.crisp.im/settings/website/{$website_id|escape:'htmlall':'UTF-8'}">Go to my Crisp settings</a>
     			<a class="crisp-button crisp" href="{$add_to_crisp_link|escape:'htmlall':'UTF-8'}">Connect with Crisp</a>
		    </div>
	  	</div>
	</div>
{/if}