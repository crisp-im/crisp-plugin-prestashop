{if $is_crisp_working == true}
  <div class="wrap crisp-wrap">
    <div class="crisp-modal">
      <span class="crisp-span">Crisp is working. Click on retry to reconfigure</span>
      <img class="crisp-check" src="{$img_check}">
      <a class="crisp-retry" href="{$add_to_crisp_link}">Retry</a>
    </div>
  </div>
{else}
  <div class="wrap crisp-wrap">
    <div class="crisp-modal">
      <span class="crisp-span">To get started, please click on "link with Crisp"</span>
      <a id="crisp_link" href="{$add_to_crisp_link}"><img class="crisp-sign" src="{$img_link_with_crisp}" /></a>
    </div>
  </div>
{/if}
