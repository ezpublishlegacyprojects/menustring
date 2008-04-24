{if $url}
<a href={$url}>{$node.name|wash()}</a>
{else}
{$node.name|wash()}
{/if}