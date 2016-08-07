{*
*  @author  Baptiste Jamin <baptiste@crisp.im>
*  @copyright  Crisp IM 2016
*  @license
*  @version  Release: $Revision: 0.3.0 $
*}
{if $is_crisp_working == true}
  <div class="wrap crisp-wrap">
    <div class="crisp-modal">
      <span class="crisp-span">Crisp is working. Click on retry to reconfigure</span>
      <img class="crisp-check" src="{$img_check|escape:'htmlall':'UTF-8'}">
      <a class="crisp-retry" href="{$add_to_crisp_link|escape:'htmlall':'UTF-8'}">Retry</a>
    </div>
  </div>
{else}
  <div class="wrap crisp-wrap">
    <div class="crisp-modal">
      <span class="crisp-span">To get started, please click on "link with Crisp"</span>
      <a id="crisp_link" href="{$add_to_crisp_link|escape:'htmlall':'UTF-8'}"><img class="crisp-sign" src="{$img_link_with_crisp|escape:'htmlall':'UTF-8'}" /></a>
    </div>
  </div>
{/if}
