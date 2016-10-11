<div class="post_format_items {if isset($class) && $class}{$class}{/if}">
{foreach from=$audios item=audiourl}
	<div class="item post_audio">
		<iframe src="{if isset($audiourl) && $audiourl}{$audiourl}{/if}" width="{if isset($width) && $width}{$width}{/if}" height="{if isset($height) && $height}{$height}{/if}" frameborder="0"></iframe>
	</div>
{/foreach}
</div>