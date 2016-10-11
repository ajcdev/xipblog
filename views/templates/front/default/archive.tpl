{if isset($xipblogpost) && !empty($xipblogpost)}
<section class="kr_blog_post_area">
	<div class="kr_blog_post_inner">
		{foreach from=$xipblogpost item=xpblgpst}
			<article class="blog_post {$xpblgpst.post_format}">
				<div class="blog_post_content">
					<div class="blog_post_content_top">
						<div class="post_thumbnail">
							{if $xpblgpst.post_format == 'video'}
								{assign var="postvideos" value=','|explode:$xpblgpst.video}
								{include file="./post-video.tpl" videos=$postvideos width='870' height="482" class="{if $postvideos|@count > 1 }carousel{/if}"}
							{elseif $xpblgpst.post_format == 'audio'}
								{assign var="postaudio" value=','|explode:$xpblgpst.audio}
								{include file="./post-audio.tpl" audios=$postaudio width='870' height="482" class="{if $postaudio|@count > 1 }carousel{/if}"}
							{elseif $xpblgpst.post_format == 'gallery'}
								{include file="./post-gallery.tpl" gallery=$xpblgpst.gallery_lists imagesize="medium" class="{if $xpblgpst.gallery_lists|@count > 1 }carousel{/if}"}
							{else}
								<img class="img-responsive" src="{$xpblgpst.post_img_medium}" alt="{$xpblgpst.post_title}">
							{/if}
							
							{if $xpblgpst.post_format != 'video' && $xpblgpst.post_format != 'audio'}
								<div class="post_meta_date">
									{$xpblgpst.post_date|date_format:"<b>%e</b> <b>%b</b>"}
								</div>
							{/if}
						</div>
						<div class="post_meta clearfix">
							{if $xpblgpst.post_format == 'video' || $xpblgpst.post_format == 'audio'}
								<div class="post_meta_date">
									<i class="icon-calendar"></i>
									{$xpblgpst.post_date|date_format:"%b %d, %Y"}
								</div>
							{/if}
							<div class="meta_author">
								<i class="icon-user"></i>
								<span>{l s='By' mod='xipblog'} {$xpblgpst.post_author_arr.firstname} {$xpblgpst.post_author_arr.lastname}</span>
							</div>
							<div class="meta_category">
								<i class="icon-tag"></i>
									<a href="{$xpblgpst.category_default_arr.link}">{$xpblgpst.category_default_arr.name}</a>
							</div>
							<div class="meta_comment">
								<i class="icon-eye"></i>
								<span>{l s='Views' mod='xipblog'} ({$xpblgpst.comment_count})</span>
							</div>
						</div>
					</div>
					<div class="blog_post_content_bottom">
						<h3 class="post_title"><a href="{$xpblgpst.link}">{$xpblgpst.post_title}</a></h3>
						<div class="post_content">
							{if isset($xpblgpst.post_excerpt) && !empty($xpblgpst.post_excerpt)}
								{$xpblgpst.post_excerpt|truncate:500:'...'|escape:'html':'UTF-8'}
								<a class="read_more" href="{$xpblgpst.link}">{l s='Read More >>' mod='xipblog'}</a>
							{else}
								{$xpblgpst.post_content|truncate:400:'...'|escape:'html':'UTF-8'}
								<a class="read_more" href="{$xpblgpst.link}">{l s='Read More >>' mod='xipblog'}</a>
							{/if}
						</div>
					</div>
				</div>
			</article>
		{/foreach}
	</div>
</section>
{/if}
{include file="$tpl_dir./pagination.tpl"}