<div class="kr_blog_post_area single">
	<div class="kr_blog_post_inner">
		<article class="blog_post blog_post_{$xipblogpost.post_format}">
			<div class="blog_post_content">
				<div class="blog_post_content_top">
					<div class="post_thumbnail">
						{if $xipblogpost.post_format == 'video'}
							{assign var="postvideos" value=','|explode:$xipblogpost.video}
							{include file="./post-video.tpl" videos=$postvideos width='870' height="482" class="{if $postvideos|@count > 1 }carousel{/if}"}
						{elseif $xipblogpost.post_format == 'audio'}
							{assign var="postaudio" value=','|explode:$xipblogpost.audio}
							{include file="./post-audio.tpl" audios=$postaudio width='870' height="482" class="{if $postaudio|@count > 1 }carousel{/if}"}
						{elseif $xipblogpost.post_format == 'gallery'}
							{include file="./post-gallery.tpl" gallery=$xipblogpost.gallery_lists imagesize="medium" class="{if $xipblogpost.gallery_lists|@count > 1 }carousel{/if}"}
						{else}
							<img class="img-responsive" src="{$xipblogpost.post_img_medium}" alt="{$xipblogpost.post_title}">
						{/if}
						<div class="post_meta_date">
							{$xipblogpost.post_date|date_format:"<b>%e</b> <b>%b</b>"}
						</div>
					</div>
					<div class="post_meta clearfix">
						<div class="meta_author">
							<i class="icon-user"></i>
							<span>{l s='By' mod='xipblog'} {$xipblogpost.post_author_arr.firstname} {$xipblogpost.post_author_arr.lastname}</span>
						</div>
						<div class="meta_category">
							<i class="icon-tag"></i>
							<span>{$xipblogpost.category_default_arr.name}</span>
						</div>
						<div class="meta_comment">
							<i class="icon-eye"></i>
							<span>{l s='Views' mod='xipblog'} ({$xipblogpost.comment_count})</span>
						</div>
					</div>
				</div>
				<div class="blog_post_content_bottom">
					<h3 class="post_title">{$xipblogpost.post_title}</h3>
					<div class="post_content">
						{$xipblogpost.post_content}
					</div>
				</div>
			</div>
		</article>
	</div>
</div>
{if ($xipblogpost.comment_status == 'open') || ($xipblogpost.comment_status == 'close')}
			{include file="./comment-list.tpl"}
{/if}
{if (isset($disable_blog_com) && $disable_blog_com == 1) && ($xipblogpost.comment_status == 'open')}
			{include file="./comment.tpl"}
{/if}